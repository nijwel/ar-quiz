<?php

namespace App\Models;

use App\Enums\SubmitReason;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * One try at a quiz. Created when the student starts the exam and closed on submit,
 * so attempts are counted even if the student leaves without answering.
 */
class QuizAttempt extends Model {
    protected $fillable = [
        'user_id',
        'quiz_id',
        'attempt',
        'started_at',
        'ends_at',
        'submitted_at',
        'submit_reason',
        'answered_count',
        'correct_count',
    ];

    protected function casts(): array {
        return [
            'started_at'    => 'datetime',
            'ends_at'       => 'datetime',
            'submitted_at'  => 'datetime',
            'submit_reason' => SubmitReason::class,
        ];
    }

    public function user() {
        return $this->belongsTo( User::class );
    }

    public function quiz() {
        return $this->belongsTo( Quiz::class );
    }

    public function scopeOfStudent( Builder $query, int $userId, int $quizId ): Builder {
        return $query->where( 'user_id', $userId )->where( 'quiz_id', $quizId );
    }

    public function scopeInProgress( Builder $query ): Builder {
        return $query->whereNull( 'submitted_at' );
    }

    public function scopeSubmitted( Builder $query ): Builder {
        return $query->whereNotNull( 'submitted_at' );
    }

    /**
     * Only each student's latest submitted attempt per quiz, so retakes don't add up
     * in totals and rankings.
     */
    public function scopeLatestSubmitted( Builder $query ): Builder {
        return $query->whereNotNull( 'quiz_attempts.submitted_at' )
            ->whereRaw( 'quiz_attempts.attempt = (
                SELECT MAX(qa2.attempt) FROM quiz_attempts qa2
                WHERE qa2.user_id = quiz_attempts.user_id
                  AND qa2.quiz_id = quiz_attempts.quiz_id
                  AND qa2.submitted_at IS NOT NULL
            )' );
    }

    /**
     * One row per student, ranked by accuracy over their latest attempt of each quiz:
     * user_id, total_correct, total_attempted, total_quizzes, average_score
     */
    public function scopeLeaderboard( Builder $query ): Builder {
        return $query->latestSubmitted()
            ->selectRaw( 'user_id,
                SUM(correct_count) as total_correct,
                SUM(answered_count) as total_attempted,
                COUNT(*) as total_quizzes,
                SUM(correct_count) * 100.0 / NULLIF(SUM(answered_count), 0) as average_score' )
            ->groupBy( 'user_id' )
            ->orderByDesc( 'average_score' )  // Higher accuracy first
            ->orderByDesc( 'total_quizzes' ); // On a tie, more exams taken ranks higher
    }

    /**
     * The student's latest submitted attempt for every quiz they took, most recent first:
     * quiz_id => {attempt, answered, correct, submit_reason, submitted_at}
     */
    public static function latestSummary( int $userId ): Collection {
        return self::query()
            ->latestSubmitted()
            ->where( 'user_id', $userId )
            ->orderByDesc( 'submitted_at' )
            ->toBase()
            ->get( ['quiz_id', 'attempt', 'answered_count as answered', 'correct_count as correct', 'submit_reason', 'submitted_at'] )
            ->keyBy( 'quiz_id' );
    }

    /** Number of attempts a student has started for a quiz, finished or not. */
    public static function countFor( int $userId, int $quizId ): int {
        return (int) self::ofStudent( $userId, $quizId )->max( 'attempt' );
    }

    public function isExpired(): bool {
        return $this->ends_at && now()->greaterThan( $this->ends_at );
    }

    public function remainingSeconds(): int {
        return $this->ends_at ? (int) max( 0, now()->diffInSeconds( $this->ends_at, false ) ) : 0;
    }

    /** Close the attempt with its final counts. */
    public function finish( SubmitReason $reason, int $answered = 0, int $correct = 0 ): void {
        $this->update( [
            'submitted_at'   => now(),
            'submit_reason'  => $reason,
            'answered_count' => $answered,
            'correct_count'  => $correct,
        ] );
    }
}
