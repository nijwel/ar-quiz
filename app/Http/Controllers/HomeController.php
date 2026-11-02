<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
        // Only each quiz's latest attempt counts, so retakes don't inflate the totals
        $latestAttempts = QuizAttempt::latestSummary( auth()->id() );
        $totalMarks     = $latestAttempts->sum( 'correct' );
        $quiz_attempted = $latestAttempts->count();

        // Same ordering as the leaderboard page
        $rankedUserIds = QuizAttempt::query()->leaderboard()->pluck( 'user_id' );

        $position = $rankedUserIds->search( auth()->id() );
        $ranking  = $position === false ? 0 : $position + 1;

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

        $activeCount = Quiz::where( 'start_exam_at', '<=', now() )
            ->where( 'end_exam_at', '>=', now() )
            ->count();

        $recentQuizzes = Quiz::with( 'category:id,name' )
            ->withCount( 'questions' )
            ->latest()
            ->latest( 'id' ) // Tie-breaker: many quizzes share a created_at, which made pages overlap
            ->take( 5 )
            ->get();

        return view( 'admin.home', compact( 'quizCount', 'userCount', 'activeCount', 'recentQuizzes' ) );
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
        return view( 'user.profile', [
            'interests' => auth()->user()->interests()->with( 'children:id,parent_id,name' )->orderBy( 'name' )->get( ['categories.id', 'categories.name'] ),
        ] );
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
