<?php

namespace App\Http\Controllers\Api\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramBotCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CaseController extends Controller
{
    /**
     * Список кейсов с фильтром по tag и пагинацией.
     */
    public function index(Request $request): JsonResponse
    {
        $query = TelegramBotCase::query()->orderBy('sort_order')->orderBy('id');
        if ($request->filled('tag')) {
            $tag = $request->input('tag');
            $query->whereJsonContains('tags', $tag);
        }
        $perPage = min((int) $request->input('per_page', 15), 50);
        $paginator = $query->paginate($perPage);
        return response()->json(['data' => $paginator->items(), 'meta' => [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ]]);
    }

    /**
     * Один кейс с медиа по sort_order.
     */
    public function show(int $id): JsonResponse
    {
        $case = TelegramBotCase::with(['media' => fn ($q) => $q->orderBy('sort_order')])->find($id);
        if (!$case) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json(['data' => $case]);
    }
}
