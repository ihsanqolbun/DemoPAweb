<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'username', 'password', 'role', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'last_login_at' => 'datetime',
    ];

    // Kasih tau Laravel pakai 'username' bukan 'email'
    public function getAuthIdentifierName(): string
    {
        return 'username';
    }

    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
    public function isAdmin(): bool      { return $this->role === 'admin'; }
    public function isPimpinan(): bool   { return $this->role === 'pimpinan'; }
    public function isHakim(): bool      { return $this->role === 'hakim'; }

    public function canManageUsers(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canExport(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'pimpinan']);
    }

    public function canEditData(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }
}