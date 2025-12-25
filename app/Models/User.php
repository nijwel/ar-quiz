<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'avatar',
        'student_id',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $table = 'users';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    protected static function boot() {
        parent::boot();

        static::creating( function ( $model ) {

            // default status
            if ( empty( $model->status ) ) {
                $model->status = 'active';
            }

            // ONLY user type
            if ( $model->type === 'user' && empty( $model->student_id ) ) {

                $lastStudent = self::where( 'type', 'user' )
                    ->orderBy( 'id', 'desc' )
                    ->first();

                $nextNumber = $lastStudent
                ? intval( substr( $lastStudent->student_id, 3 ) ) + 1
                : 1;

                $model->student_id = 'STU' . str_pad( $nextNumber, 4, '0', STR_PAD_LEFT );
            }
        } );

    }

    public function userAnswers() {
        return $this->hasMany( UserAnswer::class );
    }

    public function quizResults() {
        return $this->hasMany( UserAnswer::class )->where( 'status', 'correct' );
    }

}
