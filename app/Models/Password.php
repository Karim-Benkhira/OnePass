<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Password extends Model
{
    use HasFactory;

    protected $fillable = [
        'encrypted_password',
        'website',
        'username',
        'iv'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}