<?php

namespace App\Http\Controllers\Api\Crm\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramBotReview;
use App\Http\Requests\Crm\Telegram\UpdateReviewRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TelegramBotReview::query()->orderBy('id', 'desc');
        if ($request->filled('status') && in_array($request->input('status'), ['pending', 'approved'], true)) {
            $query->where('status', $request->input('status'));
        }
        $items = $query->get();
        return response()->json(['data' => $items]);
    }

    public function update(UpdateReviewRequest $request, int $id): JsonResponse
    {
        $item = TelegramBotReview::findOrFail($id);
        $item->update($request->validated());
        return response()->json(['data' => $item]);
    }

    public function approve(int $id): JsonResponse
    {
        $item = TelegramBotReview::findOrFail($id);
        $item->update(['status' => TelegramBotReview::STATUS_APPROVED]);
        return response()->json(['data' => $item]);
    }

    /** Reject = удаление отзыва из модерации (удаляем запись). */
    public function reject(int $id): JsonResponse
    {
        $item = TelegramBotReview::findOrFail($id);
        $item->delete();
        return response()->json(['data' => ['id' => $id]]);
    }
}
