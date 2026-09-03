<?php

namespace Database\Factories;

use App\Enums\PreOperationalRequiredAction;
use App\Enums\PreOperationalResult;
use App\Models\Asset;
use App\Models\PreOperationalChecklist;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PreOperationalChecklist>
 */
class PreOperationalChecklistFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'asset_id' => Asset::factory(),
            'performed_by' => User::factory(),
            'inspected_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'result' => PreOperationalResult::Apto,
            'anomaly_notes' => null,
            'required_action' => PreOperationalRequiredAction::Ninguna,
            'additional_notes' => null,
        ];
    }
}
