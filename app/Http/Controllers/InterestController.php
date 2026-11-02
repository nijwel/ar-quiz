<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateInterestsRequest;
use App\Models\Category;
use App\Models\QuizAttempt;
use App\Models\Setting;

/**
 * Lets students choose the subjects they want to take exams in.
 */
class InterestController extends Controller {
    /**
     * The subject picker. Also the first page new students see.
     */
    public function edit() {
        $subjects = Category::subjectsWithExams();
        $selected = auth()->user()->interests()->pluck( 'categories.id' )->all();
        $max      = Setting::maxInterests();

        return view( 'user.interests', [
            'groups'     => $subjects->filter( fn( Category $subject ) => $subject->children->isNotEmpty() )->values(),
            'singles'    => $subjects->filter( fn( Category $subject ) => $subject->children->isEmpty() )->values(),
            // First visit: suggest the subjects the student already took exams in
            'selected'   => $selected ?: $this->subjectsFromHistory( $max ),
            'max'        => $max,
            'firstVisit' => $selected === [],
        ] );
    }

    public function update( UpdateInterestsRequest $request ) {
        $request->user()->interests()->sync( $request->categoryIds() );

        return redirect()->route( 'user.quiz.index' )->with( 'success', 'আপনার বিষয়গুলো সেভ হয়েছে। এখন এই বিষয়গুলোর পরীক্ষা দিতে পারবেন।' );
    }

    /**
     * One-click add from the locked subjects on the exam list.
     */
    public function add( Category $category ) {
        $user = auth()->user();

        abort_unless( $category->status, 404 );

        if ( $user->canTakeExamsIn( $category->id ) ) {
            return redirect()->route( 'user.quiz.index', ['category' => $category->slug] );
        }

        // Adding a whole group replaces its sub-categories the student had chosen one by one
        $chosen   = $user->interests()->pluck( 'categories.id' );
        $replaced = $chosen->intersect( $category->children()->pluck( 'id' ) );

        if ( $chosen->count() - $replaced->count() + 1 > Setting::maxInterests() ) {
            return back()->with( 'warning', 'আপনি সর্বোচ্চ ' . Setting::maxInterests() . 'টি বিষয় বেছে নিয়েছেন। নতুন বিষয় যোগ করতে প্রোফাইল থেকে একটি বাদ দিন।' );
        }

        $user->interests()->detach( $replaced );
        $user->interests()->attach( $category->id );

        return redirect()->route( 'user.quiz.index', ['category' => $category->slug] )
            ->with( 'success', "“{$category->name}” আপনার বিষয়ে যোগ হয়েছে।" );
    }

    /**
     * Categories of the quizzes the student has already attempted (for existing students).
     */
    private function subjectsFromHistory( int $limit ): array {
        return QuizAttempt::query()
            ->where( 'user_id', auth()->id() )
            ->join( 'quizzes', 'quizzes.id', '=', 'quiz_attempts.quiz_id' )
            ->distinct()
            ->limit( $limit )
            ->pluck( 'quizzes.category_id' )
            ->all();
    }
}
