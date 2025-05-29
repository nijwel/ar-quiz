<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Quiz;
use App\Models\UserAnswer;
use Illuminate\Http\Request;

class UserAnswerController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $totalMarks = UserAnswer::where( 'user_id', auth()->id() )->get()->where( 'status', 'correct' )->count();
        $quizzes    = Quiz::with( 'questions.answers' )->get();
        return view( 'user.quiz.index', compact( 'quizzes', 'totalMarks' ) );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store( Request $request ) {

        $quiz  = Quiz::find( $request->quiz_id );
        $score = 0;

        foreach ( $request->answers as $questionId => $answerId ) {
            $answer = Answer::find( $answerId );
            if ( $answer->is_correct ) {
                $score++;
            }

            UserAnswer::create( [
                'user_id'     => auth()->id(),
                'question_id' => $questionId,
                'answer_id'   => $answerId,
                'quiz_id'     => $quiz->id,
                'status'      => $answer->is_correct == 1 ? 'correct' : 'incorrect',
            ] );
        }

        return redirect()->route( 'user.quiz.index' )->with( 'success', "You scored $score out of " . $quiz->questions->count() );
    }

    /**
     * Display the specified resource.
     */
    public function show( string $quiz ) {
        $quiz = Quiz::with( 'questions.answers' )->whereSlug( $quiz )->first();
        return view( 'user.quiz.view', compact( 'quiz' ) );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit( string $id ) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update( Request $request, string $id ) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( string $id ) {
        //
    }
}
