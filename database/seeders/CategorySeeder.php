<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder {
    public function run(): void {
        $categories = [
            'General Knowledge',
            'PHP',
            'JAVASCRIPT',
            'Jquery',
            'CSS',
            'HTML',
            'Laravel',
            'Bootstrap',
            'Tailwind',
            'React',
            'Vue',
        ];

        foreach ( $categories as $category ) {
            DB::table( 'categories' )->insert( [
                'name'       => $category,
                'slug'       => Str::slug( $category ),
                'status'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ] );
        }
    }
}
