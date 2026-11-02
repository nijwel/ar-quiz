<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /** Existing categories that become sub-categories of "Programming". */
    private const PROGRAMMING = ['php', 'javascript', 'jquery', 'css', 'html', 'laravel', 'bootstrap', 'tailwind', 'react', 'vue'];

    public function up(): void {
        // A category can belong to a group, e.g. Programming > PHP
        Schema::table( 'categories', function ( Blueprint $table ) {
            $table->foreignId( 'parent_id' )->nullable()->after( 'id' )
                ->constrained( 'categories' )->nullOnDelete();
        } );

        // The subjects a student chose to take exams in
        Schema::create( 'category_user', function ( Blueprint $table ) {
            $table->foreignId( 'user_id' )->constrained()->cascadeOnDelete();
            $table->foreignId( 'category_id' )->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary( ['user_id', 'category_id'] );
        } );

        $this->groupProgrammingCategories();
    }

    public function down(): void {
        Schema::dropIfExists( 'category_user' );

        Schema::table( 'categories', function ( Blueprint $table ) {
            $table->dropConstrainedForeignId( 'parent_id' );
        } );

        DB::table( 'categories' )->where( 'slug', 'programming' )->delete();
    }

    private function groupProgrammingCategories(): void {
        if ( !DB::table( 'categories' )->whereIn( 'slug', self::PROGRAMMING )->exists() ) {
            return;
        }

        $programmingId = DB::table( 'categories' )->where( 'slug', 'programming' )->value( 'id' )
            ?? DB::table( 'categories' )->insertGetId( [
                'name'       => 'Programming',
                'slug'       => 'programming',
                'status'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ] );

        DB::table( 'categories' )->whereIn( 'slug', self::PROGRAMMING )->update( ['parent_id' => $programmingId] );
    }
};
