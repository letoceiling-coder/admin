<?php

namespace App\Http\Controllers\Api\Crm\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramBotSetting;
use App\Http\Requests\Crm\Telegram\UpdateSettingsRequest;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = TelegramBotSetting::getSingleton();
        return response()->json(['data' => $settings]);
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $settings = TelegramBotSetting::getSingleton();
        $settings->update($request->validated());
        return response()->json(['data' => $settings]);
    }
}
