<?php

namespace App\Http\Controllers;

use App\Enums\Difficulty;
use App\Enums\SubmitReason;
use App\Http\Requests\SubmitQuizRequest;
use App\Models\Answer;
use App\Models\Category;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Setting;
use App\Models\UserAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserAnswerController extends Controller {
    /** Seconds after the deadline that a submission still counts as on time. */
    private const SUBMIT_GRACE_SECONDS = 30;

    /**
     * Exam list with the student's latest result for each quiz.
     */
    public function index( Request $request ) {
        $userId      = auth()->id();
        $maxAttempts = Setting::maxAttempts();
        $difficulty  = Difficulty::tryFrom( (string) $request->query( 'difficulty' ) );
        $categoryId  = $request->filled( 'category' )
            ? Category::active()->where( 'slug', $request->query( 'category' ) )->value( 'id' )
            : null;

        $quizzes = Quiz::query()
            ->availableToStudents()
            ->with( 'category:id,name' )
            ->withCount( 'questions' )
            ->when( $categoryId, fn( $query ) => $query->where( 'category_id', $categoryId ) )
            ->when( $difficulty, fn( $query ) => $query->where( 'difficulty', $difficulty ) )
            ->latest()
            ->get();

        // Number of exams per level (within the selected category) for the level tiles
        $levelCounts = Quiz::query()
            ->availableToStudents()
            ->when( $categoryId, fn( $query ) => $query->where( 'category_id', $categoryId ) )
            ->selectRaw( 'difficulty, COUNT(*) as total' )
            ->groupBy( 'difficulty' )
            ->toBase()
            ->pluck( 'total', 'difficulty' );

        $latestAttempts = QuizAttempt::latestSummary( $userId );

        $quizResults = $quizzes->mapWithKeys( fn( Quiz $quiz ) => [
            $quiz->id => $quiz->resultFor( $latestAttempts->get( $quiz->id ), $maxAttempts ),
        ] );

        return view( 'user.quiz.index', [
            'quizzes'     => $quizzes,
            'quizResults' => $quizResults,
            'totalMarks'  => $latestAttempts->sum( 'correct' ),
            'categories'  => Category::active()->get( ['id', 'name', 'slug'] ),
            'difficulty'  => $difficulty,
            'levelCounts' => $levelCounts,
        ] );
    }

    /**
     * Start screen, or the running exam if the student already started it.
     */
    public function show( string $slug ) {
        $quiz = Quiz::with( 'category:id,name,status' )->whereSlug( $slug )->firstOrFail();

        if ( $unavailable = $this->unavailableReason( $quiz ) ) {
            return redirect()->route( 'user.quiz.index' )->with( ...$unavailable );
        }

        $attempt = $this->openAttempt( $quiz );
        $taken   = QuizAttempt::countFor( auth()->id(), $quiz->id );

        if ( !$attempt && $taken >= Setting::maxAttempts() ) {
            return redirect()->route( 'user.quiz.result', $quiz->slug )
                ->with( 'warning', 'আপনি এই পরীক্ষার সর্বোচ্চ সংখ্যক চেষ্টা (' . Setting::maxAttempts() . ') শেষ করেছেন।' );
        }

        $questionCount = $quiz->questions()->count();

        return view( 'user.quiz.view', [
            'quiz'               => $quiz,
            'questionCount'      => $questionCount,
            'examSeconds'        => $quiz->examSeconds( $questionCount ),
            'secondsPerQuestion' => Setting::secondsPerQuestion(),
            'attemptNumber'      => $attempt?->attempt ?? $taken + 1,
            'maxAttempts'        => Setting::maxAttempts(),
            // A running attempt (e.g. after a failed auto-submit) resumes with its server-side time left
            'resumeAttempt'      => $attempt,
            'questionsHtml'      => $attempt ? $this->renderQuestions( $quiz ) : null,
        ] );
    }

    /**
     * Begin a new attempt and send the questions. Questions are never in the page
     * before this, so they cannot be read from the page source in advance.
     */
    public function start( string $slug ) {
        $quiz = Quiz::with( 'category:id,status' )->whereSlug( $slug )->firstOrFail();

        if ( $unavailable = $this->unavailableReason( $quiz ) ) {
            return response()->json( ['message' => $unavailable[1]], 422 );
        }

        $attempt = $this->openAttempt( $quiz );

        if ( !$attempt ) {
            $taken = QuizAttempt::countFor( auth()->id(), $quiz->id );

            if ( $taken >= Setting::maxAttempts() ) {
                return response()->json( ['message' => 'আপনি এই পরীক্ষার সর্বোচ্চ সংখ্যক চেষ্টা শেষ করেছেন।'], 403 );
            }

            $attempt = QuizAttempt::create( [
                'user_id'    => auth()->id(),
                'quiz_id'    => $quiz->id,
                'attempt'    => $taken + 1,
                'started_at' => now(),
                'ends_at'    => now()->addSeconds( $quiz->examSeconds( $quiz->questions()->count() ) ),
            ] );
        }

        return response()->json( [
            'attempt'           => $attempt->attempt,
            'remaining_seconds' => $attempt->remainingSeconds(),
            'html'              => $this->renderQuestions( $quiz ),
        ] );
    }

    /**
     * Save the answers of the open attempt and close it.
     * Handles normal submits, time-up and anti-cheat auto-submits (sent with fetch or a beacon).
     */
    public function store( SubmitQuizRequest $request ) {
        $userId  = auth()->id();
        $quiz    = Quiz::findOrFail( $request->integer( 'quiz_id' ) );
        $attempt = QuizAttempt::ofStudent( $userId, $quiz->id )->inProgress()->latest( 'attempt' )->first();

        if ( !$attempt ) {
            return $this->submitResponse( $request, route( 'user.quiz.result', $quiz->slug ),
                'warning', 'এই পরীক্ষার কোনো চলমান চেষ্টা পাওয়া যায়নি।' );
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
                'attempt'     => $attempt->attempt,
                'question_id' => $questionId,
                'answer_id'   => $answerId,
                'status'      => $answers[$answerId]->is_correct ? 'correct' : 'incorrect',
                'created_at'  => $now,
                'updated_at'  => $now,
            ] );

        $reason = $request->enum( 'reason', SubmitReason::class ) ?? SubmitReason::Manual;

        // A short grace period covers network delay; anything later is flagged
        if ( $attempt->ends_at && $now->greaterThan( $attempt->ends_at->copy()->addSeconds( self::SUBMIT_GRACE_SECONDS ) ) ) {
            $reason = SubmitReason::Late;
        }

        $score = $rows->where( 'status', 'correct' )->count();

        DB::transaction( function () use ( $rows, $attempt, $reason, $score ) {
            UserAnswer::insert( $rows->values()->all() );
            $attempt->finish( $reason, $rows->count(), $score );
        } );

        $resultUrl = route( 'user.quiz.result', [$quiz->slug, 'attempt' => $attempt->attempt] );

        if ( $reason->isViolation() ) {
            return $this->submitResponse( $request, $resultUrl, 'warning',
                "আপনার পরীক্ষা স্বয়ংক্রিয়ভাবে জমা হয়েছে (কারণ: {$reason->banglaLabel()})। স্কোর: $score / {$questionIds->count()}" );
        }

        return $this->submitResponse( $request, $resultUrl, 'success', "You scored $score out of {$questionIds->count()}" );
    }

    /**
     * Review of one submitted attempt (the latest by default).
     */
    public function result( Request $request, string $slug ) {
        $userId = auth()->id();
        $quiz   = Quiz::with( 'category:id,status' )->whereSlug( $slug )->firstOrFail();

        $attemptsTaken = (int) QuizAttempt::ofStudent( $userId, $quiz->id )->submitted()->max( 'attempt' );
        if ( $attemptsTaken === 0 ) {
            return redirect()->route( 'user.quiz.show', $quiz->slug );
        }

        $attempt       = min( max( 1, $request->integer( 'attempt', $attemptsTaken ) ), $attemptsTaken );
        $attemptRecord = QuizAttempt::ofStudent( $userId, $quiz->id )->where( 'attempt', $attempt )->first();
        $showReview    = Setting::showAnswerReview();

        if ( $showReview ) {
            $quiz->load( 'questions:id,quiz_id,question', 'questions.answers:id,question_id,answer,is_correct' );

            // question_id => chosen answer_id for this attempt
            $chosen = UserAnswer::where( 'user_id', $userId )
                ->where( 'quiz_id', $quiz->id )
                ->where( 'attempt', $attempt )
                ->pluck( 'answer_id', 'question_id' );

            foreach ( $quiz->questions as $question ) {
                $question->user_answer_id = $chosen->get( $question->id );
                $question->is_correct     = (bool) $question->answers->firstWhere( 'id', $question->user_answer_id )?->is_correct;
            }

            $rightAnswers   = $quiz->questions->where( 'is_correct', true )->count();
            $wrongAnswers   = $chosen->count() - $rightAnswers;
            $totalQuestions = $quiz->questions->count();
        } else {
            // Review is turned off: correct answers are never loaded, the score comes from the attempt
            $rightAnswers   = (int) $attemptRecord?->correct_count;
            $wrongAnswers   = (int) $attemptRecord?->answered_count - $rightAnswers;
            $totalQuestions = $quiz->questions()->count();
        }

        return view( 'user.quiz.result', [
            'quiz'           => $quiz,
            'attempt'        => $attempt,
            'attemptsTaken'  => $attemptsTaken,
            'submitReason'   => $attemptRecord?->submit_reason,
            'submittedAt'    => $attemptRecord?->submitted_at,
            'canRetake'      => $quiz->isAvailableToStudents() && QuizAttempt::countFor( $userId, $quiz->id ) < Setting::maxAttempts(),
            'showReview'     => $showReview,
            'totalQuestions' => $totalQuestions,
            'totalMarks'     => $rightAnswers,
            'rightAnswers'   => $rightAnswers,
            'wrongAnswers'   => $wrongAnswers,
        ] );
    }

    /**
     * Ranking by accuracy, using each student's latest attempt per quiz.
     */
    public function leaderboard() {
        $leaderboard = QuizAttempt::query()->leaderboard()->with( 'user:id,name' )->get();

        return view( 'user.quiz.leaderboard', compact( 'leaderboard' ) );
    }

    /**
     * [flash type, message] if the quiz or its category is turned off or the quiz is outside
     * its schedule, otherwise null. Needs the category relation loaded.
     */
    private function unavailableReason( Quiz $quiz ): ?array {
        if ( !$quiz->isAvailableToStudents() ) {
            return ['error', 'এই পরীক্ষাটি এখন উপলব্ধ নয়।'];
        }

        if ( $quiz->end_exam_at && now()->gt( $quiz->end_exam_at ) ) {
            return ['error', 'The quiz is not available at the moment. It ended on ' . $quiz->end_exam_at->toDayDateTimeString() . '.'];
        }

        if ( $quiz->start_exam_at && now()->lt( $quiz->start_exam_at ) ) {
            return ['warning', 'The quiz is not available at the moment. It is scheduled to start on ' . $quiz->start_exam_at->toDayDateTimeString() . '.'];
        }

        return null;
    }

    /**
     * The student's running attempt, or null. An attempt whose time ran out while the
     * student was away is closed as abandoned.
     */
    private function openAttempt( Quiz $quiz ): ?QuizAttempt {
        $attempt = QuizAttempt::ofStudent( auth()->id(), $quiz->id )->inProgress()->latest( 'attempt' )->first();

        if ( $attempt && $attempt->ends_at && now()->greaterThan( $attempt->ends_at->copy()->addSeconds( self::SUBMIT_GRACE_SECONDS ) ) ) {
            $attempt->finish( SubmitReason::Expired );

            return null;
        }

        return $attempt;
    }

    /**
     * Questions and answer options in random order. is_correct is never loaded.
     */
    private function renderQuestions( Quiz $quiz ): string {
        $questions = $quiz->questions()
            ->select( 'id', 'quiz_id', 'question' )
            ->inRandomOrder()
            ->with( ['answers' => fn( $query ) => $query->select( 'id', 'question_id', 'answer' )->inRandomOrder()] )
            ->get();

        return view( 'user.quiz._questions', compact( 'quiz', 'questions' ) )->render();
    }

    /**
     * Redirect for normal form posts; JSON with the redirect URL for background (fetch) submits.
     */
    private function submitResponse( Request $request, string $url, string $type, string $message ) {
        session()->flash( $type, $message );

        return $request->expectsJson()
            ? response()->json( ['redirect' => $url] )
            : redirect( $url );
    }
}
