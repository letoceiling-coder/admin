<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommercialProposalMailing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class CommercialProposalController extends Controller
{
    private const BOT_USERNAME = 'test_pirogi_ek_bot';
    private const CONTACT_USERNAME = 'rinellestudio';

    public function preview(): JsonResponse
    {
        $html = view('emails.commercial-proposal', [
            'botUrl' => 'https://t.me/' . self::BOT_USERNAME,
            'contactUrl' => 'https://t.me/' . self::CONTACT_USERNAME,
        ])->render();

        return response()->json(['html' => $html]);
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 20);
        $perPage = $perPage >= 1 && $perPage <= 100 ? $perPage : 20;

        $mailings = CommercialProposalMailing::query()
            ->orderByDesc('sent_at')
            ->paginate($perPage);

        return response()->json($mailings);
    }

    public function send(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Укажите email.',
            'email.email' => 'Некорректный формат email.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = $request->input('email');
        $botUrl = 'https://t.me/' . self::BOT_USERNAME;
        $contactUrl = 'https://t.me/' . self::CONTACT_USERNAME;

        try {
            Mail::send('emails.commercial-proposal', [
                'botUrl' => $botUrl,
                'contactUrl' => $contactUrl,
            ], function ($message) use ($email) {
                $message->to($email)
                    ->subject('Коммерческое предложение: Telegram-бот с Mini App для приёма заказов');
            });
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Не удалось отправить письмо. Попробуйте позже.',
            ], 500);
        }

        CommercialProposalMailing::create([
            'email' => $email,
            'sent_at' => now(),
        ]);

        return response()->json(['message' => 'Коммерческое предложение отправлено.']);
    }

    public function resend(CommercialProposalMailing $mailing): JsonResponse
    {
        $email = $mailing->email;
        $botUrl = 'https://t.me/' . self::BOT_USERNAME;
        $contactUrl = 'https://t.me/' . self::CONTACT_USERNAME;

        try {
            Mail::send('emails.commercial-proposal', [
                'botUrl' => $botUrl,
                'contactUrl' => $contactUrl,
            ], function ($message) use ($email) {
                $message->to($email)
                    ->subject('Коммерческое предложение: Telegram-бот с Mini App для приёма заказов');
            });
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Не удалось отправить письмо. Попробуйте позже.',
            ], 500);
        }

        CommercialProposalMailing::create([
            'email' => $email,
            'sent_at' => now(),
        ]);

        return response()->json(['message' => 'КП повторно отправлено на ' . $email . '.']);
    }
}
