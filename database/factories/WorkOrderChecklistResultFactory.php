<?php

namespace Database\Factories;

use App\Models\ChecklistItem;
use App\Models\WorkOrder;
use App\Models\WorkOrderChecklistResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrderChecklistResult>
 */
class WorkOrderChecklistResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'work_order_id' => WorkOrder::factory(),
            'checklist_item_id' => ChecklistItem::factory(),
            'passed' => $this->faker->boolean(90),
            'notes' => null,
        ];
    }
}
