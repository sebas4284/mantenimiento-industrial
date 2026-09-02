<?php

namespace Database\Factories;

use App\Models\Plant;
use App\Models\SparePart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SparePart>
 */
class SparePartFactory extends Factory
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
            'code' => 'RP-'.$this->faker->unique()->numerify('####'),
            'name' => $this->faker->randomElement(['Rodamiento 6205', 'Correa en V', 'Filtro de aceite', 'Sello mecánico', 'Fusible 10A', 'Manguera hidráulica', 'Sensor de proximidad', 'Empaque de válvula']),
            'stock_quantity' => $this->faker->numberBetween(0, 40),
            'minimum_stock' => $this->faker->numberBetween(5, 15),
        ];
    }
}
