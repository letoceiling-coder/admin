<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramLead extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';

    protected $table = 'telegram_leads';

    protected $fillable = [
        'telegram_user_id',
        'username',
        'name',
        'contact',
        'message',
        'budget_range',
        'deadline',
        'service_id',
        'case_id',
        'status',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(TelegramBotService::class, 'service_id');
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(TelegramBotCase::class, 'case_id');
    }
}
