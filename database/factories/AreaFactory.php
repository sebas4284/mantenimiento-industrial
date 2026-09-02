<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Plant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Area>
 */
class AreaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plant_id' => Plant::factory(),
            'name' => $this->faker->randomElement(['Línea 1', 'Línea 2', 'Línea 3', 'Empaque', 'Almacén', 'Utilities']),
        ];
    }
}
