<?php

namespace App\Http\Requests\Crm\Telegram;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => 'sometimes|required|string|max:500',
            'answer' => 'sometimes|required|string|max:5000',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }
}
