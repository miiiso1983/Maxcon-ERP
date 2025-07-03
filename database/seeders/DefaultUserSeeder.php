<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DefaultUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        User::updateOrCreate(
            ['email' => 'admin@maxcon-demo.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@maxcon-demo.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create additional demo users
        User::updateOrCreate(
            ['email' => 'manager@maxcon-demo.com'],
            [
                'name' => 'Manager User',
                'email' => 'manager@maxcon-demo.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@maxcon-demo.com'],
            [
                'name' => 'Regular User',
                'email' => 'user@maxcon-demo.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
