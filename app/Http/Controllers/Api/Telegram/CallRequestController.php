<?php

namespace App\Http\Controllers\Api\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramCallRequest;
use App\Http\Requests\Telegram\StoreCallRequestRequest;
use Illuminate\Http\JsonResponse;

class CallRequestController extends Controller
{
    public function store(StoreCallRequestRequest $request): JsonResponse
    {
        $callRequest = TelegramCallRequest::create([
            'telegram_user_id' => $request->input('tg_user_id'),
            'username' => $request->input('username'),
            'name' => $request->input('full_name'),
            'phone' => $request->input('phone'),
            'preferred_time' => $request->input('preferred_time'),
            'comment' => $request->input('comment'),
            'status' => TelegramCallRequest::STATUS_NEW,
        ]);
        return response()->json(['data' => ['id' => $callRequest->id]], 201);
    }
}
