<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParticipantController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $participants = User::whereType( 'user' )
            ->withCount( ['quizResults' => fn( $query ) => $query->latestAttempt()] )
            ->withCount( ['userAnswers as quizzes_attempted_count' => function ( $query ) {
                $query->select( DB::raw( 'count(distinct quiz_id)' ) );
            }] )
            ->paginate( 15 );
        return view( 'admin.participants.index', compact( 'participants' ) );
    }

    public function myExams() {
        $latestAttempts = UserAnswer::latestAttemptSummary( auth()->id() );
        $maxAttempts    = Setting::maxAttempts();

        $quizzes = Quiz::with( 'category:id,name' )
            ->withCount( 'questions' )
            ->whereIn( 'id', $latestAttempts->keys() )
            ->get();

        $quizResults = $quizzes->mapWithKeys( fn( Quiz $quiz ) => [
            $quiz->id => $quiz->resultFor( $latestAttempts->get( $quiz->id ), $maxAttempts ),
        ] );

        return view( 'user.quiz.my_exams', [
            'quizzes'     => $quizzes,
            'quizResults' => $quizResults,
            'totalMarks'  => $latestAttempts->sum( 'correct' ),
        ] );
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show( string $id ) {
        $participant = User::findOrFail( $id );

        // Each quiz with only this student's latest attempt
        $quizzes = Quiz::with( [
            'category:id,name',
            'userAnswers' => fn( $query ) => $query
                ->where( 'user_id', $participant->id )
                ->latestAttempt()
                ->select( 'id', 'quiz_id', 'user_id', 'attempt', 'status' ),
        ] )
            ->withCount( 'questions' )
            ->whereHas( 'userAnswers', fn( $query ) => $query->where( 'user_id', $participant->id ) )
            ->get();

        return view( 'admin.participants.view', compact( 'participant', 'quizzes' ) );
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