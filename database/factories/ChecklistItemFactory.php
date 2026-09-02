<?php

namespace Database\Factories;

use App\Models\ChecklistItem;
use App\Models\ChecklistTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChecklistItem>
 */
class ChecklistItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'checklist_template_id' => ChecklistTemplate::factory(),
            'label' => $this->faker->randomElement(['Revisar nivel de aceite', 'Medir presión', 'Verificar temperatura', 'Inspeccionar fugas', 'Comprobar vibración', 'Ajustar tornillería']),
            'order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
