<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        
        // Проверяем, существуют ли планы, чтобы избежать дубликатов
        $plans = [
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
        ];

        foreach ($plans as $plan) {
            $exists = DB::table('plans')->where('name', $plan['name'])->exists();
            if (!$exists) {
                DB::table('plans')->insert($plan);
            } else {
                // Обновляем существующий план, если нужно
                DB::table('plans')
                    ->where('name', $plan['name'])
                    ->update([
                        'cost' => $plan['cost'],
                        'is_active' => $plan['is_active'],
                        'limits' => $plan['limits'],
                        'updated_at' => $now,
                    ]);
            }
        }
    }
}
