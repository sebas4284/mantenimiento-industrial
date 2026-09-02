<?php

namespace Database\Factories;

use App\Models\SparePart;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SparePartUsage>
 */
class SparePartUsageFactory extends Factory
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
            'spare_part_id' => SparePart::factory(),
            'used_by' => User::factory(),
            'quantity' => $this->faker->numberBetween(1, 5),
        ];
    }
}
