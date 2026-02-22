<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_bot_settings', function (Blueprint $table) {
            $table->id();
            $table->string('welcome_text')->nullable();
            $table->string('start_text')->nullable();
            $table->text('home_offer_text')->nullable();
            $table->string('home_banner_file_id')->nullable();
            $table->string('site_url')->nullable();
            $table->string('presentation_file_id')->nullable();
            $table->string('presentation_url')->nullable();
            $table->string('manager_username')->nullable();
            $table->string('notify_chat_id')->nullable();
            $table->json('feature_flags')->nullable();
            $table->string('utm_template')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_bot_settings');
    }
};
