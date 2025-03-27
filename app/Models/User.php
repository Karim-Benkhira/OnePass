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


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'master_password' => 'hashed',
        ];
    }


    public function devices()
    {
    return $this->hasMany(Device::class);
    }
    public function passwords()
    {
        return $this->hasMany(Password::class);
    }

    public function ipManagements()
    {
        return $this->hasMany(IpManagement::class);
    }

    public function whitelistedIps()
    {
        return $this->ipManagements()->where('status', 'whitelist');
    }

    public function blacklistedIps()
    {
        return $this->ipManagements()->where('status', 'blacklist');

    }
}
