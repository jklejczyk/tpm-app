<?php

namespace Database\Factories;

use App\Models\MachineModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MachineModel>
 */
class MachineModelFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->unique()->bothify('machine-###'),
            'name' => fake()->words(2, true),
        ];
    }
}
