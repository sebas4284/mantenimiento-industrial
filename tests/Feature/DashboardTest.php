<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\WorkOrderPriority;
use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderType;
use App\Livewire\Dashboard;
use App\Models\Area;
use App\Models\Asset;
use App\Models\Plant;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $this->actingAs($admin);

        return $admin;
    }

    private function makeAsset(): Asset
    {
        $plant = Plant::factory()->create();

        return Asset::factory()->for(Area::factory()->for($plant))->create();
    }

    public function test_preventive_compliance_delta_compares_to_previous_period_of_equal_length(): void
    {
        $this->actingAsAdmin();
        $asset = $this->makeAsset();

        // Current period (last 90 days): 1 of 2 preventivos completed => 50%.
        WorkOrder::factory()->preventivo()->create([
            'asset_id' => $asset->id,
            'status' => WorkOrderStatus::Completada,
            'opened_at' => now()->subDays(10),
        ]);
        WorkOrder::factory()->preventivo()->create([
            'asset_id' => $asset->id,
            'status' => WorkOrderStatus::Abierta,
            'opened_at' => now()->subDays(5),
        ]);

        // Previous period (90-180 days ago): 2 of 2 preventivos completed => 100%.
        WorkOrder::factory()->preventivo()->create([
            'asset_id' => $asset->id,
            'status' => WorkOrderStatus::Completada,
            'opened_at' => now()->subDays(120),
        ]);
        WorkOrder::factory()->preventivo()->create([
            'asset_id' => $asset->id,
            'status' => WorkOrderStatus::Completada,
            'opened_at' => now()->subDays(130),
        ]);

        Livewire::test(Dashboard::class)
            ->assertViewHas('preventiveCompliance', 50.0)
            ->assertViewHas('preventiveComplianceDelta', -50.0);
    }

    public function test_backlog_ring_percentage_is_share_in_progress_or_waiting(): void
    {
        $this->actingAsAdmin();
        $asset = $this->makeAsset();

        WorkOrder::factory()->create(['asset_id' => $asset->id, 'status' => WorkOrderStatus::Abierta]);
        WorkOrder::factory()->create(['asset_id' => $asset->id, 'status' => WorkOrderStatus::EnProgreso]);
        WorkOrder::factory()->create(['asset_id' => $asset->id, 'status' => WorkOrderStatus::EnProgreso]);
        WorkOrder::factory()->create(['asset_id' => $asset->id, 'status' => WorkOrderStatus::EnEspera]);

        Livewire::test(Dashboard::class)
            ->assertViewHas('backlogTotal', 4)
            ->assertViewHas('backlogRingPct', 75);
    }

    public function test_top_assets_ranks_by_corrective_count_with_most_recent_technician(): void
    {
        $this->actingAsAdmin();
        $plant = Plant::factory()->create();
        $area = Area::factory()->for($plant)->create();
        $assetA = Asset::factory()->for($area)->create();
        $assetB = Asset::factory()->for($area)->create();
        $earlierTech = User::factory()->role(UserRole::Tecnico)->create(['plant_id' => $plant->id]);
        $latestTech = User::factory()->role(UserRole::Tecnico)->create(['plant_id' => $plant->id]);

        WorkOrder::factory()->create([
            'asset_id' => $assetA->id, 'type' => WorkOrderType::Correctivo,
            'assigned_to' => $earlierTech->id, 'opened_at' => now()->subDays(10),
        ]);
        WorkOrder::factory()->create([
            'asset_id' => $assetA->id, 'type' => WorkOrderType::Correctivo,
            'assigned_to' => $latestTech->id, 'opened_at' => now()->subDays(2),
        ]);
        WorkOrder::factory()->create([
            'asset_id' => $assetA->id, 'type' => WorkOrderType::Correctivo,
            'assigned_to' => null, 'opened_at' => now()->subDays(1),
        ]);
        WorkOrder::factory()->create([
            'asset_id' => $assetB->id, 'type' => WorkOrderType::Correctivo,
            'opened_at' => now()->subDays(3),
        ]);

        Livewire::test(Dashboard::class)->assertViewHas('topAssets', function ($topAssets) use ($assetA, $latestTech) {
            $first = $topAssets->first();

            return $first['code'] === $assetA->code
                && $first['fails'] === 3
                && $first['technician'] === $latestTech->name;
        });
    }

    public function test_attention_card_picks_the_oldest_open_order_at_the_highest_present_priority(): void
    {
        $this->actingAsAdmin();
        $asset = $this->makeAsset();

        WorkOrder::factory()->create([
            'asset_id' => $asset->id, 'priority' => WorkOrderPriority::Baja,
            'status' => WorkOrderStatus::Abierta, 'opened_at' => now()->subDays(1),
        ]);
        WorkOrder::factory()->create([
            'asset_id' => $asset->id, 'priority' => WorkOrderPriority::Media,
            'status' => WorkOrderStatus::Abierta, 'opened_at' => now()->subDays(10),
        ]);
        WorkOrder::factory()->create([
            'asset_id' => $asset->id, 'priority' => WorkOrderPriority::Alta,
            'status' => WorkOrderStatus::EnEspera, 'opened_at' => now()->subDays(5),
        ]);
        $olderAlta = WorkOrder::factory()->create([
            'asset_id' => $asset->id, 'priority' => WorkOrderPriority::Alta,
            'status' => WorkOrderStatus::Abierta, 'opened_at' => now()->subDays(8),
        ]);

        Livewire::test(Dashboard::class)
            ->assertViewHas('attentionWorkOrder', fn ($wo) => $wo->id === $olderAlta->id);
    }
}
