<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Setting;
use App\Models\User;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class ParticipantController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $participants = User::whereType( 'user' )
            ->withSum( ['quizAttempts as total_marks' => fn( $query ) => $query->latestSubmitted()], 'correct_count' )
            ->withCount( ['quizAttempts as quizzes_attempted_count' => fn( $query ) => $query->latestSubmitted()] )
            ->paginate( 15 );
        return view( 'admin.participants.index', compact( 'participants' ) );
    }

    public function myExams() {
        $latestAttempts = QuizAttempt::latestSummary( auth()->id() );
        $maxAttempts    = Setting::maxAttempts();

        // Keep the summary's order: the most recently taken exam first
        $position = $latestAttempts->keys()->flip();
        $quizzes  = Quiz::with( 'category:id,name' )
            ->withCount( 'questions' )
            ->whereIn( 'id', $latestAttempts->keys() )
            ->get()
            ->sortBy( fn( Quiz $quiz ) => $position[$quiz->id] )
            ->values();

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
        $participant = User::with( 'interests:id,name' )->findOrFail( $id );

        // Latest submitted attempt of every quiz this student took
        $latestAttempts = QuizAttempt::latestSummary( $participant->id );

        $quizzes = Quiz::with( 'category:id,name' )
            ->withCount( 'questions' )
            ->whereIn( 'id', $latestAttempts->keys() )
            ->get();

        return view( 'admin.participants.view', compact( 'participant', 'quizzes', 'latestAttempts' ) );
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