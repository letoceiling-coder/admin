<?php

namespace App\Http\Requests\Crm\Telegram;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:telegram_bot_service_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'result' => 'nullable|string|max:2000',
            'price_or_terms' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }
}
