<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create( 'settings', function ( Blueprint $table ) {
            $table->id();
            $table->string( 'key' )->unique();
            $table->text( 'value' )->nullable();
            $table->timestamps();
        } );

        // Optional fixed exam length. NULL = questions x "seconds per question" setting.
        Schema::table( 'quizzes', function ( Blueprint $table ) {
            $table->unsignedSmallInteger( 'duration_minutes' )->nullable()->after( 'difficulty' );
        } );

        // Which try this answer belongs to. Existing answers are the first attempt.
        Schema::table( 'user_answers', function ( Blueprint $table ) {
            $table->unsignedSmallInteger( 'attempt' )->default( 1 )->after( 'quiz_id' );
            $table->index( ['user_id', 'quiz_id', 'attempt'] );
        } );
    }

    public function down(): void {
        Schema::table( 'user_answers', function ( Blueprint $table ) {
            $table->dropIndex( ['user_id', 'quiz_id', 'attempt'] );
            $table->dropColumn( 'attempt' );
        } );

        Schema::table( 'quizzes', function ( Blueprint $table ) {
            $table->dropColumn( 'duration_minutes' );
        } );

        Schema::dropIfExists( 'settings' );
    }
};
