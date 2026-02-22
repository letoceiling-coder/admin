<?php

namespace App\Http\Controllers\Api\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramBotSetting;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    /**
     * Одна запись настроек (первая по id — предсказуемо).
     */
    public function index(): JsonResponse
    {
        $settings = TelegramBotSetting::getSingleton();
        return response()->json([
            'data' => [
                'welcome_text' => $settings->welcome_text,
                'start_text' => $settings->start_text,
                'home_offer_text' => $settings->home_offer_text,
                'home_banner_file_id' => $settings->home_banner_file_id,
                'site_url' => $settings->site_url,
                'presentation_file_id' => $settings->presentation_file_id,
                'presentation_url' => $settings->presentation_url,
                'manager_username' => $settings->manager_username,
                'notify_chat_id' => $settings->notify_chat_id,
                'feature_flags' => $settings->feature_flags,
                'utm_template' => $settings->utm_template,
            ],
        ]);
    }
}
