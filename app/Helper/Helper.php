<?php

use App\Models\UserAnswer;

if ( !function_exists( 'quizResult' ) ) {
    function quizResult( $quizId, $userId = null ) {
        $totalMarks = UserAnswer::where( 'quiz_id', $quizId )->where( 'user_id', $userId )->get()->where( 'status', 'correct' )->count();
        return $totalMarks ?: 0;
    }
}

if ( !function_exists( 'totalAnswers' ) ) {
    function totalAnswers( $quizId, $userId = null ) {
        return UserAnswer::where( 'quiz_id', $quizId )->where( 'user_id', $userId )->count();
    }
}

if ( !function_exists( 'rightAnswer' ) ) {
    function rightAnswer( $quizId, $userId = null ) {
        $totalMarks = UserAnswer::where( 'quiz_id', $quizId )->where( 'user_id', $userId )->get()->where( 'status', 'correct' )->count();
        return $totalMarks ?: 0;
    }
}

if ( !function_exists( 'wrongAnswer' ) ) {
    function wrongAnswer( $quizId, $userId = null ) {
        $totalMarks = UserAnswer::where( 'quiz_id', $quizId )->where( 'user_id', $userId )->get()->where( 'status', 'incorrect' )->count();
        return $totalMarks ?: 0;
    }
}

if ( !function_exists( 'userQuizParticipants' ) ) {
    function userQuizParticipants( $userId ) {
        return UserAnswer::where( 'user_id', $userId )
            ->distinct( 'quiz_id' )
            ->count( 'quiz_id' );
    }
}

if ( !function_exists( 'totalUserMarks' ) ) {
    function totalUserMarks( $userId ) {
        return UserAnswer::where( 'user_id', $userId )->count();
    }
}