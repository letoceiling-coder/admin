<?php

namespace App\Http\Requests\Crm\Telegram;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'welcome_text' => 'nullable|string|max:500',
            'start_text' => 'nullable|string|max:500',
            'home_offer_text' => 'nullable|string|max:2000',
            'home_banner_file_id' => 'nullable|string|max:255',
            'site_url' => 'nullable|string|url|max:500',
            'presentation_file_id' => 'nullable|string|max:255',
            'presentation_url' => 'nullable|string|url|max:500',
            'manager_username' => 'nullable|string|max:255',
            'notify_chat_id' => 'nullable|string|max:255',
            'feature_flags' => 'nullable|array',
            'utm_template' => 'nullable|string|max:500',
        ];
    }
}
