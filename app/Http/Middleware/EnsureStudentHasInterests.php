<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends students who have not chosen any subjects to the interest picker first.
 */
class EnsureStudentHasInterests {
    /** Routes a student can use before choosing subjects. */
    private const ALLOWED_ROUTES = ['user.interests.*', 'user.profile', 'user.update.*'];

    public function handle( Request $request, Closure $next ): Response {
        $user = $request->user();

        if ( $user && $user->type === 'user' && !$request->routeIs( ...self::ALLOWED_ROUTES ) && empty( $user->examCategoryIds() ) ) {
            return redirect()->route( 'user.interests.edit' );
        }

        return $next( $request );
    }
}
