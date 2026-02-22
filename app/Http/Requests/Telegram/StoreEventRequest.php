<?php

namespace App\Http\Requests\Telegram;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tg_user_id' => 'required|integer',
            'event_name' => 'required|string|in:screen_view,cta_click,lead_created,ticket_created',
            'payload_json' => 'nullable|array',
        ];
    }
}
