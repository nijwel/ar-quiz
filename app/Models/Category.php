<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Category extends Model {
    protected $guarded = [];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function quizzes() {
        return $this->hasMany( Quiz::class );
    }

    /**
     * Categories shown to students.
     */
    public function scopeActive( Builder $query ): void {
        $query->where( 'status', true );
    }
}
