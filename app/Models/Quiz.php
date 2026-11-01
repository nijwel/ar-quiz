<?php

namespace App\Models;

use App\Enums\Difficulty;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model {

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'difficulty',
        'duration_minutes',
        'start_exam_at',
        'end_exam_at',
        'status',
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'         => 'integer',
        'difficulty' => Difficulty::class,
        'status'     => 'boolean',
        'duration_minutes' => 'integer',
        'start_exam_at' => 'datetime',
        'end_exam_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the questions for the quiz.
     */
    public function questions() {
        return $this->hasMany( Question::class, 'quiz_id' );
    }

    // app/Models/Quiz.php

    public function userAnswers() {
        return $this->hasMany( UserAnswer::class, 'quiz_id' );
    }

    public function category() {
        return $this->belongsTo( Category::class );
    }

    /**
     * Active quizzes in an active category, i.e. the ones students may see and take.
     */
    public function scopeAvailableToStudents( Builder $query ): void {
        $query->where( 'status', true )
            ->whereIn( 'category_id', Category::active()->select( 'id' ) );
    }

    public function isAvailableToStudents(): bool {
        return $this->status && $this->category?->status;
    }

    /**
     * Total exam time in seconds: the fixed duration if the admin set one,
     * otherwise questions x the "seconds per question" setting.
     */
    /**
     * Data for the student's quiz card, from a QuizAttempt::latestSummary() row.
     * Requires withCount('questions').
     */
    public function resultFor( ?object $latestAttempt, int $maxAttempts ): array {
        $attempts = (int) ( $latestAttempt->attempt ?? 0 );
        $right    = (int) ( $latestAttempt->correct ?? 0 );

        return [
            'attempted'       => $attempts > 0,
            'attempts'        => $attempts,
            'attempts_left'   => max( 0, $maxAttempts - $attempts ),
            'total_questions' => $this->questions_count,
            'exam_seconds'    => $this->examSeconds( $this->questions_count ),
            'right'           => $right,
            'wrong'           => (int) ( $latestAttempt->answered ?? 0 ) - $right,
            'marks'           => $right,
            'taken_at'        => isset( $latestAttempt->submitted_at ) ? Carbon::parse( $latestAttempt->submitted_at ) : null,
        ];
    }

    public function examSeconds( int $questionCount ): int {
        return $this->duration_minutes
            ? $this->duration_minutes * 60
            : $questionCount * Setting::secondsPerQuestion();
    }

}
