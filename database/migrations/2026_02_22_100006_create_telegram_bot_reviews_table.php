<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_bot_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('author_name')->nullable();
            $table->string('company')->nullable();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('text');
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('telegram_user_id')->nullable();
            $table->timestamps();
        });

        Schema::table('telegram_bot_reviews', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
            $table->index('telegram_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_bot_reviews');
    }
};
