<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{


     use HasApiTokens, HasFactory;

     protected $fillable = ['name','email', 'master_password'];
 
     protected $hidden = ['master_password','remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'master_password' => 'hashed',
        ];
    }
    public function passwords()
    {
        return $this->hasMany(Password::class);
    }
}
