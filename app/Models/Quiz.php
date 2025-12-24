<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model {

    protected $fillable = [
        'title',
        'slug',
        'description',
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'         => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the questions for the quiz.
     */
    public function questions() {
        return $this->hasMany( Question::class );
    }

}