<?php

namespace App\Http\Requests\Telegram;

use Illuminate\Foundation\Http\FormRequest;

class StoreCallRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tg_user_id' => 'required|integer',
            'username' => 'nullable|string|max:255',
            'full_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:255',
            'preferred_time' => 'nullable|string|max:500',
            'comment' => 'nullable|string|max:2000',
            'source_service_id' => 'nullable|integer|exists:telegram_bot_services,id',
            'source_case_id' => 'nullable|integer|exists:telegram_bot_cases,id',
        ];
    }
}
