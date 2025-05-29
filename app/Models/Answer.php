<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model {
    protected $fillable = [
        'answer',
        'is_correct',
        'question_id',
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'          => 'integer',
        'is_correct'  => 'boolean',
        'question_id' => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];
    /**
     * Get the question that owns the answer.
     */
    public function question() {
        return $this->belongsTo( Question::class );
    }
}
