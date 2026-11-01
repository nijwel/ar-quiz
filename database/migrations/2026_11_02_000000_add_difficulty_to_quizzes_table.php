<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Existing quizzes become "standard"
        Schema::table( 'quizzes', function ( Blueprint $table ) {
            $table->string( 'difficulty', 20 )->default( 'standard' )->after( 'description' )->index();
        } );
    }

    public function down(): void {
        Schema::table( 'quizzes', function ( Blueprint $table ) {
            $table->dropIndex( ['difficulty'] );
            $table->dropColumn( 'difficulty' );
        } );
    }
};
