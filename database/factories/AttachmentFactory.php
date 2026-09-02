<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attachable_type' => WorkOrder::class,
            'attachable_id' => WorkOrder::factory(),
            'uploaded_by' => User::factory(),
            'path' => 'attachments/'.$this->faker->uuid().'.jpg',
        ];
    }
}
