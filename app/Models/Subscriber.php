<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscriber extends Model
{
    protected $fillable = [
        'domain',
        'login',
        'subscription_start',
        'subscription_end',
        'is_active',
        'plan_id',
        'api_token',
        'payment_data',
    ];

    protected $hidden = ['api_token'];

    protected $casts = [
        'subscription_start' => 'date',
        'subscription_end' => 'date',
        'is_active' => 'boolean',
        'payment_data' => 'array',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Проверить, истекла ли подписка
     */
    public function isExpired(): bool
    {
        if (!$this->subscription_end) {
            return false; // Если дата не установлена, считаем что не истекла
        }
        
        return $this->subscription_end->endOfDay()->isPast();
    }

    /**
     * Получить актуальную активность подписки (с учетом даты окончания)
     */
    public function getActualIsActiveAttribute(): bool
    {
        // Если в БД is_active = false, то подписка неактивна
        if (!$this->is_active) {
            return false;
        }
        
        // Если подписка истекла, она не может быть активной
        return !$this->isExpired();
    }
}
