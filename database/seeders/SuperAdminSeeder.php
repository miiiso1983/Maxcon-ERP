<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@maxcon.com',
            'password' => Hash::make('password'),
            'is_super_admin' => true,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->command->info('Super Admin user created successfully!');
        $this->command->info('Email: admin@maxcon.com');
        $this->command->info('Password: password');
    }
}
