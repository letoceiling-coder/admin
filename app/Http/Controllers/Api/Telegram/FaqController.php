<?php

namespace App\Http\Controllers\Api\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramBotFaq;
use Illuminate\Http\JsonResponse;

class FaqController extends Controller
{
    /**
     * Список FAQ по sort_order.
     */
    public function index(): JsonResponse
    {
        $items = TelegramBotFaq::query()->orderBy('sort_order')->orderBy('id')->get();
        return response()->json(['data' => $items]);
    }
}
