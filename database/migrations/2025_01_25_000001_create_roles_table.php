<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedTinyInteger('level')->default(1);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::table('roles')->insert([
            ['name' => 'user', 'level' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manager', 'level' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'administrator', 'level' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'developer', 'level' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
