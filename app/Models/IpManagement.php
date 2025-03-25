<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpManagement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'status',
        'description',
        'last_access'
    ];

    protected $casts = [
        'last_access' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isWhitelisted()
    {
        return $this->status === 'whitelist';
    }

    public function isBlacklisted()
    {
        return $this->status === 'blacklist';
    }
} 