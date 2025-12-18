<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed subscription plans first
        $this->call([
            SubscriptionPlanSeeder::class,
            TaskSeeder::class,
        ]);

        // Create admin user
        $admin = User::create([
            'name' => 'SKYpesa Admin',
            'email' => 'admin@skypesa.co.tz',
            'password' => Hash::make('password123'),
            'phone' => '0700000000',
            'role' => 'admin',
            'is_active' => true,
            'is_verified' => true,
        ]);

        // Create test user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@skypesa.co.tz',
            'password' => Hash::make('password123'),
            'phone' => '0711111111',
            'role' => 'user',
            'is_active' => true,
            'is_verified' => true,
        ]);
    }
}
