<?php

namespace App\Services;

use App\Enums\Difficulty;
use App\Models\Category;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Exports quizzes to a portable JSON format and imports them back.
 *
 * One quiz:   { "quiz": { ... } }
 * Many:       { "version": 1, "exported_at": "...", "quizzes": [ { ... }, ... ] }
 *
 * A quiz looks like:
 * {
 *   "title": "...", "category": "PHP", "difficulty": "easy", "description": "...",
 *   "duration_minutes": 30, "start_exam_at": null, "end_exam_at": null,
 *   "questions": [ { "question": "...", "answers": [ { "answer": "...", "is_correct": true }, ... ] } ]
 * }
 */
class QuizTransfer {
    public const FORMAT_VERSION = 1;

    private const EXPORT_CHUNK_SIZE = 50;
    private const INSERT_CHUNK_SIZE = 1000;

    /** Category name (lowercase) => id, filled while importing to avoid repeated lookups. */
    private array $categoryIds = [];

    // ---------------------------------------------------------------- Export

    /**
     * One quiz in the portable format. Needs category and questions.answers loaded.
     */
    public function toArray( Quiz $quiz ): array {
        return [
            'title'            => $quiz->title,
            'category'         => $quiz->category?->name,
            'difficulty'       => $quiz->difficulty?->value,
            'description'      => $quiz->description,
            'duration_minutes' => $quiz->duration_minutes,
            'start_exam_at'    => $quiz->start_exam_at?->toDateTimeString(),
            'end_exam_at'      => $quiz->end_exam_at?->toDateTimeString(),
            'questions'        => $quiz->questions->map( fn( $question ) => [
                'question' => $question->question,
                'answers'  => $question->answers->map( fn( $answer ) => [
                    'answer'     => $answer->answer,
                    'is_correct' => (bool) $answer->is_correct,
                ] )->values()->all(),
            ] )->values()->all(),
        ];
    }

    /**
     * Write every quiz matched by the query as one JSON document, a chunk at a time,
     * so large exports don't have to fit in memory.
     */
    public function exportMany( Builder $query, callable $write ): void {
        $write( '{"version":' . self::FORMAT_VERSION . ',"exported_at":' . json_encode( now()->toIso8601String() ) . ',"quizzes":[' );

        $first = true;
        $query->with( $this->exportRelations() )->chunkById( self::EXPORT_CHUNK_SIZE, function ( $quizzes ) use ( &$first, $write ) {
            foreach ( $quizzes as $quiz ) {
                $write( ( $first ? "\n" : ",\n" ) . $this->encode( $this->toArray( $quiz ) ) );
                $first = false;
            }
        } );

        $write( "\n]}\n" );
    }

    public function exportOne( Quiz $quiz ): string {
        $quiz->loadMissing( $this->exportRelations() );

        return $this->encode( ['quiz' => $this->toArray( $quiz )], pretty: true );
    }

    private function exportRelations(): array {
        return [
            'category:id,name',
            'questions'         => fn( $query ) => $query->select( 'id', 'quiz_id', 'question' )->orderBy( 'id' ),
            'questions.answers' => fn( $query ) => $query->select( 'id', 'question_id', 'answer', 'is_correct' )->orderBy( 'id' ),
        ];
    }

    private function encode( array $data, bool $pretty = false ): string {
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;

        return json_encode( $data, $pretty ? $flags | JSON_PRETTY_PRINT : $flags );
    }

    // ---------------------------------------------------------------- Import

    /**
     * Import one or many quizzes. Nothing is saved if any quiz is invalid.
     * Quizzes whose title already exists are skipped.
     *
     * @return array{imported: list<string>, skipped: list<string>}
     *
     * @throws ValidationException
     */
    public function import( mixed $payload ): array {
        $quizzes = $this->extractQuizzes( $payload );
        $this->validate( $quizzes );

        $existing = Quiz::whereIn( 'title', array_column( $quizzes, 'title' ) )
            ->pluck( 'title' )
            ->map( fn( $title ) => Str::lower( $title ) )
            ->flip();

        $result = ['imported' => [], 'skipped' => []];

        DB::transaction( function () use ( $quizzes, $existing, &$result ) {
            foreach ( $quizzes as $data ) {
                if ( $existing->has( Str::lower( $data['title'] ) ) ) {
                    $result['skipped'][] = $data['title'];
                    continue;
                }

                $this->createQuiz( $data );
                $result['imported'][] = $data['title'];
            }
        } );

        return $result;
    }

    /**
     * Save questions and answers for a quiz with one insert for the questions and
     * batched inserts for the answers.
     *
     * @param  array  $questions  [['question' => '...', 'answers' => [['answer' => '...', 'is_correct' => bool], ...]], ...]
     */
    public function saveQuestions( Quiz $quiz, array $questions ): void {
        $now = now();

        $quiz->questions()->insert( array_map( fn( $question ) => [
            'quiz_id'    => $quiz->id,
            'question'   => $question['question'],
            'created_at' => $now,
            'updated_at' => $now,
        ], $questions ) );

        // Inserted rows get increasing ids, so ordering by id matches the input order
        $questionIds = $quiz->questions()->orderBy( 'id' )->pluck( 'id' );

        $answers = [];
        foreach ( array_values( $questions ) as $index => $question ) {
            foreach ( $question['answers'] as $answer ) {
                $answers[] = [
                    'question_id' => $questionIds[$index],
                    'answer'      => $answer['answer'],
                    'is_correct'  => (bool) $answer['is_correct'],
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }
        }

        foreach ( array_chunk( $answers, self::INSERT_CHUNK_SIZE ) as $chunk ) {
            DB::table( 'answers' )->insert( $chunk );
        }
    }

    /**
     * Accepts {"quiz": {...}}, {"quizzes": [...]} or a plain list of quizzes.
     */
    private function extractQuizzes( mixed $payload ): array {
        $quizzes = match ( true ) {
            is_array( $payload ) && isset( $payload['quizzes'] )   => $payload['quizzes'],
            is_array( $payload ) && isset( $payload['quiz'] )      => [$payload['quiz']],
            is_array( $payload ) && array_is_list( $payload )      => $payload,
            default                                                 => null,
        };

        if ( !is_array( $quizzes ) || $quizzes === [] ) {
            throw ValidationException::withMessages( [
                'quiz_file' => 'The file has no quizzes. Expected {"quiz": {...}} or {"quizzes": [...]}.',
            ] );
        }

        return array_values( $quizzes );
    }

    /**
     * Validate every quiz. Each quiz gets its own validator: Laravel's wildcard rules slow
     * down sharply on one huge nested array, but are fast on many small ones.
     */
    private function validate( array $quizzes ): void {
        $messages = [];
        $titles   = [];

        foreach ( $quizzes as $index => $quiz ) {
            if ( !is_array( $quiz ) ) {
                $messages["$index"] = ['must be an object'];
                continue;
            }

            $validator = Validator::make( $quiz, $this->quizRules() );
            $validator->after( fn( $validator ) => $this->checkOneCorrectAnswer( $validator, $quiz ) );

            foreach ( $validator->errors()->messages() as $key => $errors ) {
                $messages["$index.$key"] = $errors;
            }

            // The same title twice in one file
            $title = Str::lower( trim( (string) ( $quiz['title'] ?? '' ) ) );
            if ( $title !== '' && isset( $titles[$title] ) ) {
                $messages["$index.title"][] = 'is used by more than one quiz in this file';
            }
            $titles[$title] = true;
        }

        if ( $messages ) {
            throw ValidationException::withMessages( [
                'quiz_file' => $this->readableErrors( $messages, $quizzes ),
            ] );
        }
    }

    private function quizRules(): array {
        return [
            'title'                          => ['required', 'string', 'max:255'],
            'category'                       => ['required', 'string', 'max:255'],
            'difficulty'                     => ['nullable', Rule::enum( Difficulty::class )],
            'description'                    => ['nullable', 'string'],
            'duration_minutes'               => ['nullable', 'integer', 'min:1', 'max:1440'],
            'start_exam_at'                  => ['nullable', 'date'],
            'end_exam_at'                    => ['nullable', 'date'],
            'questions'                      => ['required', 'array', 'min:1'],
            'questions.*.question'           => ['required', 'string'],
            'questions.*.answers'            => ['required', 'array', 'min:2'],
            'questions.*.answers.*.answer'   => ['required', 'string'],
            'questions.*.answers.*.is_correct' => ['required', 'boolean'],
        ];
    }

    /**
     * Each question needs exactly one correct answer, matching how exams are taken.
     */
    private function checkOneCorrectAnswer( $validator, array $quiz ): void {
        foreach ( (array) ( $quiz['questions'] ?? [] ) as $i => $question ) {
            $correct = collect( (array) ( $question['answers'] ?? [] ) )
                ->filter( fn( $answer ) => filter_var( $answer['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN ) )
                ->count();

            if ( $correct !== 1 ) {
                $validator->errors()->add( "questions.$i.answers", "must have exactly one correct answer (found $correct)" );
            }
        }
    }

    /**
     * Turn keys like "2.questions.4.answers" into "Quiz 3 “Title”, question 5: ...".
     */
    private function readableErrors( array $messages, array $quizzes ): array {
        $lines = [];

        foreach ( $messages as $key => $errors ) {
            preg_match( '/^(\d+)(?:\.questions\.(\d+))?/', $key, $match );
            $quizIndex = (int) ( $match[1] ?? 0 );
            $title     = Str::limit( (string) ( $quizzes[$quizIndex]['title'] ?? 'untitled' ), 40 );
            $where     = 'Quiz ' . ( $quizIndex + 1 ) . " “{$title}”" . ( isset( $match[2] ) ? ', question ' . ( $match[2] + 1 ) : '' );

            foreach ( $errors as $error ) {
                // Drop the long dotted attribute name from Laravel's message
                $lines[] = "$where: " . preg_replace( '/The [\w.]+ field /', '', $error );
            }
        }

        return array_slice( $lines, 0, 10 );
    }

    private function createQuiz( array $data ): void {
        $quiz = Quiz::create( [
            'category_id'      => $this->categoryId( $data['category'] ),
            'title'            => $data['title'],
            'slug'             => Str::slug( $data['title'] ),
            'description'      => $data['description'] ?? null,
            'difficulty'       => Difficulty::tryFrom( (string) ( $data['difficulty'] ?? '' ) ) ?? Difficulty::Standard,
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'start_exam_at'    => $data['start_exam_at'] ?? null,
            'end_exam_at'      => $data['end_exam_at'] ?? null,
        ] );

        $this->saveQuestions( $quiz, $data['questions'] );
    }

    /**
     * Find a category by name or slug (case-insensitive), creating it if needed.
     */
    private function categoryId( string $name ): int {
        $name = trim( $name );
        $key  = Str::lower( $name );

        return $this->categoryIds[$key] ??= Category::query()
            ->whereRaw( 'LOWER(name) = ?', [$key] )
            ->orWhere( 'slug', Str::slug( $name ) )
            ->value( 'id' )
            ?? Category::create( ['name' => $name, 'slug' => Str::slug( $name ), 'status' => true] )->id;
    }
}
