<?php

namespace App\Http\Controllers\Api\Crm\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramLead;
use App\Http\Requests\Crm\Telegram\UpdateLeadRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TelegramLead::query()->with(['service', 'case'])->orderBy('id', 'desc');
        if ($request->filled('status') && in_array($request->input('status'), ['new', 'in_progress', 'done'], true)) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }
        $perPage = min((int) $request->input('per_page', 15), 100);
        $paginator = $query->paginate($perPage);
        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function update(UpdateLeadRequest $request, int $id): JsonResponse
    {
        $item = TelegramLead::findOrFail($id);
        $item->update($request->validated());
        return response()->json(['data' => $item->load(['service', 'case'])]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $query = TelegramLead::query()->with(['service', 'case'])->orderBy('id', 'desc');
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="telegram_leads_' . date('Y-m-d_His') . '.csv"',
        ];
        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'telegram_user_id', 'username', 'name', 'contact', 'message', 'budget_range', 'deadline', 'service_id', 'case_id', 'status', 'created_at'], ';');
            foreach ($query->cursor() as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->telegram_user_id,
                    $row->username,
                    $row->name,
                    $row->contact,
                    $row->message,
                    $row->budget_range,
                    $row->deadline,
                    $row->service_id,
                    $row->case_id,
                    $row->status,
                    $row->created_at?->toDateTimeString(),
                ], ';');
            }
            fclose($handle);
        }, 'telegram_leads.csv', $headers);
    }
}
