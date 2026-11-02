<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InterestController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserAnswerController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get( '/', function () {
    return redirect()->route( 'login' );
} );

Auth::routes();

/*------------------------------------------
All Normal Users Routes List
--------------------------------------------*/
Route::prefix( 'user' )->middleware( ['auth', 'user-access:user', 'has-interests'] )->group( function () {

    Route::get( '/home', [HomeController::class, 'index'] )->name( 'home' );
    Route::get( '/profile', [HomeController::class, 'userProfile'] )->name( 'user.profile' );
    Route::put( 'update/profile', [HomeController::class, 'updateProfile'] )->name( 'user.update.profile' );
    Route::put( 'update/password', [HomeController::class, 'updatePassword'] )->name( 'user.update.password' );

    // Subjects the student takes exams in
    Route::get( '/interests', [InterestController::class, 'edit'] )->name( 'user.interests.edit' );
    Route::put( '/interests', [InterestController::class, 'update'] )->name( 'user.interests.update' );
    Route::post( '/interests/{category}', [InterestController::class, 'add'] )->name( 'user.interests.add' );
    route::prefix( 'quizzes' )->group( function () {
        Route::get( '/', [UserAnswerController::class, 'index'] )->name( 'user.quiz.index' );
        Route::get( '/{quiz}', [UserAnswerController::class, 'show'] )->name( 'user.quiz.show' );
        Route::post( '/{quiz}/start', [UserAnswerController::class, 'start'] )->name( 'user.quiz.start' );
        Route::post( '/submit', [UserAnswerController::class, 'store'] )->name( 'user.quiz.submit' );
        Route::get( '/result/{quiz}', [UserAnswerController::class, 'result'] )->name( 'user.quiz.result' );
    } );
    Route::get( '/my-quiz', [ParticipantController::class, 'myExams'] )->name( 'user.my.quiz' );
    Route::get( '/leader/board', [UserAnswerController::class, 'leaderboard'] )->name( 'user.quiz.leaderboard' );
} );

/*------------------------------------------
All Admin Routes List
--------------------------------------------*/
Route::prefix( 'admin' )->middleware( ['auth', 'user-access:admin'] )->group( function () {

    Route::get( '/home', [HomeController::class, 'adminHome'] )->name( 'admin.home' );
    Route::get( '/profile', [HomeController::class, 'adminProfile'] )->name( 'admin.profile' );
    Route::put( 'update/profile', [HomeController::class, 'updateProfile'] )->name( 'admin.profile.update' );
    Route::put( 'update/password', [HomeController::class, 'updatePassword'] )->name( 'admin.password.update' );

    Route::prefix( 'category' )->group( function () {
        Route::get( '/', [CategoryController::class, 'index'] )->name( 'category.index' );
        Route::get( '/create', [CategoryController::class, 'create'] )->name( 'category.create' );
        Route::post( '/store', [CategoryController::class, 'store'] )->name( 'category.store' );
        Route::get( '/edit/{id}', [CategoryController::class, 'edit'] )->name( 'category.edit' );
        Route::put( '/update/{id}', [CategoryController::class, 'update'] )->name( 'category.update' );
        Route::delete( '/destroy/{id}', [CategoryController::class, 'destroy'] )->name( 'category.destroy' );
        Route::patch( '/bulk-status', [CategoryController::class, 'bulkStatus'] )->name( 'category.bulk-status' );
        Route::delete( '/bulk-destroy', [CategoryController::class, 'bulkDestroy'] )->name( 'category.bulk-destroy' );
        Route::patch( '/{category}/status', [CategoryController::class, 'toggleStatus'] )->name( 'category.status' );
    } );

    route::prefix( 'quiz' )->group( function () {
        Route::get( '/', [QuizController::class, 'index'] )->name( 'quiz.index' );
        Route::get( '/create', [QuizController::class, 'create'] )->name( 'quiz.create' );
        Route::post( '/store', [QuizController::class, 'store'] )->name( 'quiz.store' );
        Route::get( '/edit/{id}', [QuizController::class, 'edit'] )->name( 'quiz.edit' );
        Route::put( '/update/{id}', [QuizController::class, 'update'] )->name( 'quiz.update' );
        Route::delete( '/destroy/{id}', [QuizController::class, 'destroy'] )->name( 'quiz.destroy' );
        // Bulk actions share one form of row checkboxes, so both are POST (buttons pick the route via formaction)
        Route::post( '/bulk/status', [QuizController::class, 'bulkStatus'] )->name( 'quiz.status.bulk' );
        Route::post( '/bulk/destroy', [QuizController::class, 'bulkDestroy'] )->name( 'quiz.destroy.bulk' );
        Route::patch( '/{quiz}/status', [QuizController::class, 'toggleStatus'] )->name( 'quiz.status' );
        Route::post( '/upload', [QuizController::class, 'upload'] )->name( 'quiz.upload' );
        Route::get( '/export', [QuizController::class, 'export'] )->name( 'quiz.export' );
        Route::get( '/export/{id}', [QuizController::class, 'exportOne'] )->name( 'quiz.export.single' );
    } );

    Route::get( '/settings', [SettingController::class, 'index'] )->name( 'admin.settings' );
    Route::put( '/settings', [SettingController::class, 'update'] )->name( 'admin.settings.update' );
    Route::put( '/settings/branding', [SettingController::class, 'updateBranding'] )->name( 'admin.settings.branding' );

    Route::prefix( 'participants' )->group( function () {
        Route::get( '/', [ParticipantController::class, 'index'] )->name( 'participant.index' );
        Route::get( '/view/{id}', [ParticipantController::class, 'show'] )->name( 'participant.view' );
    } );

} );