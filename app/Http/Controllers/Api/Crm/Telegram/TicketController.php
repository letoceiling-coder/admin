<?php

namespace App\Http\Controllers\Api\Crm\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramTicket;
use App\Http\Requests\Crm\Telegram\UpdateTicketRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TelegramTicket::query()->orderBy('id', 'desc');
        if ($request->filled('status') && in_array($request->input('status'), ['new', 'in_progress', 'done'], true)) {
            $query->where('status', $request->input('status'));
        }
        $items = $query->get();
        return response()->json(['data' => $items]);
    }

    public function update(UpdateTicketRequest $request, int $id): JsonResponse
    {
        $item = TelegramTicket::findOrFail($id);
        $item->update($request->validated());
        return response()->json(['data' => $item]);
    }
}
