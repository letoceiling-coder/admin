<?php

namespace App\Http\Controllers\Api\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramEvent;
use App\Http\Requests\Telegram\StoreEventRequest;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    public function store(StoreEventRequest $request): JsonResponse
    {
        $event = TelegramEvent::create([
            'telegram_user_id' => $request->input('tg_user_id'),
            'event_type' => $request->input('event_name'),
            'payload' => $request->input('payload_json'),
        ]);
        return response()->json(['data' => ['id' => $event->id]], 201);
    }
}
