<?php

namespace App\Http\Controllers\Api\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramBotReview;
use App\Http\Requests\Telegram\StoreReviewRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Только approved, пагинация.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 15), 50);
        $paginator = TelegramBotReview::approved()
            ->orderBy('id', 'desc')
            ->paginate($perPage);
        return response()->json(['data' => $paginator->items(), 'meta' => [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ]]);
    }

    public function store(StoreReviewRequest $request): JsonResponse
    {
        $review = TelegramBotReview::create([
            'telegram_user_id' => $request->input('tg_user_id'),
            'author_name' => $request->input('full_name'),
            'company' => $request->input('company'),
            'rating' => $request->input('rating'),
            'text' => $request->input('text'),
            'status' => TelegramBotReview::STATUS_PENDING,
        ]);
        return response()->json(['data' => ['id' => $review->id]], 201);
    }
}
