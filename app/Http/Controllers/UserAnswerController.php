<?php

namespace App\Http\Controllers;

use App\Enums\Difficulty;
use App\Http\Requests\SubmitQuizRequest;
use App\Models\Answer;
use App\Models\Category;
use App\Models\Quiz;
use App\Models\Setting;
use App\Models\UserAnswer;
use Illuminate\Http\Request;

class UserAnswerController extends Controller {
    /**
     * Exam list with the student's latest result for each quiz.
     */
    public function index( Request $request ) {
        $userId      = auth()->id();
        $maxAttempts = Setting::maxAttempts();
        $difficulty  = Difficulty::tryFrom( (string) $request->query( 'difficulty' ) );
        $categoryId  = $request->filled( 'category' )
            ? Category::where( 'slug', $request->query( 'category' ) )->value( 'id' )
            : null;

        $quizzes = Quiz::query()
            ->with( 'category:id,name' )
            ->withCount( 'questions' )
            ->when( $categoryId, fn( $query ) => $query->where( 'category_id', $categoryId ) )
            ->when( $difficulty, fn( $query ) => $query->where( 'difficulty', $difficulty ) )
            ->latest()
            ->get();

        // Number of exams per level (within the selected category) for the level tiles
        $levelCounts = Quiz::query()
            ->when( $categoryId, fn( $query ) => $query->where( 'category_id', $categoryId ) )
            ->selectRaw( 'difficulty, COUNT(*) as total' )
            ->groupBy( 'difficulty' )
            ->toBase()
            ->pluck( 'total', 'difficulty' );

        $latestAttempts = UserAnswer::latestAttemptSummary( $userId );

        $quizResults = $quizzes->mapWithKeys( fn( Quiz $quiz ) => [
            $quiz->id => $quiz->resultFor( $latestAttempts->get( $quiz->id ), $maxAttempts ),
        ] );

        return view( 'user.quiz.index', [
            'quizzes'     => $quizzes,
            'quizResults' => $quizResults,
            'totalMarks'  => $latestAttempts->sum( 'correct' ),
            'categories'  => Category::where( 'status', true )->get( ['id', 'name', 'slug'] ),
            'difficulty'  => $difficulty,
            'levelCounts' => $levelCounts,
        ] );
    }

    /**
     * Start screen and exam.
     */
    public function show( string $slug ) {
        $quiz = Quiz::with( 'category:id,name' )->whereSlug( $slug )->firstOrFail();

        if ( $quiz->end_exam_at && now()->gt( $quiz->end_exam_at ) ) {
            return redirect()->route( 'user.quiz.index' )->with( 'error',
                'The quiz is not available at the moment. It ended on ' . $quiz->end_exam_at->toDayDateTimeString() . '.' );
        }

        if ( $quiz->start_exam_at && now()->lt( $quiz->start_exam_at ) ) {
            return redirect()->route( 'user.quiz.index' )->with( 'warning',
                'The quiz is not available at the moment. It is scheduled to start on ' . $quiz->start_exam_at->toDayDateTimeString() . '.' );
        }

        $attemptsTaken = UserAnswer::attemptsTaken( auth()->id(), $quiz->id );
        $maxAttempts   = Setting::maxAttempts();

        if ( $attemptsTaken >= $maxAttempts ) {
            return redirect()->route( 'user.quiz.result', $quiz->slug )
                ->with( 'warning', 'আপনি এই পরীক্ষার সর্বোচ্চ সংখ্যক চেষ্টা (' . $maxAttempts . ') শেষ করেছেন।' );
        }

        // is_correct is intentionally not loaded so it never reaches the page
        $quiz->load( ['questions' => fn( $query ) => $query
                ->select( 'id', 'quiz_id', 'question' )
                ->inRandomOrder()
                ->with( ['answers' => fn( $query ) => $query->select( 'id', 'question_id', 'answer' )->inRandomOrder()] ),
        ] );

        return view( 'user.quiz.view', [
            'quiz'               => $quiz,
            'examSeconds'        => $quiz->examSeconds( $quiz->questions->count() ),
            'secondsPerQuestion' => Setting::secondsPerQuestion(),
            'attempt'            => $attemptsTaken + 1,
            'maxAttempts'        => $maxAttempts,
        ] );
    }

    /**
     * Save a submitted exam as a new attempt.
     */
    public function store( SubmitQuizRequest $request ) {
        $userId = auth()->id();
        $quiz   = Quiz::findOrFail( $request->integer( 'quiz_id' ) );

        $attempt = UserAnswer::attemptsTaken( $userId, $quiz->id ) + 1;
        if ( $attempt > Setting::maxAttempts() ) {
            return redirect()->route( 'user.quiz.result', $quiz->slug )
                ->with( 'warning', 'আপনি এই পরীক্ষার সর্বোচ্চ সংখ্যক চেষ্টা শেষ করেছেন।' );
        }

        // Only keep answers that really belong to this quiz's questions
        $questionIds = $quiz->questions()->pluck( 'id' );
        $submitted   = collect( $request->input( 'answers', [] ) )->only( $questionIds->all() );
        $answers     = Answer::whereIn( 'id', $submitted->values() )
            ->get( ['id', 'question_id', 'is_correct'] )
            ->keyBy( 'id' );

        $now  = now();
        $rows = $submitted
            ->filter( fn( $answerId, $questionId ) => $answers->get( $answerId )?->question_id == $questionId )
            ->map( fn( $answerId, $questionId ) => [
                'user_id'     => $userId,
                'quiz_id'     => $quiz->id,
                'attempt'     => $attempt,
                'question_id' => $questionId,
                'answer_id'   => $answerId,
                'status'      => $answers[$answerId]->is_correct ? 'correct' : 'incorrect',
                'created_at'  => $now,
                'updated_at'  => $now,
            ] );

        UserAnswer::insert( $rows->values()->all() );

        $score = $rows->where( 'status', 'correct' )->count();

        return redirect()
            ->route( 'user.quiz.result', [$quiz->slug, 'attempt' => $attempt] )
            ->with( 'success', "You scored $score out of {$questionIds->count()}" );
    }

    /**
     * Review of one attempt (the latest by default).
     */
    public function result( Request $request, string $slug ) {
        $userId = auth()->id();
        $quiz   = Quiz::with( 'questions:id,quiz_id,question', 'questions.answers:id,question_id,answer,is_correct' )
            ->whereSlug( $slug )
            ->firstOrFail();

        $attemptsTaken = UserAnswer::attemptsTaken( $userId, $quiz->id );
        if ( $attemptsTaken === 0 ) {
            return redirect()->route( 'user.quiz.show', $quiz->slug );
        }

        $attempt = min( max( 1, $request->integer( 'attempt', $attemptsTaken ) ), $attemptsTaken );

        // question_id => chosen answer_id for this attempt
        $chosen = UserAnswer::where( 'user_id', $userId )
            ->where( 'quiz_id', $quiz->id )
            ->where( 'attempt', $attempt )
            ->pluck( 'answer_id', 'question_id' );

        foreach ( $quiz->questions as $question ) {
            $question->user_answer_id = $chosen->get( $question->id );
            $question->is_correct     = (bool) $question->answers->firstWhere( 'id', $question->user_answer_id )?->is_correct;
        }

        $rightAnswers = $quiz->questions->where( 'is_correct', true )->count();

        return view( 'user.quiz.result', [
            'quiz'          => $quiz,
            'attempt'       => $attempt,
            'attemptsTaken' => $attemptsTaken,
            'canRetake'     => $attemptsTaken < Setting::maxAttempts(),
            'totalMarks'    => $rightAnswers,
            'rightAnswers'  => $rightAnswers,
            'wrongAnswers'  => $chosen->count() - $rightAnswers,
        ] );
    }

    /**
     * Ranking by accuracy, using each student's latest attempt per quiz.
     */
    public function leaderboard() {
        $leaderboard = UserAnswer::query()
            ->latestAttempt()
            ->with( 'user:id,name' )
            ->selectRaw( "user_id,
                SUM(CASE WHEN status = 'correct' THEN 1 ELSE 0 END) as total_correct,
                COUNT(*) as total_attempted,
                COUNT(DISTINCT quiz_id) as total_quizzes,
                SUM(CASE WHEN status = 'correct' THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as average_score" )
            ->groupBy( 'user_id' )
            ->orderByDesc( 'average_score' )  // Higher accuracy first
            ->orderByDesc( 'total_quizzes' )  // On a tie, more exams taken ranks higher
            ->get();

        return view( 'user.quiz.leaderboard', compact( 'leaderboard' ) );
    }
}
