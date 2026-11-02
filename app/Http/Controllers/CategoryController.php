<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkCategoryStatusRequest;
use App\Http\Requests\BulkDestroyCategoryRequest;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Models\Quiz;

class CategoryController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        // Each group first, followed by its sub-categories
        $categories = Category::query()
            ->with( 'parent:id,name' )
            ->withCount( ['quizzes', 'children', 'students'] )
            // Own quizzes plus those of the sub-categories, so a group like Programming shows its real total
            ->addSelect( ['total_quizzes_count' => Quiz::query()
                    ->selectRaw( 'COUNT(*)' )
                    ->where( fn( $query ) => $query
                            ->whereColumn( 'quizzes.category_id', 'categories.id' )
                            ->orWhereIn( 'quizzes.category_id', Category::query()
                                    ->from( 'categories as sub' )
                                    ->select( 'sub.id' )
                                    ->whereColumn( 'sub.parent_id', 'categories.id' ) ) ),
            ] )
            ->orderByRaw( 'COALESCE(parent_id, id), parent_id IS NOT NULL, name' )
            ->paginate( 15 );

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

    public function create() {
        return view( 'admin.category.create', ['groups' => $this->groupOptions()] );
    }

    public function store( CategoryRequest $request ) {
        $category = Category::create( $request->validated() );

        return redirect()->route( 'category.index' )->with( 'success', "“{$category->name}” created." );
    }

    public function edit( string $id ) {
        $category = Category::withCount( 'children' )->findOrFail( $id );

        return view( 'admin.category.edit', [
            'category' => $category,
            'groups'   => $this->groupOptions( except: $category->id ),
        ] );
    }

    public function update( CategoryRequest $request, string $id ) {
        $category = Category::findOrFail( $id );
        $category->update( $request->validated() );

        return redirect()->route( 'category.index' )->with( 'success', "“{$category->name}” updated." );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( string $id ) {
        $category = Category::withCount( 'quizzes' )->findOrFail( $id );

        if ( $category->children()->exists() ) {
            return back()->with( 'warning', "\"{$category->name}\" is a group with sub-categories. Move them to another group first." );
        }

        // Deleting would cascade to the quizzes and every student's results
        if ( $category->quizzes_count > 0 ) {
            return back()->with( 'warning', "\"{$category->name}\" still has {$category->quizzes_count} quiz(zes). Deactivate it instead, or move or delete its quizzes first." );
        }

        $category->delete();

        return back()->with( 'success', "\"{$category->name}\" deleted." );
    }

    /**
     * Top-level categories that can hold sub-categories.
     */
    private function groupOptions( ?int $except = null ) {
        return Category::topLevel()
            ->when( $except, fn( $query ) => $query->whereKeyNot( $except ) )
            ->orderBy( 'name' )
            ->get( ['id', 'name'] );
    }
}
