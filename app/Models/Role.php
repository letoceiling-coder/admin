<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['name', 'level'];

    public const USER = 'user';
    public const MANAGER = 'manager';
    public const ADMINISTRATOR = 'administrator';
    public const DEVELOPER = 'developer';

    /** Уровни доступа по возрастанию: user(1) < manager(2) < administrator(3) < developer(4) */
    public const LEVEL_USER = 1;
    public const LEVEL_MANAGER = 2;
    public const LEVEL_ADMINISTRATOR = 3;
    public const LEVEL_DEVELOPER = 4;

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function hasAccessLevel(int $minLevel): bool
    {
        return $this->level >= $minLevel;
    }

    /** Доступ к /admin/* только у manager и administrator */
    public function hasAdminPanelAccess(): bool
    {
        return in_array($this->name, [self::MANAGER, self::ADMINISTRATOR], true);
    }
}
