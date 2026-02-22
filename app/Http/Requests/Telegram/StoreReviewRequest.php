<?php

namespace App\Http\Requests\Telegram;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
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
            'rating' => 'required|integer|min:1|max:5',
            'text' => 'required|string|max:5000',
            'company' => 'nullable|string|max:255',
        ];
    }
}
