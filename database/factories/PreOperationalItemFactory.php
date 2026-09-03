<?php

namespace Database\Factories;

use App\Models\PreOperationalItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PreOperationalItem>
 */
class PreOperationalItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'section' => $this->faker->randomElement(['Condiciones generales', 'Seguridad y protecciones', 'Sistema mecánico']),
            'label' => $this->faker->randomElement(['¿La máquina se encuentra en buen estado general?', '¿Las guardas y protecciones están instaladas?', '¿No se presentan ruidos anormales?']),
            'order' => $this->faker->numberBetween(1, 40),
        ];
    }
}
