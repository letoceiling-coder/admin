<?php

namespace App\Http\Controllers\Api\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramLead;
use App\Http\Requests\Telegram\StoreLeadRequest;
use Illuminate\Http\JsonResponse;

class LeadController extends Controller
{
    public function store(StoreLeadRequest $request): JsonResponse
    {
        $contact = $request->input('phone') ?: $request->input('username') ?: 'n/a';
        $lead = TelegramLead::create([
            'telegram_user_id' => $request->input('tg_user_id'),
            'username' => $request->input('username'),
            'name' => $request->input('full_name'),
            'contact' => $contact,
            'message' => $request->input('message'),
            'service_id' => $request->input('source_service_id'),
            'case_id' => $request->input('source_case_id'),
            'status' => TelegramLead::STATUS_NEW,
        ]);
        return response()->json(['data' => ['id' => $lead->id]], 201);
    }
}
