<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TelegramBotServiceCategory extends Model
{
    protected $table = 'telegram_bot_service_categories';

    protected $fillable = ['name', 'sort_order'];

    public function services(): HasMany
    {
        return $this->hasMany(TelegramBotService::class, 'category_id');
    }
}
