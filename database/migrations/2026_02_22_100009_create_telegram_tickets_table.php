<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('telegram_user_id')->nullable();
            $table->string('username')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->string('attachment_file_id')->nullable();
            $table->string('status', 20)->default('new');
            $table->timestamps();
        });

        Schema::table('telegram_tickets', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_tickets');
    }
};
