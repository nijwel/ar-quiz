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

    route::prefix( 'quizzes' )->group( function () {
        Route::get( '/', [UserAnswerController::class, 'index'] )->name( 'user.quiz.index' );
        Route::get( '/{quiz}', [UserAnswerController::class, 'show'] )->name( 'user.quiz.show' );
        Route::post( '/submit', [UserAnswerController::class, 'store'] )->name( 'user.quiz.submit' );
    } );
} );

/*------------------------------------------
All Admin Routes List
--------------------------------------------*/
Route::prefix( 'admin' )->middleware( ['auth', 'user-access:admin'] )->group( function () {

    Route::get( '/home', [HomeController::class, 'adminHome'] )->name( 'admin.home' );

    route::prefix( 'quiz' )->group( function () {
        Route::get( '/', [QuizController::class, 'index'] )->name( 'quiz.index' );
        Route::get( '/create', [QuizController::class, 'create'] )->name( 'quiz.create' );
        Route::post( '/store', [QuizController::class, 'store'] )->name( 'quiz.store' );
        Route::get( '/edit/{id}', [QuizController::class, 'edit'] )->name( 'quiz.edit' );
        Route::put( '/update/{id}', [QuizController::class, 'update'] )->name( 'quiz.update' );
        Route::delete( '/destroy/{id}', [QuizController::class, 'destroy'] )->name( 'quiz.destroy' );
    } );

    Route::prefix( 'participants' )->group( function () {
        Route::get( '/', [ParticipantController::class, 'index'] )->name( 'participant.index' );
        Route::get( '/view/{id}', [ParticipantController::class, 'show'] )->name( 'participant.view' );
    } );

} );
