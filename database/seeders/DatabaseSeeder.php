<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run role seeder first
        $this->call(RoleSeeder::class);

        // Create requested Admin user (Admin@123 / ee25062506)
        $admin = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'Admin@123', // Using this as the username/identifier
            'password' => 'ee25062506', // As requested
        ]);
        $admin->assignRole('admin');

        // Create test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@aws-system.local',
        ]);
        $user->assignRole('user');
    }
}
