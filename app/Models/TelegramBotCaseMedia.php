<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramBotCaseMedia extends Model
{
    protected $table = 'telegram_bot_case_media';

    protected $fillable = ['case_id', 'file_id', 'type', 'sort_order'];

    public function case(): BelongsTo
    {
        return $this->belongsTo(TelegramBotCase::class, 'case_id');
    }
}
