<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_applications', function (Blueprint $table) {
            $table->id();
            $table->string('domain');
            $table->string('name');
            $table->string('email');
            $table->string('api_token', 64)->unique();
            $table->timestamp('expires_at');
            $table->string('status', 20)->default('pending'); // pending, approved, rejected
            $table->timestamps();
        });

        Schema::table('subscription_applications', function (Blueprint $table) {
            $table->index(['domain', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_applications');
    }
};
