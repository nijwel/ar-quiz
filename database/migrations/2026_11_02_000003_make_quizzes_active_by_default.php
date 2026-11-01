<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Quiz status now controls student visibility. Every quiz was created with the old
     * default (false) while the flag was unused, so switch them all on to keep them visible.
     */
    public function up(): void {
        Schema::table( 'quizzes', function ( Blueprint $table ) {
            $table->boolean( 'status' )->default( true )->change();
        } );

        DB::table( 'quizzes' )->update( ['status' => true] );
    }

    public function down(): void {
        Schema::table( 'quizzes', function ( Blueprint $table ) {
            $table->boolean( 'status' )->default( false )->change();
        } );
    }
};
