<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuizController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $quizzes = Quiz::latest()
            ->when( request()->category, function ( $quiz, $category ) {
                $quiz->where( 'category_id', $category );
            } )
            ->with( 'questions', 'category' )
            ->withCount( 'questions' )
            ->paginate( 10 );
        $categories = Category::where( 'status', true )->get();
        return view( 'admin.quiz.index', compact( 'quizzes', 'categories' ) );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {

        $categories = Category::where( 'status', true )->get();
        return view( 'admin.quiz.create', compact( 'categories' ) );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store( Request $request ) {
        $request->validate( [
            'title'               => 'required|string|unique:quizzes,title',
            'description'         => 'nullable|string',
            'start_exam_at'       => 'nullable|date',
            'end_exam_at'         => 'nullable|date|after:start_exam_at',
            'category_id'         => 'required|exists:categories,id',
            'questions'           => 'required|array',
            'questions.*.text'    => 'required|string',
            'questions.*.answers' => 'required|array|min:2',
            'questions.*.correct' => 'required',
        ] );

        try {
            $quiz = Quiz::create( [
                'category_id'   => $request->category_id,
                'title'         => $request->title,
                'slug'          => Str::slug( $request->title ),
                'description'   => $request->description,
                'start_exam_at' => $request->start_exam_at,
                'end_exam_at'   => $request->end_exam_at,
            ] );

            foreach ( $request->questions as $questionData ) {
                $question = $quiz->questions()->create( [
                    'question' => $questionData['text'],
                ] );

                foreach ( $questionData['answers'] as $index => $answerText ) {
                    $isCorrect = (int) ( $index == $questionData['correct'] );
                    $question->answers()->create( [
                        'answer'     => $answerText,
                        'is_correct' => $isCorrect,
                    ] );
                }
            }

            return redirect()->route( 'quiz.index' )->with( 'success', 'Quiz created successfully.' );
        } catch ( \Exception $e ) {
            return redirect()->back()->withErrors( ['error' => 'Failed to create quiz: ' . $e->getMessage()] );
        }
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
        $quiz       = Quiz::with( 'questions:id,quiz_id,question', 'questions.answers:id,question_id,answer,is_correct' )->findOrFail( $id );
        $categories = Category::where( 'status', true )->get();
        return view( 'admin.quiz.edit', compact( 'quiz', 'categories' ) );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update( Request $request, $id ) {
        $request->validate( [
            'category_id'         => 'required|exists:categories,id',
            'title'               => 'required|string|unique:quizzes,title,' . $id,
            'description'         => 'nullable|string',
            'start_exam_at'       => 'nullable|date',
            'end_exam_at'         => 'nullable|date|after:start_exam_at',
            'questions'           => 'required|array',
            'questions.*.text'    => 'required|string',
            'questions.*.answers' => 'required|array|min:2',
            'questions.*.correct' => 'required',
        ] );

        try {
            $quiz = Quiz::findOrFail( $id );

            $quiz->update( [
                'category_id'   => $request->category_id,
                'title'         => $request->title,
                'slug'          => Str::slug( $request->title ),
                'description'   => $request->description,
                'start_exam_at' => $request->start_exam_at,
                'end_exam_at'   => $request->end_exam_at,
            ] );

            foreach ( $quiz->questions as $question ) {
                $question->answers()->delete();
                $question->delete();
            }

            foreach ( $request->questions as $questionData ) {
                $question = $quiz->questions()->create( [
                    'question' => $questionData['text'],
                ] );

                foreach ( $questionData['answers'] as $index => $answerText ) {
                    $isCorrect = (int) ( $index == $questionData['correct'] );
                    $question->answers()->create( [
                        'answer'     => $answerText,
                        'is_correct' => $isCorrect,
                    ] );
                }
            }

            return redirect()->route( 'quiz.index' )->with( 'success', 'Quiz updated successfully.' );
        } catch ( \Exception $e ) {
            return redirect()->back()->withErrors( ['error' => 'Failed to update quiz: ' . $e->getMessage()] );
        }
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

            // Create quiz
            $quiz = Quiz::create( [
                'category_id'   => $categoryId,
                'title'         => $quizData['quiz']['title'],
                'slug'          => Str::slug( $quizData['quiz']['title'] ),
                'description'   => $quizData['quiz']['description'] ?? null,
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

}