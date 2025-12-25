<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Quiz;
use App\Models\UserAnswer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserAnswerController extends Controller {
    /**
     * Display a listing of the resource.
     */
    // public function index() {
    //     $totalMarks = UserAnswer::where( 'user_id', auth()->id() )->get()->where( 'status', 'correct' )->count();
    //     $quizzes    = Quiz::with( 'questions.answers' )->get();
    //     return view( 'user.quiz.index', compact( 'quizzes', 'totalMarks' ) );
    // }

    public function index() {
        $userId = auth()->id();

        // Total marks (DB level filtering)
        $totalMarks = UserAnswer::where( 'user_id', $userId )
            ->where( 'status', 'correct' )
            ->count();

        // Quizzes with questions
        $quizzes = Quiz::with( 'questions' )->get();

        // All answers of this user (ONE query)
        $userAnswers = UserAnswer::where( 'user_id', $userId )->get();

        // Quiz wise result prepare
        $quizResults = [];

        foreach ( $quizzes as $quiz ) {
            $answers = $userAnswers->where( 'quiz_id', $quiz->id );

            $right = $answers->where( 'status', 'correct' )->count();
            $wrong = $answers->where( 'status', 'incorrect' )->count();
            $total = $answers->count();

            $quizResults[$quiz->id] = [
                'marks'           => $right,
                'total_questions' => $quiz->questions->count(),
                'total_answers'   => $total,
                'right'           => $right,
                'wrong'           => $wrong,
                'attempted'       => $total > 0,
            ];
        }

        return view( 'user.quiz.index', compact(
            'quizzes',
            'totalMarks',
            'quizResults'
        ) );
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
    // public function show( string $quiz ) {
    //     $quiz = Quiz::with( 'questions.answers' )->whereSlug( $quiz )->first();
    //     return view( 'user.quiz.view', compact( 'quiz' ) );
    // }
    public function show( string $quiz ) {
        $quiz = Quiz::whereSlug( $quiz )
            ->with( ['questions' => function ( $query ) {
                $query->inRandomOrder()
                    ->with( ['answers' => function ( $q ) {
                        $q->inRandomOrder();
                    }] );
            }] )
            ->firstOrFail();

        if ( $quiz->start_exam_at && now()->lt( $quiz->start_exam_at ) ) {
            return redirect()
                ->route( 'user.quiz.index' )
                ->with(
                    'warning',
                    'The quiz is not available at the moment. It is scheduled to start on ' . Carbon::parse( $quiz->start_exam_at )->toDayDateTimeString() . '.'
                );

        }

        return view( 'user.quiz.view', compact( 'quiz' ) );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function result( string $quizSlug ) {
        $quiz = Quiz::with( 'questions.answers' )
            ->whereSlug( $quizSlug )
            ->firstOrFail();

        $userAnswers = UserAnswer::where( 'user_id', auth()->id() )
            ->where( 'quiz_id', $quiz->id )
            ->get()
            ->keyBy( 'question_id' );

        // Transform questions for user's answers
        $quiz->questions->transform( function ( $question ) use ( $userAnswers ) {
            $userAnswer = $userAnswers->get( $question->id );

            $question->is_correct     = false; // default
            $question->user_answer_id = $userAnswer->answer_id ?? null;

            if ( $userAnswer ) {
                $question->is_correct = $question->answers
                    ->where( 'id', $userAnswer->answer_id )
                    ->where( 'is_correct', true )
                    ->isNotEmpty();
            }

            return $question;
        } );

        // Calculate summary
        $totalQuestions = $quiz->questions->count();
        $totalMarks     = $quiz->questions->where( 'is_correct', true )->count();
        $totalAnswers   = $userAnswers->count();
        $rightAnswers   = $quiz->questions->where( 'is_correct', true )->count();
        $wrongAnswers   = $quiz->questions->where( 'is_correct', false )->whereNotNull( 'user_answer_id' )->count();

        return view( 'user.quiz.result', compact(
            'quiz',
            'totalMarks',
            'totalQuestions',
            'totalAnswers',
            'rightAnswers',
            'wrongAnswers'
        ) );
    }

    /**
     * Update the specified resource in storage.
     */
    public function leaderboard() {
        $leaderboard = UserAnswer::with( 'user' )
            ->selectRaw( 'user_id,
             COUNT(CASE WHEN status = "correct" THEN 1 END) as total_correct,
             COUNT(question_id) as total_attempted,
             COUNT(DISTINCT quiz_id) as total_quizzes,
             (COUNT(CASE WHEN status = "correct" THEN 1 END) * 100.0 / NULLIF(COUNT(question_id), 0)) as average_score' )
            ->groupBy( 'user_id' )
            ->orderByDesc( 'average_score' ) // গড় নম্বরের ভিত্তিতে র‍্যাঙ্কিং
            ->orderByDesc( 'total_quizzes' ) // গড় সমান হলে যে বেশি পরীক্ষা দিয়েছে সে আগে থাকবে
            ->get();

        return view( 'user.quiz.leaderboard', compact( 'leaderboard' ) );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( string $id ) {
        //
    }
}