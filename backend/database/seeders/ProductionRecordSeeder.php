<?php

namespace Database\Seeders;

use App\Models\ProductionRecordModel;
use Illuminate\Database\Seeder;

class ProductionRecordSeeder extends Seeder
{
    public function run(): void
    {
        ProductionRecordModel::factory()->create([
            'machine_id' => 'press-01',
            'period_start' => '2026-01-01 08:00:00',
            'period_end' => '2026-01-01 16:00:00',
            'produced_units' => 800,
            'defective_units' => 20,
            'ideal_cycle_time' => 30,
        ]);
    }
}
