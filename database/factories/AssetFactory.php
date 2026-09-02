<?php

namespace Database\Factories;

use App\Enums\AssetCriticality;
use App\Enums\AssetStatus;
use App\Models\Area;
use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'area_id' => Area::factory(),
            'code' => 'EQ-'.$this->faker->unique()->numerify('####'),
            'name' => $this->faker->randomElement(['Compresor', 'Banda transportadora', 'Motor eléctrico', 'Bomba centrífuga', 'Horno industrial', 'Robot soldador']).' '.$this->faker->numberBetween(1, 9),
            'manufacturer' => $this->faker->company(),
            'model' => strtoupper($this->faker->bothify('??-####')),
            'serial_number' => strtoupper($this->faker->bothify('SN-########')),
            'criticality' => $this->faker->randomElement(AssetCriticality::cases()),
            'status' => AssetStatus::Operativo,
        ];
    }
}
