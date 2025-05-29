<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model {

    protected $fillable = [
        'question',
        'quiz_id',
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'         => 'integer',
        'quiz_id'    => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    /**
     * Get the quiz that owns the question.
     */
    public function quiz() {
        return $this->belongsTo( Quiz::class );
    }

    /**
     * Get the answers for the question.
     */
    public function answers() {
        return $this->hasMany( Answer::class );
    }
}
