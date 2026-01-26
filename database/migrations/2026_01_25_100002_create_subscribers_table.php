<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique();
            $table->string('login');
            $table->date('subscription_start')->nullable();
            $table->date('subscription_end')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->string('api_token', 64)->unique()->nullable();
            $table->json('payment_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscribers');
    }
};
