<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Password extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','website', 'encrypted_password','username','iv'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
