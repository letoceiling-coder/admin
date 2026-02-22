<?php

namespace App\Http\Requests\Crm\Telegram;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:new,in_progress,done',
        ];
    }
}
