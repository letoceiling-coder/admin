<?php

namespace App\Http\Controllers\Api\Crm\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramBotCase;
use App\Models\TelegramBotService;
use App\Models\TelegramEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    /**
     * Top services and top cases by lead_created events (last N days).
     * GET /api/crm/telegram/analytics/top?days=30
     */
    public function top(Request $request): JsonResponse
    {
        $days = (int) $request->input('days', 30);
        $days = max(1, min(365, $days));
        $since = now()->subDays($days);

        $events = TelegramEvent::where('event_type', 'lead_created')
            ->where('created_at', '>=', $since)
            ->get();

        $byService = [];
        $byCase = [];
        foreach ($events as $e) {
            $payload = $e->payload ?? [];
            $sid = $payload['source_service_id'] ?? null;
            if ($sid !== null) {
                $byService[$sid] = ($byService[$sid] ?? 0) + 1;
            }
            $cid = $payload['source_case_id'] ?? null;
            if ($cid !== null) {
                $byCase[$cid] = ($byCase[$cid] ?? 0) + 1;
            }
        }

        arsort($byService);
        arsort($byCase);

        $topServiceIds = array_slice(array_keys($byService), 0, 5);
        $services = TelegramBotService::whereIn('id', $topServiceIds)->get()->keyBy('id');
        $top_services = [];
        foreach (array_slice($byService, 0, 5, true) as $id => $count) {
            $top_services[] = [
                'service_id' => $id,
                'name' => $services->get($id)?->name ?? '—',
                'count' => $count,
            ];
        }

        $topCaseIds = array_slice(array_keys($byCase), 0, 5);
        $cases = TelegramBotCase::whereIn('id', $topCaseIds)->get()->keyBy('id');
        $top_cases = [];
        foreach (array_slice($byCase, 0, 5, true) as $id => $count) {
            $top_cases[] = [
                'case_id' => $id,
                'title' => $cases->get($id)?->title ?? '—',
                'count' => $count,
            ];
        }

        return response()->json([
            'data' => [
                'top_services' => array_values($top_services),
                'top_cases' => array_values($top_cases),
            ],
        ]);
    }
}
