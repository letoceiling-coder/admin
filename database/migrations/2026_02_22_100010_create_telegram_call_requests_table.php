<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_call_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('telegram_user_id')->nullable();
            $table->string('username')->nullable();
            $table->string('name')->nullable();
            $table->string('phone');
            $table->string('preferred_time')->nullable();
            $table->text('comment')->nullable();
            $table->string('status', 20)->default('new');
            $table->timestamps();
        });

        Schema::table('telegram_call_requests', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_call_requests');
    }
};
