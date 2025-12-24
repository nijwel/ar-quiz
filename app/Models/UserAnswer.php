<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAnswer extends Model {

    protected $table = 'user_answers';

    protected $fillable = [
        'user_id',
        'question_id',
        'answer_id',
        'quiz_id',
        'status',
    ];

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