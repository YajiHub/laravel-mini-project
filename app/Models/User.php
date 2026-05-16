<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role_id', 'phone', 'avatar', 'is_active', 'last_login_at', 'mfa_enabled', 'mfa_secret'])]
#[Hidden(['password', 'remember_token', 'mfa_secret'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'mfa_enabled' => 'boolean',
        ];
    }

    // Relationships
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function posTransactions()
    {
        return $this->hasMany(PosTransaction::class);
    }

    public function loginAttempts()
    {
        return $this->hasMany(LoginAttempt::class, 'email', 'email');
    }

    // Helper Methods
    public function hasPermission($permissionName)
    {
        if (!$this->role) {
            return false;
        }
        return $this->role->permissions()->where('name', $permissionName)->exists();
    }

    public function hasAnyPermission(array $permissions)
    {
        if (!$this->role) {
            return false;
        }
        return $this->role->permissions()->whereIn('name', $permissions)->exists();
    }

    public function isAdmin()
    {
        return $this->role && $this->role->name === 'admin';
    }

    public function isStaff()
    {
        return $this->role && in_array($this->role->name, ['admin', 'staff']);
    }
}
