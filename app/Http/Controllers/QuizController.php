<?php

namespace App\Http\Controllers;

use App\Enums\Difficulty;
use App\Http\Requests\QuizRequest;
use App\Models\Category;
use App\Models\Quiz;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuizController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index( Request $request ) {
        $difficulty = Difficulty::tryFrom( (string) $request->query( 'difficulty' ) );

        $quizzes = Quiz::query()
            ->with( 'category:id,name' )
            ->withCount( 'questions' )
            ->when( $request->query( 'category' ), fn( $query, $categoryId ) => $query->where( 'category_id', $categoryId ) )
            ->when( $difficulty, fn( $query ) => $query->where( 'difficulty', $difficulty ) )
            ->latest()
            ->paginate( 10 );

        return view( 'admin.quiz.index', [
            'quizzes'    => $quizzes,
            'categories' => $this->activeCategories(),
        ] );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        return view( 'admin.quiz.create', [
            'categories'         => $this->activeCategories(),
            'secondsPerQuestion' => Setting::secondsPerQuestion(),
        ] );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store( QuizRequest $request ) {
        DB::transaction( function () use ( $request ) {
            $quiz = Quiz::create( $request->quizData() + ['slug' => Str::slug( $request->title )] );

            $this->saveQuestions( $quiz, $request->input( 'questions' ) );
        } );

        return redirect()->route( 'quiz.index' )->with( 'success', 'Quiz created successfully.' );
    }

    /**
     * Display the specified resource.
     */
    public function show( string $id ) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit( string $id ) {
        $quiz = Quiz::with( [
            'questions:id,quiz_id,question',
            'questions.answers:id,question_id,answer,is_correct',
        ] )->findOrFail( $id );

        return view( 'admin.quiz.edit', [
            'quiz'               => $quiz,
            'categories'         => $this->activeCategories(),
            'secondsPerQuestion' => Setting::secondsPerQuestion(),
        ] );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update( QuizRequest $request, string $id ) {
        $quiz = Quiz::findOrFail( $id );

        DB::transaction( function () use ( $quiz, $request ) {
            $quiz->update( $request->quizData() + ['slug' => Str::slug( $request->title )] );

            // Questions are replaced as a whole; answers are removed by the FK cascade
            $quiz->questions()->delete();
            $this->saveQuestions( $quiz, $request->input( 'questions' ) );
        } );

        return redirect()->route( 'quiz.index' )->with( 'success', 'Quiz updated successfully.' );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( string $id ) {

        try {
            $quiz = Quiz::findOrFail( $id );
            // $quiz->questions()->each( function ( $question ) {
            //     $question->answers()->delete();
            //     $question->delete();
            // } );
            $quiz->delete();

            return redirect()->route( 'quiz.index' )->with( 'success', 'Quiz deleted successfully.' );
        } catch ( \Exception $e ) {
            return redirect()->back()->withErrors( ['error' => 'Failed to delete quiz: ' . $e->getMessage()] );
        }
    }

    /**
     * Handle quiz file upload and import.
     */
    // public function upload( Request $request ) {
    //     $request->validate( [
    //         'quiz_file' => 'required|file|mimes:json',
    //     ] );

    //     $file = $request->file( 'quiz_file' );

    //     $quizData = json_decode( file_get_contents( $file->path() ), true );

    //     Log::info( 'Quiz Data: ', $quizData );

    //     if ( !isset( $quizData['quiz'] ) || !isset( $quizData['quiz']['title'] ) ) {
    //         return redirect()->back()->withErrors( ['error' => 'Invalid quiz file format.'] );
    //     }

    //     $existingQuiz = Quiz::where( 'title', $quizData['quiz']['title'] )->first();
    //     if ( $existingQuiz ) {
    //         return redirect()->back()->withErrors( ['error' => 'A quiz with the same title already exists.'] );
    //     }

    //     $quiz = Quiz::create( [
    //         'title'       => $quizData['quiz']['title'],
    //         'slug'        => Str::slug( $quizData['quiz']['title'] ),
    //         'description' => $quizData['quiz']['description'],
    //     ] );

    //     foreach ( $quizData['quiz']['questions'] as $questionData ) {
    //         $question = $quiz->questions()->create( [
    //             'question' => $questionData['question'],
    //         ] );

    //         foreach ( $questionData['answers'] as $answerData ) {
    //             $question->answers()->create( [
    //                 'answer'     => $answerData['answer'],
    //                 'is_correct' => $answerData['is_correct'],
    //             ] );
    //         }
    //     }

    //     return redirect()->route( 'quiz.index' )->with( 'success', 'Quiz imported successfully.' );
    // }

    // public function upload( Request $request ) {
    //     $request->validate( [
    //         'quiz_file' => 'nullable|file|mimes:json',
    //         'quiz_json' => 'nullable|string',
    //     ] );

    //     if ( $request->hasFile( 'quiz_file' ) ) {
    //         $quizData = json_decode(
    //             file_get_contents( $request->file( 'quiz_file' )->path() ),
    //             true
    //         );
    //     } elseif ( $request->filled( 'quiz_json' ) ) {
    //         $quizData = json_decode( $request->quiz_json, true );
    //     } else {
    //         return back()->withErrors( [
    //             'error' => 'Please upload a JSON file or paste quiz JSON data.',
    //         ] );
    //     }

    //     if ( !$quizData || !isset( $quizData['quiz']['title'] ) ) {
    //         return back()->withErrors( [
    //             'error' => 'Invalid quiz JSON format.',
    //         ] );
    //     }

    //     // Duplicate quiz check
    //     $existingQuiz = Quiz::where( 'title', $quizData['quiz']['title'] )->first();
    //     if ( $existingQuiz ) {
    //         return back()->withErrors( [
    //             'error' => 'A quiz with the same title already exists.',
    //         ] );
    //     }

    //     // Create quiz
    //     $quiz = Quiz::create( [
    //         'category_id'   => $quizData['quiz']['category_id'],
    //         'title'         => $quizData['quiz']['title'],
    //         'slug'          => Str::slug( $quizData['quiz']['title'] ),
    //         'description'   => $quizData['quiz']['description'] ?? null,
    //         'start_exam_at' => $quizData['quiz']['start_exam_at'] ?? null,
    //         'end_exam_at'   => $quizData['quiz']['end_exam_at'] ?? null,
    //     ] );

    //     foreach ( $quizData['quiz']['questions'] as $questionData ) {
    //         $question = $quiz->questions()->create( [
    //             'question' => $questionData['question'],
    //         ] );

    //         foreach ( $questionData['answers'] as $answerData ) {
    //             $question->answers()->create( [
    //                 'answer'     => $answerData['answer'],
    //                 'is_correct' => $answerData['is_correct'],
    //             ] );
    //         }
    //     }

    //     return redirect()->route( 'quiz.index' )
    //         ->with( 'success', 'Quiz imported successfully.' );
    // }

    public function upload( Request $request ) {
        $request->validate( [
            'quiz_file' => 'nullable|file|mimes:json',
            'quiz_json' => 'nullable|string',
        ] );

        // Read JSON
        if ( $request->hasFile( 'quiz_file' ) ) {
            $quizData = json_decode(
                file_get_contents( $request->file( 'quiz_file' )->path() ),
                true
            );
        } elseif ( $request->filled( 'quiz_json' ) ) {
            $quizData = json_decode( $request->quiz_json, true );
        } else {
            return back()->withErrors( ['error' => 'Please upload or paste quiz JSON.'] );
        }

        // Basic structure validation
        if (
            !$quizData ||
            !isset( $quizData['quiz']['title'] ) ||
            !isset( $quizData['quiz']['category'] ) ||
            !isset( $quizData['quiz']['questions'] )
        ) {
            return back()->withErrors( ['error' => 'Invalid quiz JSON structure.'] );
        }

        // Duplicate quiz check
        if ( Quiz::where( 'title', $quizData['quiz']['title'] )->exists() ) {
            return back()->withErrors( [
                'error' => 'A quiz with this title already exists.',
            ] );
        }

        DB::beginTransaction();

        try {
            // Resolve or create category
            $categoryId = $this->resolveCategory( $quizData['quiz']['category'] );

            $difficulty = Difficulty::tryFrom( strtolower( trim( $quizData['quiz']['difficulty'] ?? 'standard' ) ) );
            if ( !$difficulty ) {
                throw new \Exception( 'Invalid difficulty. Use one of: ' . implode( ', ', Difficulty::values() ) . '.' );
            }

            // Create quiz
            $quiz = Quiz::create( [
                'category_id'   => $categoryId,
                'title'         => $quizData['quiz']['title'],
                'slug'          => Str::slug( $quizData['quiz']['title'] ),
                'description'   => $quizData['quiz']['description'] ?? null,
                'difficulty'    => $difficulty,
                'duration_minutes' => ( (int) ( $quizData['quiz']['duration_minutes'] ?? 0 ) ) ?: null,
                'start_exam_at' => $quizData['quiz']['start_exam_at'] ?? null,
                'end_exam_at'   => $quizData['quiz']['end_exam_at'] ?? null,
            ] );

            // Questions & answers
            foreach ( $quizData['quiz']['questions'] as $questionData ) {

                if (
                    !isset( $questionData['question'] ) ||
                    !isset( $questionData['answers'] ) ||
                    !is_array( $questionData['answers'] )
                ) {
                    throw new \Exception( 'Invalid question format.' );
                }

                $question = $quiz->questions()->create( [
                    'question' => $questionData['question'],
                ] );

                foreach ( $questionData['answers'] as $answerData ) {
                    if ( !isset( $answerData['answer'], $answerData['is_correct'] ) ) {
                        throw new \Exception( 'Invalid answer format.' );
                    }

                    $question->answers()->create( [
                        'answer'     => $answerData['answer'],
                        'is_correct' => (bool) $answerData['is_correct'],
                    ] );
                }
            }

            DB::commit();

            return redirect()
                ->route( 'quiz.index' )
                ->with( 'success', 'Quiz imported successfully.' );

        } catch ( \Exception $e ) {
            DB::rollBack();

            return back()->withErrors( [
                'error' => $e->getMessage(),
            ] );
        }
    }

    /**
     * Resolve category by name or slug (case-insensitive).
     * Auto create if not exists.
     */
    private function resolveCategory( string $categoryName ): int {
        $name = trim( $categoryName );
        $slug = Str::slug( $name );

        $category = Category::whereRaw( 'LOWER(name) = ?', [strtolower( $name )] )
            ->orWhere( 'slug', $slug )
            ->first();

        if ( $category ) {
            return $category->id;
        }

        // Auto create category
        $newCategory = Category::create( [
            'name'   => $name,
            'slug'   => $slug,
            'status' => true,
        ] );

        return $newCategory->id;
    }

    /**
     * Create the questions of a quiz, inserting each question's answers in one query.
     */
    private function saveQuestions( Quiz $quiz, array $questions ): void {
        foreach ( $questions as $questionData ) {
            $question = $quiz->questions()->create( ['question' => $questionData['text']] );

            $answers = collect( $questionData['answers'] )->map( fn( $text, $index ) => [
                'answer'     => $text,
                'is_correct' => (int) $index === (int) $questionData['correct'],
            ] );

            $question->answers()->createMany( $answers->values()->all() );
        }
    }

    private function activeCategories() {
        return Category::where( 'status', true )->get( ['id', 'name'] );
    }
}
