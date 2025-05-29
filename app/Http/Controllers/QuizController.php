<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuizController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $quizzes = Quiz::latest()->paginate( 10 );
        return view( 'admin.quiz.index', compact( 'quizzes' ) );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        return view( 'admin.quiz.create' );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store( Request $request ) {
        $request->validate( [
            'title'               => 'required|string|unique:quizzes,title',
            'description'         => 'nullable|string',
            'questions'           => 'required|array',
            'questions.*.text'    => 'required|string',
            'questions.*.answers' => 'required|array|min:2',
            'questions.*.correct' => 'required',
        ] );

        try {
            $quiz = Quiz::create( [
                'title'       => $request->title,
                'slug'        => Str::slug( $request->title ),
                'description' => $request->description,
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

            return redirect()->route( 'quizzes.index' )->with( 'success', 'Quiz created successfully.' );
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
        $quiz = Quiz::with( 'questions:id,quiz_id,question', 'questions.answers:id,question_id,answer,is_correct' )->findOrFail( $id );
        return view( 'admin.quiz.edit', compact( 'quiz' ) );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update( Request $request, $id ) {
        $request->validate( [
            'title'               => 'required|string|unique:tables,title,except,' . $id,
            'description'         => 'nullable|string',
            'questions'           => 'required|array',
            'questions.*.text'    => 'required|string',
            'questions.*.answers' => 'required|array|min:2',
            'questions.*.correct' => 'required',
        ] );

        try {
            $quiz = Quiz::findOrFail( $id );

            $quiz->update( [
                'title'       => $request->title,
                'slug'        => Str::slug( $request->title ),
                'description' => $request->description,
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

            return redirect()->route( 'quizzes.index' )->with( 'success', 'Quiz deleted successfully.' );
        } catch ( \Exception $e ) {
            return redirect()->back()->withErrors( ['error' => 'Failed to delete quiz: ' . $e->getMessage()] );
        }
    }
}