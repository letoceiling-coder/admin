<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('telegram_user_id')->nullable();
            $table->string('username')->nullable();
            $table->string('name')->nullable();
            $table->string('contact');
            $table->text('message')->nullable();
            $table->string('budget_range')->nullable();
            $table->string('deadline')->nullable();
            $table->foreignId('service_id')->nullable()->constrained('telegram_bot_services')->nullOnDelete();
            $table->foreignId('case_id')->nullable()->constrained('telegram_bot_cases')->nullOnDelete();
            $table->string('status', 20)->default('new');
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::table('telegram_leads', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_leads');
    }
};
