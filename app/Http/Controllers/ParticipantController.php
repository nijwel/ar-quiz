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
            ->withCount( 'quizResults' )
            ->withCount( ['userAnswers as quizzes_attempted_count' => function ( $query ) {
                $query->select( DB::raw( 'count(distinct quiz_id)' ) );
            }] )
            ->paginate( 15 );
        return view( 'admin.participants.index', compact( 'participants' ) );
    }

    public function myExams() {
        $userId = auth()->id();

        $quizIds = DB::table( 'user_answers' )
            ->where( 'user_id', $userId )
            ->distinct()
            ->pluck( 'quiz_id' );

        $quizzes = Quiz::whereIn( 'id', $quizIds )
            ->withCount( 'questions' )
            ->with( ['userAnswers' => function ( $query ) use ( $userId ) {
                $query->where( 'user_id', $userId );
            }] )
            ->get();

        // ইউজারের অর্জিত মোট নম্বর (টোটাল পয়েন্ট) ক্যালকুলেশন
        $totalMarks = 0;
        foreach ( $quizzes as $quiz ) {
            $totalMarks += $quiz->userAnswers->filter( function ( $ans ) {
                return $ans->status == 'correct';
            } )->count();
        }

        return view( 'user.quiz.my_exams', compact( 'quizzes', 'totalMarks' ) );
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

        $quizIds = DB::table( 'user_answers' )
            ->where( 'user_id', $id )
            ->distinct()
            ->pluck( 'quiz_id' );

        $quizzes = Quiz::whereIn( 'id', $quizIds )
            ->withCount( 'questions' )
            ->with( ['userAnswers' => function ( $query ) use ( $id ) {
                $query->where( 'user_id', $id );
            }] )
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
