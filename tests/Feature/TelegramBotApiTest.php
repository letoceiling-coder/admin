<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class TelegramBotApiTest extends TestCase
{
    private const TEST_TOKEN = 'test-telegram-bot-token';

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.telegram.bot_api_token', self::TEST_TOKEN);
    }

    public function test_telegram_settings_returns_401_without_token(): void
    {
        $response = $this->getJson('/api/telegram/settings');
        $response->assertStatus(401);
        $response->assertJson(['message' => 'Unauthorized']);
    }

    public function test_telegram_settings_returns_401_with_wrong_token(): void
    {
        $response = $this->getJson('/api/telegram/settings', [
            'Authorization' => 'Bearer wrong-token',
        ]);
        $response->assertStatus(401);
    }

    public function test_telegram_settings_returns_200_with_valid_token(): void
    {
        $response = $this->getJson('/api/telegram/settings', [
            'Authorization' => 'Bearer ' . self::TEST_TOKEN,
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [
            'welcome_text', 'start_text', 'home_offer_text', 'home_banner_file_id',
            'site_url', 'presentation_file_id', 'presentation_url', 'manager_username',
            'notify_chat_id', 'feature_flags', 'utm_template',
        ]]);
    }
}
