<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAnswer;

class HomeController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware( 'auth' );
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index() {
        $userStats = auth()->user()->userAnswers()
            ->selectRaw( 'COUNT(CASE WHEN status = "correct" THEN 1 END) as total_correct,
                     COUNT(question_id) as total_attempted,
                     COUNT(DISTINCT quiz_id) as total_quizzes' )
            ->first();

        $totalMarks     = $userStats->total_correct ?? 0;
        $quiz_attempted = $userStats->total_quizzes ?? 0;
        $allUsersStats  = UserAnswer::selectRaw( 'user_id,
            (COUNT(CASE WHEN status = "correct" THEN 1 END) * 100.0 / NULLIF(COUNT(question_id), 0)) as average_score' )
            ->groupBy( 'user_id' )
            ->orderByDesc( 'average_score' )
            ->get();

        $ranking = $allUsersStats->search( function ( $item ) {
            return $item->user_id === auth()->id();
        } );

        $ranking = ( $ranking !== false ) ? $ranking + 1 : 0;

        return view( 'user.home', compact( 'totalMarks', 'quiz_attempted', 'ranking' ) );
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function adminHome() {
        return view( 'admin.home' );
    }
}
