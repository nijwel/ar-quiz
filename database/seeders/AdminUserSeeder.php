<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        User::create( [
            'name'     => 'Admin',
            'email'    => 'admin@gmail.com',
            'password' => Hash::make( '12345678' ),
            'type'     => 'admin',
        ] );

        User::create( [
            'name'     => 'User',
            'email'    => 'user@gmail.com',
            'password' => Hash::make( '12345678' ),
            'type'     => 'user',
        ] );
    }
}