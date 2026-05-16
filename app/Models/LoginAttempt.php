<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    protected $fillable = ['email', 'ip_address', 'user_agent', 'success'];

    protected $casts = [
        'success' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    public function scopeRecent($query, $minutes = 15)
    {
        return $query->where('created_at', '>=', now()->subMinutes($minutes));
    }

    public static function recordAttempt($email, $success, $ipAddress = null, $userAgent = null)
    {
        return static::create([
            'email' => $email,
            'ip_address' => $ipAddress ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->userAgent(),
            'success' => $success,
        ]);
    }
}
