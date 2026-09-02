<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\ChecklistTemplate;
use App\Models\MaintenancePlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenancePlan>
 */
class MaintenancePlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $frequencyDays = $this->faker->randomElement([7, 15, 30, 90]);

        return [
            'asset_id' => Asset::factory(),
            'checklist_template_id' => ChecklistTemplate::factory(),
            'name' => $this->faker->randomElement(['Mantenimiento preventivo mensual', 'Lubricación programada', 'Revisión trimestral']),
            'frequency_days' => $frequencyDays,
            'next_due_date' => now()->addDays($frequencyDays),
            'active' => true,
        ];
    }
}
