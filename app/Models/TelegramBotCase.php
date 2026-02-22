<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TelegramBotCase extends Model
{
    protected $table = 'telegram_bot_cases';

    protected $fillable = [
        'title',
        'task',
        'solution',
        'result',
        'tags',
        'sort_order',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function media(): HasMany
    {
        return $this->hasMany(TelegramBotCaseMedia::class, 'case_id')->orderBy('sort_order');
    }
}
