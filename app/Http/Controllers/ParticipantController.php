<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParticipantController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $participants = User::whereType( 'user' )
            ->withCount( 'quizResults' ) // total quizzes completed
            ->withCount( ['userAnswers as quizzes_attempted_count' => function ( $query ) {
                $query->select( DB::raw( 'count(distinct quiz_id)' ) );
            }] )
            ->paginate( 15 );
        return view( 'admin.participants.index', compact( 'participants' ) );
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
        $participant     = User::findOrFail( $id );
        $participantQuiz = $participant->userAnswers()->distinct()->pluck( 'quiz_id' )->toArray();

        $quizzes = Quiz::whereIn( 'id', $participantQuiz )->get();
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