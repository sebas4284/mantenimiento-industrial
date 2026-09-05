<?php

namespace Tests\Feature;

use App\Enums\AssetStatus;
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

    public function test_status_shows_en_mantenimiento_when_an_order_is_in_progress(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $this->actingAs($admin);

        $plant = Plant::factory()->create();
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create(['status' => AssetStatus::Operativo]);

        WorkOrder::factory()->create([
            'asset_id' => $asset->id,
            'status' => WorkOrderStatus::EnProgreso,
        ]);

        Livewire::test(Show::class, ['asset' => $asset])
            ->assertViewHas('displayStatus', AssetStatus::Mantenimiento);
    }

    public function test_status_shows_operativo_when_no_order_is_in_progress(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $this->actingAs($admin);

        $plant = Plant::factory()->create();
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create(['status' => AssetStatus::Operativo]);

        WorkOrder::factory()->create([
            'asset_id' => $asset->id,
            'status' => WorkOrderStatus::Completada,
        ]);

        Livewire::test(Show::class, ['asset' => $asset])
            ->assertViewHas('displayStatus', AssetStatus::Operativo);
    }

    public function test_status_shows_inactivo_regardless_of_active_orders(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $this->actingAs($admin);

        $plant = Plant::factory()->create();
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create(['status' => AssetStatus::Inactivo]);

        WorkOrder::factory()->create([
            'asset_id' => $asset->id,
            'status' => WorkOrderStatus::EnProgreso,
        ]);

        Livewire::test(Show::class, ['asset' => $asset])
            ->assertViewHas('displayStatus', AssetStatus::Inactivo);
    }

    public function test_availability_percent_is_reduced_by_completed_correctivo_downtime(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $this->actingAs($admin);

        $plant = Plant::factory()->create();
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create([
            'created_at' => now()->subDays(10),
        ]);

        WorkOrder::factory()->create([
            'asset_id' => $asset->id,
            'type' => WorkOrderType::Correctivo,
            'status' => WorkOrderStatus::Completada,
            'opened_at' => now()->subDays(5),
            'started_at' => now()->subDays(5),
            'completed_at' => now()->subDays(5)->addHours(24),
        ]);

        $availability = Livewire::test(Show::class, ['asset' => $asset])
            ->viewData('availabilityPercent');

        $this->assertNotNull($availability);
        $this->assertLessThan(100, $availability);
        $this->assertGreaterThan(0, $availability);
    }

    public function test_availability_uses_the_earliest_work_order_when_it_predates_the_asset_row(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $this->actingAs($admin);

        $plant = Plant::factory()->create();
        // Reproduces seeded/imported data: the asset row itself was only
        // inserted "yesterday", but its real maintenance history goes back
        // months. Before the fix this collapsed availability to 0% because
        // the observed window was measured from the asset's created_at
        // instead of its actual history.
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create([
            'created_at' => now()->subDay(),
        ]);

        WorkOrder::factory()->create([
            'asset_id' => $asset->id,
            'type' => WorkOrderType::Correctivo,
            'status' => WorkOrderStatus::Completada,
            'opened_at' => now()->subDays(90),
            'started_at' => now()->subDays(90),
            'completed_at' => now()->subDays(90)->addHours(48),
        ]);

        $component = Livewire::test(Show::class, ['asset' => $asset]);

        $this->assertGreaterThan(80, $component->viewData('availabilityPercent'));
        $this->assertGreaterThan(100, $component->viewData('mtbfHours'));
    }

    public function test_correctivo_and_preventivo_percentages_reflect_this_assets_own_orders(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $this->actingAs($admin);

        $plant = Plant::factory()->create();
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create();

        WorkOrder::factory()->create(['asset_id' => $asset->id, 'type' => WorkOrderType::Correctivo]);
        WorkOrder::factory()->create(['asset_id' => $asset->id, 'type' => WorkOrderType::Preventivo]);
        WorkOrder::factory()->create(['asset_id' => $asset->id, 'type' => WorkOrderType::Preventivo]);

        Livewire::test(Show::class, ['asset' => $asset])
            ->assertViewHas('correctivoPercent', 33.3)
            ->assertViewHas('preventivoPercent', 66.7);
    }

    public function test_correctivo_and_preventivo_percentages_are_null_without_any_maintenance(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $this->actingAs($admin);

        $plant = Plant::factory()->create();
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create();

        Livewire::test(Show::class, ['asset' => $asset])
            ->assertViewHas('correctivoPercent', null)
            ->assertViewHas('preventivoPercent', null);
    }

    public function test_admin_can_edit_the_asset(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $this->actingAs($admin);

        $plant = Plant::factory()->create();
        $area = Area::factory()->for($plant)->create();
        $asset = Asset::factory()->for($area)->create();
        $newArea = Area::factory()->for($plant)->create();

        Livewire::test(Show::class, ['asset' => $asset])
            ->set('edit_area_id', $newArea->id)
            ->set('edit_code', 'EQ-NEW-001')
            ->set('edit_name', 'Motor renombrado')
            ->set('edit_criticality', 'A')
            ->set('edit_status', 'inactivo')
            ->call('saveEdit')
            ->assertHasNoErrors();

        $asset->refresh();
        $this->assertSame($newArea->id, $asset->area_id);
        $this->assertSame('EQ-NEW-001', $asset->code);
        $this->assertSame('Motor renombrado', $asset->name);
        $this->assertSame(AssetStatus::Inactivo, $asset->status);
    }

    public function test_editing_rejects_mantenimiento_as_a_manual_status(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $this->actingAs($admin);

        $plant = Plant::factory()->create();
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create();

        Livewire::test(Show::class, ['asset' => $asset])
            ->set('edit_status', 'mantenimiento')
            ->call('saveEdit')
            ->assertHasErrors(['edit_status']);
    }

    public function test_a_technician_cannot_edit_the_asset(): void
    {
        $plant = Plant::factory()->create();
        $technician = User::factory()->role(UserRole::Tecnico)->create(['plant_id' => $plant->id]);
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create();

        $this->actingAs($technician);

        Livewire::test(Show::class, ['asset' => $asset])
            ->call('openEditModal')
            ->assertForbidden();
    }
}
