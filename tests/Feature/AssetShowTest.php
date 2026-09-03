<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderType;
use App\Livewire\Assets\Show;
use App\Models\Area;
use App\Models\Asset;
use App\Models\MaintenancePlan;
use App\Models\Plant;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AssetShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_kpi_row_shows_mttr_and_the_nearest_active_preventive_due_date(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $this->actingAs($admin);

        $plant = Plant::factory()->create();
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create();

        WorkOrder::factory()->create([
            'asset_id' => $asset->id,
            'type' => WorkOrderType::Correctivo,
            'status' => WorkOrderStatus::Completada,
            'started_at' => now()->subDays(10),
            'completed_at' => now()->subDays(10)->addHours(4),
        ]);

        MaintenancePlan::factory()->create([
            'asset_id' => $asset->id, 'active' => true, 'next_due_date' => now()->addDays(9),
        ]);
        MaintenancePlan::factory()->create([
            'asset_id' => $asset->id, 'active' => true, 'next_due_date' => now()->addDays(30),
        ]);
        MaintenancePlan::factory()->create([
            'asset_id' => $asset->id, 'active' => false, 'next_due_date' => now()->addDay(),
        ]);

        Livewire::test(Show::class, ['asset' => $asset])
            ->assertViewHas('mttrHours', 4.0)
            ->assertViewHas('nextPreventiveDate', fn ($date) => $date->isSameDay(now()->addDays(9)));
    }

    public function test_kpi_row_has_no_mtbf_or_mttr_when_the_asset_has_no_correctivos(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $this->actingAs($admin);

        $plant = Plant::factory()->create();
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create();

        Livewire::test(Show::class, ['asset' => $asset])
            ->assertViewHas('mtbfHours', null)
            ->assertViewHas('mttrHours', null)
            ->assertViewHas('nextPreventiveDate', null);
    }
}
