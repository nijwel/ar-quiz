<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Category extends Model {
    protected $guarded = [];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function quizzes() {
        return $this->hasMany( Quiz::class );
    }

    /** The group this category belongs to, e.g. Programming for PHP. */
    public function parent() {
        return $this->belongsTo( self::class, 'parent_id' );
    }

    public function children() {
        return $this->hasMany( self::class, 'parent_id' );
    }

    /** Students who chose this subject. */
    public function students() {
        return $this->belongsToMany( User::class )->withTimestamps();
    }

    /**
     * Active groups and stand-alone subjects that have at least one available exam, each
     * with its active sub-categories that have exams. Two queries. Adds exam_count
     * (own exams plus those of the sub-categories).
     */
    public static function subjectsWithExams(): Collection {
        $countExams = ['quizzes' => fn( $query ) => $query->where( 'status', true )];

        return self::active()
            ->topLevel()
            ->withCount( $countExams )
            ->with( ['children' => fn( $query ) => $query->active()->withCount( $countExams )->orderBy( 'name' )] )
            ->orderBy( 'name' )
            ->get()
            ->each( function ( self $subject ) {
                $subject->setRelation( 'children', $subject->children->where( 'quizzes_count', '>', 0 )->values() );
                $subject->exam_count = $subject->quizzes_count + $subject->children->sum( 'quizzes_count' );
            } )
            ->filter( fn( self $subject ) => $subject->exam_count > 0 )
            ->values();
    }

    /**
     * Groups and stand-alone subjects (categories without a parent).
     */
    public function scopeTopLevel( Builder $query ): void {
        $query->whereNull( 'parent_id' );
    }

    /**
     * Categories shown to students.
     */
    public function scopeActive( Builder $query ): void {
        $query->where( 'status', true );
    }
}
