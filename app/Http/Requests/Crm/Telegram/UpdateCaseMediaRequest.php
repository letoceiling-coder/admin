<?php

namespace App\Http\Requests\Crm\Telegram;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCaseMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'nullable|string|max:20|in:photo,document',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }
}
