<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        DB::table('plans')->insert([
            [
                'name' => 'standard',
                'cost' => 0,
                'is_active' => true,
                'limits' => json_encode(['max_users' => 5, 'description' => 'Стандартный план']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'premium',
                'cost' => 1,
                'is_active' => true,
                'limits' => json_encode(['max_users' => null, 'description' => 'Премиум без ограничений']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
