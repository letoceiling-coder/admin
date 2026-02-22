<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramBotSetting extends Model
{
    protected $table = 'telegram_bot_settings';

    protected $fillable = [
        'welcome_text',
        'start_text',
        'home_offer_text',
        'home_banner_file_id',
        'site_url',
        'presentation_file_id',
        'presentation_url',
        'manager_username',
        'notify_chat_id',
        'feature_flags',
        'utm_template',
    ];

    protected $casts = [
        'feature_flags' => 'array',
    ];

    public static function getSingleton(): self
    {
        $row = self::first();
        if ($row) {
            return $row;
        }
        return self::create([]);
    }
}
