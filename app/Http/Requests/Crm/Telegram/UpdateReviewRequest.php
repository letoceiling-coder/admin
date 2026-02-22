<?php

namespace App\Http\Requests\Crm\Telegram;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'author_name' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'rating' => 'nullable|integer|min:1|max:5',
            'text' => 'nullable|string|max:5000',
        ];
    }
}
