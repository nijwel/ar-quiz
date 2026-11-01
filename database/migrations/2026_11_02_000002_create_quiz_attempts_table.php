<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // One row per exam attempt: recorded when the student starts, closed when they submit
        Schema::create( 'quiz_attempts', function ( Blueprint $table ) {
            $table->id();
            $table->foreignId( 'user_id' )->constrained()->cascadeOnDelete();
            $table->foreignId( 'quiz_id' )->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger( 'attempt' );
            $table->timestamp( 'started_at' );
            $table->timestamp( 'ends_at' )->nullable();
            $table->timestamp( 'submitted_at' )->nullable();
            $table->string( 'submit_reason', 20 )->nullable();
            $table->unsignedSmallInteger( 'answered_count' )->default( 0 );
            $table->unsignedSmallInteger( 'correct_count' )->default( 0 );
            $table->timestamps();

            $table->unique( ['user_id', 'quiz_id', 'attempt'] );
        } );

        // Existing results become submitted attempts
        DB::statement( "
            INSERT INTO quiz_attempts
                (user_id, quiz_id, attempt, started_at, submitted_at, submit_reason, answered_count, correct_count, created_at, updated_at)
            SELECT user_id, quiz_id, attempt, MIN(created_at), MAX(created_at), 'manual',
                COUNT(*), SUM(CASE WHEN status = 'correct' THEN 1 ELSE 0 END), MIN(created_at), MAX(created_at)
            FROM user_answers
            GROUP BY user_id, quiz_id, attempt
        " );
    }

    public function down(): void {
        Schema::dropIfExists( 'quiz_attempts' );
    }
};
