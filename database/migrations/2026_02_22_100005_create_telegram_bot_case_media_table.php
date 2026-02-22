<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_bot_case_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('telegram_bot_cases')->cascadeOnDelete();
            $table->string('file_id');
            $table->string('type', 20)->default('photo');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_bot_case_media');
    }
};
