<?php

namespace App\Http\Controllers\Api\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramTicket;
use App\Http\Requests\Telegram\StoreTicketRequest;
use Illuminate\Http\JsonResponse;

class TicketController extends Controller
{
    public function store(StoreTicketRequest $request): JsonResponse
    {
        $ticket = TelegramTicket::create([
            'telegram_user_id' => $request->input('tg_user_id'),
            'username' => $request->input('username'),
            'subject' => $request->input('subject'),
            'message' => $request->input('message'),
            'attachment_file_id' => $request->input('attachment_file_id'),
            'status' => TelegramTicket::STATUS_NEW,
        ]);
        return response()->json(['data' => ['id' => $ticket->id]], 201);
    }
}
