<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $with = ['role'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole(string $name): bool
    {
        return $this->role && $this->role->name === $name;
    }

    /** Доступ к /admin/* только у manager и administrator */
    public function hasAdminPanelAccess(): bool
    {
        return $this->role && $this->role->hasAdminPanelAccess();
    }

    public function hasAccessLevel(int $minLevel): bool
    {
        return $this->role && $this->role->hasAccessLevel($minLevel);
    }
}
