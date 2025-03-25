<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a test user directly
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'master_password' => Hash::make('password123'), // For a password manager, this might be the master_password field
                'email_verified_at' => now()
            ]
        );
        
        $this->command->info('Test user created or already exists.');
        
        // Call password seeder to create passwords for users
        $this->call(PasswordSeeder::class);
        
        // Generate a token for the test user
        $token = $testUser->createToken('test-token', ['*']);
        
        // Output the token value
        $this->command->info('Test user token: ' . $token->plainTextToken);
        
        // Store the token in a file (development only)
        if (app()->environment(['local', 'development', 'testing'])) {
            file_put_contents(
                storage_path('test_user_token.txt'),
                $token->plainTextToken
            );
            $this->command->info('Token saved to: ' . storage_path('test_user_token.txt'));
        }
    }
}
