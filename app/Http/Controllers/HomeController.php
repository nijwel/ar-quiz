<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\User;
use App\Models\UserAnswer;
use Illuminate\Http\Request;

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

        $quizCount = Quiz::count();

        $userCount = User::whereType( 'user' )->count();
        return view( 'admin.home', compact( 'quizCount', 'userCount' ) );
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function adminProfile() {
        return view( 'admin.profile' );
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function userProfile() {
        return view( 'user.profile' );
    }

    public function updateProfile( Request $request ) {
        $request->validate( [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
        ] );

        auth()->user()->update( $request->only( 'name' ) );
        return back()->with( 'success', 'Profile updated successfully!' );
    }

    public function updatePassword( Request $request ) {
        $request->validate( [
            'current_password' => 'required|current_password',
            'password'         => 'required|min:8|confirmed',
        ] );

        auth()->user()->update( ['password' => Hash::make( $request->password )] );
        return back()->with( 'success', 'Password changed successfully!' );
    }
}
