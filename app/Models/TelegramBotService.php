<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramBotService extends Model
{
    protected $table = 'telegram_bot_services';

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'result',
        'price_or_terms',
        'sort_order',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(TelegramBotServiceCategory::class, 'category_id');
    }
}
