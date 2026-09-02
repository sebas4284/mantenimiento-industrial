<?php

namespace Database\Factories;

use App\Models\Plant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plant>
 */
class PlantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Planta '.$this->faker->city(),
            'location' => $this->faker->city().', '.$this->faker->country(),
            'code' => strtoupper($this->faker->unique()->lexify('???')),
        ];
    }
}
