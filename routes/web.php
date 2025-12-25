<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\QuizController;
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
Route::prefix( 'user' )->middleware( ['auth', 'user-access:user'] )->group( function () {

    Route::get( '/home', [HomeController::class, 'index'] )->name( 'home' );
    Route::get( '/profile', [HomeController::class, 'userProfile'] )->name( 'user.profile' );
    Route::put( 'update/profile', [HomeController::class, 'updateProfile'] )->name( 'user.update.profile' );
    Route::put( 'update/password', [HomeController::class, 'updatePassword'] )->name( 'user.update.password' );
    route::prefix( 'quizzes' )->group( function () {
        Route::get( '/', [UserAnswerController::class, 'index'] )->name( 'user.quiz.index' );
        Route::get( '/{quiz}', [UserAnswerController::class, 'show'] )->name( 'user.quiz.show' );
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

    route::prefix( 'quiz' )->group( function () {
        Route::get( '/', [QuizController::class, 'index'] )->name( 'quiz.index' );
        Route::get( '/create', [QuizController::class, 'create'] )->name( 'quiz.create' );
        Route::post( '/store', [QuizController::class, 'store'] )->name( 'quiz.store' );
        Route::get( '/edit/{id}', [QuizController::class, 'edit'] )->name( 'quiz.edit' );
        Route::put( '/update/{id}', [QuizController::class, 'update'] )->name( 'quiz.update' );
        Route::delete( '/destroy/{id}', [QuizController::class, 'destroy'] )->name( 'quiz.destroy' );
        Route::post( '/upload', [QuizController::class, 'upload'] )->name( 'quiz.upload' );
    } );

    Route::prefix( 'participants' )->group( function () {
        Route::get( '/', [ParticipantController::class, 'index'] )->name( 'participant.index' );
        Route::get( '/view/{id}', [ParticipantController::class, 'show'] )->name( 'participant.view' );
    } );

} );
