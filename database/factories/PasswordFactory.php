<?php

namespace Database\Factories;

use App\Models\Password;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PasswordFactory extends Factory
{
    protected $model = Password::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sites = [
            'Amazon', 'Facebook', 'Twitter', 'Instagram', 'LinkedIn', 
            'GitHub', 'Netflix', 'Google', 'Microsoft', 'Apple',
            'Dropbox', 'Spotify', 'Reddit', 'PayPal', 'Bank Account'
        ];
        
        // Generate a strong random password
        $password = $this->generateStrongPassword();
        
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->randomElement($sites) . ' Account',
            'encrypted_password' => encrypt($password), // Encrypt the generated password
        ];
    }
    
    /**
     * Create passwords for a specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
    
    /**
     * Generate a cryptographically secure strong password
     */
    private function generateStrongPassword($length = 16): string
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $special = '!@#$%^&*()-_=+';
        
        $all = $lowercase . $uppercase . $numbers . $special;
        $password = '';
        
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];
        
        for ($i = 4; $i < $length; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }
        
        return str_shuffle($password);
    }
}
