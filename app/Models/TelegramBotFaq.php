<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramBotFaq extends Model
{
    protected $table = 'telegram_bot_faq';

    protected $fillable = ['question', 'answer', 'sort_order'];
}
