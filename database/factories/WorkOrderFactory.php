<?php

namespace Database\Factories;

use App\Enums\WorkOrderPriority;
use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderType;
use App\Models\Asset;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrder>
 */
class WorkOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $openedAt = $this->faker->dateTimeBetween('-6 months', 'now');

        return [
            'asset_id' => Asset::factory(),
            'reported_by' => User::factory(),
            'assigned_to' => null,
            'type' => WorkOrderType::Correctivo,
            'priority' => $this->faker->randomElement(WorkOrderPriority::cases()),
            'status' => WorkOrderStatus::Abierta,
            'failure_description' => $this->faker->randomElement([
                'Ruido anormal en el equipo',
                'Fuga de aceite',
                'El motor no enciende',
                'Vibración excesiva',
                'Sobrecalentamiento',
            ]),
            'opened_at' => $openedAt,
        ];
    }

    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $openedAt = $attributes['opened_at'];
            $startedAt = (clone $openedAt)->modify('+'.$this->faker->numberBetween(1, 48).' hours');
            $completedAt = (clone $startedAt)->modify('+'.$this->faker->numberBetween(1, 72).' hours');

            return [
                'assigned_to' => User::factory(),
                'status' => WorkOrderStatus::Completada,
                'resolution_notes' => 'Reparación completada, equipo restaurado a condición operativa.',
                'started_at' => $startedAt,
                'completed_at' => $completedAt,
            ];
        });
    }

    public function preventivo(): static
    {
        return $this->state(fn () => [
            'type' => WorkOrderType::Preventivo,
            'failure_description' => null,
        ]);
    }
}
