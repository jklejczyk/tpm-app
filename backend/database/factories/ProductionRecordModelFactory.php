<?php

namespace Database\Factories;

use App\Models\MachineModel;
use App\Models\ProductionRecordModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductionRecordModel>
 */
class ProductionRecordModelFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $produced = fake()->numberBetween(400, 1000);

        return [
            'id' => (string) Str::ulid(),
            'machine_id' => fn () => MachineModel::factory()->create()->id,
            'period_start' => now()->startOfDay()->addHours(6),
            'period_end' => now()->startOfDay()->addHours(14),
            'produced_units' => $produced,
            'defective_units' => fake()->numberBetween(0, (int) ($produced * 0.1)),
            'ideal_cycle_time' => 30,
        ];
    }
}
