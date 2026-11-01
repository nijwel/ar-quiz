<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class UserAnswer extends Model {

    protected $table = 'user_answers';

    protected $fillable = [
        'user_id',
        'question_id',
        'answer_id',
        'quiz_id',
        'attempt',
        'status',
    ];

    /**
     * Keep only each student's latest attempt per quiz, so retakes don't add up
     * in totals, rankings and quiz cards.
     */
    public function scopeLatestAttempt( $query ) {
        return $query->whereRaw( 'user_answers.attempt = (
            SELECT MAX(ua2.attempt) FROM user_answers ua2
            WHERE ua2.user_id = user_answers.user_id AND ua2.quiz_id = user_answers.quiz_id
        )' );
    }

    /**
     * How many times a student has taken a quiz (0 if never).
     */
    public static function attemptsTaken( int $userId, int $quizId ): int {
        return (int) self::where( 'user_id', $userId )->where( 'quiz_id', $quizId )->max( 'attempt' );
    }

    /**
     * One row per quiz with the student's latest attempt number and its score:
     * quiz_id => {attempt, answered, correct}
     */
    public static function latestAttemptSummary( int $userId ): Collection {
        return self::query()
            ->latestAttempt()
            ->where( 'user_id', $userId )
            ->selectRaw( "quiz_id, attempt, COUNT(*) as answered, SUM(CASE WHEN status = 'correct' THEN 1 ELSE 0 END) as correct" )
            ->groupBy( 'quiz_id', 'attempt' )
            ->toBase()
            ->get()
            ->keyBy( 'quiz_id' );
    }

    public function user() {
        return $this->belongsTo( User::class );
    }

    public function question() {
        return $this->belongsTo( Question::class );
    }

    public function answer() {
        return $this->belongsTo( Answer::class );
    }

    public function quiz() {
        return $this->belongsTo( Quiz::class, 'quiz_id' );
    }
}