<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_name',
        'device_type',
        'ip_address',
        'last_login',
        'is_verified',
        'user_agent_data'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
