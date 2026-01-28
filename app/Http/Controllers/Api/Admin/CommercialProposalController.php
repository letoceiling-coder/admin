<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommercialProposalMailing;
use App\Models\CommercialProposalUnsubscribe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
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
            'unsubscribeUrl' => '#',
        ])->render();

        return response()->json(['html' => $html]);
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 20);
        $perPage = $perPage >= 1 && $perPage <= 100 ? $perPage : 20;

        $query = CommercialProposalMailing::query()
            ->selectRaw('email, count(*) as send_count, max(sent_at) as last_sent_at')
            ->groupBy('email')
            ->orderByDesc('last_sent_at');

        $mailings = $query->paginate($perPage);
        $unsubscribedEmails = CommercialProposalUnsubscribe::query()
            ->whereIn('email', $mailings->pluck('email'))
            ->pluck('email')
            ->flip()
            ->toArray();

        $mailings->getCollection()->transform(function ($item) use ($unsubscribedEmails) {
            $item->can_send = !isset($unsubscribedEmails[$item->email]);
            return $item;
        });

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

        if (CommercialProposalUnsubscribe::isUnsubscribed($email)) {
            return response()->json([
                'message' => 'Этот адрес в списке «Не беспокоить». Отправка запрещена.',
            ], 422);
        }

        $botUrl = 'https://t.me/' . self::BOT_USERNAME;
        $contactUrl = 'https://t.me/' . self::CONTACT_USERNAME;
        $unsubscribeUrl = URL::temporarySignedRoute(
            'api.commercial-proposal.unsubscribe',
            now()->addDays(30),
            ['email' => $email]
        );

        try {
            $fromAddress = config('mail.from.address');
            $fromName = config('mail.from.name');
            Mail::send('emails.commercial-proposal', [
                'botUrl' => $botUrl,
                'contactUrl' => $contactUrl,
                'unsubscribeUrl' => $unsubscribeUrl,
            ], function ($message) use ($email, $fromAddress, $fromName) {
                $message->from($fromAddress, $fromName)
                    ->to($email)
                    ->replyTo($fromAddress, $fromName)
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

    public function resend(Request $request): JsonResponse
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

        if (CommercialProposalUnsubscribe::isUnsubscribed($email)) {
            return response()->json([
                'message' => 'Этот адрес в списке «Не беспокоить». Повторная отправка запрещена.',
            ], 422);
        }

        $botUrl = 'https://t.me/' . self::BOT_USERNAME;
        $contactUrl = 'https://t.me/' . self::CONTACT_USERNAME;
        $unsubscribeUrl = URL::temporarySignedRoute(
            'api.commercial-proposal.unsubscribe',
            now()->addDays(30),
            ['email' => $email]
        );

        try {
            $fromAddress = config('mail.from.address');
            $fromName = config('mail.from.name');
            Mail::send('emails.commercial-proposal', [
                'botUrl' => $botUrl,
                'contactUrl' => $contactUrl,
                'unsubscribeUrl' => $unsubscribeUrl,
            ], function ($message) use ($email, $fromAddress, $fromName) {
                $message->from($fromAddress, $fromName)
                    ->to($email)
                    ->replyTo($fromAddress, $fromName)
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

    public function unsubscribe(Request $request)
    {
        if (!$request->hasValidSignature()) {
            return response()->json(['message' => 'Недействительная или просроченная ссылка.'], 403);
        }

        $email = $request->query('email');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'Некорректный email.'], 422);
        }

        CommercialProposalUnsubscribe::firstOrCreate(['email' => $email]);

        return response()->view('commercial-proposal-unsubscribed');
    }
}
