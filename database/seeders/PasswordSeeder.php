<?php

namespace Database\Seeders;

use App\Models\Password;
use App\Models\User;
use Illuminate\Database\Seeder;

class PasswordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 10 random passwords
        Password::factory()->count(10)->create();
        
        // Optionally, create passwords for specific users if they exist
        $testUser = User::where('email', 'test@example.com')->first();
        
        if ($testUser) {
            Password::factory()
                ->count(5)
                ->forUser($testUser)
                ->create();
        }
    }
}
