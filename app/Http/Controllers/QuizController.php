<?php

namespace App\Http\Controllers;

use App\Enums\Difficulty;
use App\Http\Requests\BulkDestroyQuizRequest;
use App\Http\Requests\BulkQuizStatusRequest;
use App\Http\Requests\ImportQuizRequest;
use App\Http\Requests\QuizRequest;
use App\Models\Category;
use App\Models\Quiz;
use App\Models\Setting;
use App\Services\QuizTransfer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuizController extends Controller {
    /** Page sizes the quiz list offers; the first one is the default. */
    private const PER_PAGE_OPTIONS = [10, 25, 50, 100, 150, 200, 300, 500];

    public function __construct( private QuizTransfer $transfer ) {}

    /**
     * Display a listing of the resource.
     */
    public function index( Request $request ) {
        $perPage = in_array( $request->integer( 'per_page' ), self::PER_PAGE_OPTIONS, true )
            ? $request->integer( 'per_page' )
            : self::PER_PAGE_OPTIONS[0];

        $quizzes = $this->filteredQuery( $request )
            ->with( 'category:id,name' )
            ->withCount( 'questions' )
            ->latest()
            ->latest( 'id' ) // Tie-breaker: many quizzes share a created_at, which made pages overlap
            ->paginate( $perPage );

        return view( 'admin.quiz.index', [
            'quizzes'        => $quizzes,
            'categories'     => $this->categories(),
            'perPage'        => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
        ] );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        return view( 'admin.quiz.create', [
            'categories'         => $this->categoryOptions(),
            'secondsPerQuestion' => Setting::secondsPerQuestion(),
        ] );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store( QuizRequest $request ) {
        DB::transaction( function () use ( $request ) {
            $quiz = Quiz::create( $request->quizData() + ['slug' => Str::slug( $request->title )] );

            $this->transfer->saveQuestions( $quiz, $request->questionsData() );
        } );

        return redirect()->route( 'quiz.index' )->with( 'success', 'Quiz created successfully.' );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit( string $id ) {
        $quiz = Quiz::with( [
            'questions:id,quiz_id,question',
            'questions.answers:id,question_id,answer,is_correct',
        ] )->findOrFail( $id );

        return view( 'admin.quiz.edit', [
            'quiz'               => $quiz,
            'categories'         => $this->categoryOptions(),
            'secondsPerQuestion' => Setting::secondsPerQuestion(),
        ] );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update( QuizRequest $request, string $id ) {
        $quiz = Quiz::findOrFail( $id );

        DB::transaction( function () use ( $quiz, $request ) {
            $quiz->update( $request->quizData() + ['slug' => Str::slug( $request->title )] );

            // Questions are replaced as a whole; answers are removed by the FK cascade
            $quiz->questions()->delete();
            $this->transfer->saveQuestions( $quiz, $request->questionsData() );
        } );

        return redirect()->route( 'quiz.index' )->with( 'success', 'Quiz updated successfully.' );
    }

    /**
     * Show or hide the quiz in the student panel.
     */
    public function toggleStatus( Quiz $quiz ) {
        $quiz->update( ['status' => !$quiz->status] );

        return back()->with( 'success', "\"{$quiz->title}\" is now " . ( $quiz->status ? 'shown to' : 'hidden from' ) . ' students.' );
    }

    /**
     * Show or hide several selected quizzes at once.
     */
    public function bulkStatus( BulkQuizStatusRequest $request ) {
        $show    = $request->boolean( 'status' );
        $updated = Quiz::whereIn( 'id', $request->validated( 'ids' ) )->update( ['status' => $show] );

        return back()->with( 'success', "$updated quiz(zes) " . ( $show ? 'shown to' : 'hidden from' ) . ' students.' );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( string $id ) {
        Quiz::findOrFail( $id )->delete();

        return redirect()->route( 'quiz.index' )->with( 'success', 'Quiz deleted successfully.' );
    }

    /**
     * Delete several selected quizzes at once. Questions, answers and student
     * attempts are removed by the FK cascade.
     */
    public function bulkDestroy( BulkDestroyQuizRequest $request ) {
        $deleted = Quiz::whereIn( 'id', $request->validated( 'ids' ) )->delete();

        return back()->with( 'success', "$deleted quiz(zes) deleted successfully." );
    }

    /**
     * Download all quizzes (or only those matching the current filters) as one JSON file.
     */
    public function export( Request $request ) {
        $query    = $this->filteredQuery( $request )->orderBy( 'id' );
        $filename = 'quizzes-' . now()->format( 'Y-m-d-His' ) . '.json';

        return response()->streamDownload(
            fn() => $this->transfer->exportMany( $query, function ( string $chunk ) {
                echo $chunk;
            } ),
            $filename,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Download a single quiz as a JSON file.
     */
    public function exportOne( string $id ) {
        $quiz = Quiz::findOrFail( $id );

        return response()->streamDownload(
            fn() => print( $this->transfer->exportOne( $quiz ) ),
            $quiz->slug . '.json',
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Import one or many quizzes from an uploaded file or pasted JSON.
     */
    public function upload( ImportQuizRequest $request ) {
        $result = $this->transfer->import( $request->payload() );

        $imported = count( $result['imported'] );
        $skipped  = $result['skipped'];

        $redirect = redirect()->route( 'quiz.index' );

        if ( $imported > 0 ) {
            $redirect->with( 'success', $imported === 1
                ? "Quiz “{$result['imported'][0]}” imported successfully."
                : "$imported quizzes imported successfully." );
        }

        if ( $skipped ) {
            $redirect->with( 'warning', count( $skipped ) . ' quiz(zes) skipped because the title already exists: '
                . Str::limit( implode( ', ', $skipped ), 300 ) );
        }

        return $redirect;
    }

    /**
     * Quizzes filtered by the list page's category and difficulty query parameters.
     */
    private function filteredQuery( Request $request ): Builder {
        $difficulty = Difficulty::tryFrom( (string) $request->query( 'difficulty' ) );

        return Quiz::query()
            // A group (e.g. Programming) matches the quizzes of all its sub-categories
            ->when( $request->query( 'category' ), fn( $query, $categoryId ) => $query->whereIn( 'category_id',
                Category::whereKey( $categoryId )->orWhere( 'parent_id', $categoryId )->select( 'id' )
            ) )
            ->when( $difficulty, fn( $query ) => $query->where( 'difficulty', $difficulty ) );
    }

    /**
     * Every category, inactive ones included, so a quiz in an inactive category
     * keeps its category when edited.
     */
    private function categories() {
        return Category::orderByDesc( 'status' )->orderBy( 'name' )->get( ['id', 'name', 'status'] );
    }

    /**
     * Options for the quiz form: stand-alone categories and groups with their
     * sub-categories. A group itself is not selectable, only what is inside it.
     */
    private function categoryOptions() {
        return Category::topLevel()
            ->with( ['children' => fn( $query ) => $query->orderByDesc( 'status' )->orderBy( 'name' )->select( 'id', 'parent_id', 'name', 'status' )] )
            ->orderByDesc( 'status' )
            ->orderBy( 'name' )
            ->get( ['id', 'name', 'status'] );
    }
}
