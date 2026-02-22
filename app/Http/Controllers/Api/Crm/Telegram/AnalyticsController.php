<?php

namespace App\Http\Controllers\Api\Crm\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramBotCase;
use App\Models\TelegramBotService;
use App\Models\TelegramEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnalyticsController extends Controller
{
    /**
     * Top services and top cases by lead_created events (last N days).
     * GET /api/crm/telegram/analytics/top?days=30
     */
    public function top(Request $request): JsonResponse
    {
        $top_services = [];
        $top_cases = [];

        try {
            $days = (int) $request->input('days', 30);
            $days = max(1, min(365, $days));
            $since = now()->subDays($days);

            $events = TelegramEvent::where('event_type', 'lead_created')
                ->where('created_at', '>=', $since)
                ->get();

            $byService = [];
            $byCase = [];
            foreach ($events as $e) {
                $payload = $e->payload;
                if (is_string($payload)) {
                    $decoded = json_decode($payload, true);
                    $payload = is_array($decoded) ? $decoded : [];
                }
                if (! is_array($payload)) {
                    $payload = [];
                }
                $sid = $payload['source_service_id'] ?? null;
                if ($sid !== null && $sid !== '') {
                    $sid = (int) $sid;
                    $byService[$sid] = ($byService[$sid] ?? 0) + 1;
                }
                $cid = $payload['source_case_id'] ?? null;
                if ($cid !== null && $cid !== '') {
                    $cid = (int) $cid;
                    $byCase[$cid] = ($byCase[$cid] ?? 0) + 1;
                }
            }

            arsort($byService);
            arsort($byCase);

            $topServiceIds = array_slice(array_map('intval', array_keys($byService)), 0, 5);
            if ($topServiceIds !== []) {
                $services = TelegramBotService::whereIn('id', $topServiceIds)->get()->keyBy('id');
                $i = 0;
                foreach ($byService as $id => $count) {
                    if ($i >= 5) {
                        break;
                    }
                    $top_services[] = [
                        'service_id' => (int) $id,
                        'name' => $services->get((int) $id)?->name ?? '—',
                        'count' => (int) $count,
                    ];
                    $i++;
                }
            }

            $topCaseIds = array_slice(array_map('intval', array_keys($byCase)), 0, 5);
            if ($topCaseIds !== []) {
                $cases = TelegramBotCase::whereIn('id', $topCaseIds)->get()->keyBy('id');
                $i = 0;
                foreach ($byCase as $id => $count) {
                    if ($i >= 5) {
                        break;
                    }
                    $top_cases[] = [
                        'case_id' => (int) $id,
                        'title' => $cases->get((int) $id)?->title ?? '—',
                        'count' => (int) $count,
                    ];
                    $i++;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('telegram.analytics.top failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return response()->json([
            'data' => [
                'top_services' => array_values($top_services),
                'top_cases' => array_values($top_cases),
            ],
        ]);
    }
}
