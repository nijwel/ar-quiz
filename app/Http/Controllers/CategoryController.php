<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkCategoryStatusRequest;
use App\Http\Requests\BulkDestroyCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $categories = Category::withCount( 'quizzes' )->paginate( 15 );
        return view( 'admin.category.index', compact( 'categories' ) );
    }

    /**
     * Switch a category between active and inactive. Students only see active
     * categories and the quizzes in them.
     */
    public function toggleStatus( Category $category ) {
        $category->update( ['status' => !$category->status] );

        return back()->with( 'success', "\"{$category->name}\" is now " . ( $category->status ? 'active' : 'inactive' ) . '.' );
    }

    /**
     * Activate or deactivate all selected categories with one query.
     */
    public function bulkStatus( BulkCategoryStatusRequest $request ) {
        $status  = $request->boolean( 'status' );
        $updated = Category::whereIn( 'id', $request->input( 'ids' ) )->update( ['status' => $status] );

        return back()->with( 'success', "$updated categor" . ( $updated === 1 ? 'y' : 'ies' ) . ' marked as ' . ( $status ? 'active' : 'inactive' ) . '.' );
    }

    /**
     * Delete the selected categories. Categories that still have quizzes are skipped:
     * deleting them would cascade to their quizzes and every student's results.
     */
    public function bulkDestroy( BulkDestroyCategoryRequest $request ) {
        [$inUse, $deletable] = Category::whereIn( 'id', $request->input( 'ids' ) )
            ->withCount( 'quizzes' )
            ->get( ['id', 'name'] )
            ->partition( fn( Category $category ) => $category->quizzes_count > 0 );

        if ( $deletable->isNotEmpty() ) {
            Category::whereKey( $deletable->modelKeys() )->delete();
        }

        $redirect = back();

        if ( $deletable->isNotEmpty() ) {
            $redirect->with( 'success', $deletable->count() . ' categor' . ( $deletable->count() === 1 ? 'y' : 'ies' ) . ' deleted.' );
        }

        if ( $inUse->isNotEmpty() ) {
            $redirect->with( 'warning', 'Not deleted because they still have quizzes (deactivate them instead): '
                . $inUse->map( fn( Category $category ) => "{$category->name} ({$category->quizzes_count})" )->implode( ', ' ) );
        }

        return $redirect;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store( Request $request ) {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show( string $id ) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit( string $id ) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update( Request $request, string $id ) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( string $id ) {
        $category = Category::withCount( 'quizzes' )->findOrFail( $id );

        // Deleting would cascade to the quizzes and every student's results
        if ( $category->quizzes_count > 0 ) {
            return back()->with( 'warning', "\"{$category->name}\" still has {$category->quizzes_count} quiz(zes). Deactivate it instead, or move or delete its quizzes first." );
        }

        $category->delete();

        return back()->with( 'success', "\"{$category->name}\" deleted." );
    }
}
