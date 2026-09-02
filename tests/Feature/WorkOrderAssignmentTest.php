<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\WorkOrderExecutionType;
use App\Enums\WorkOrderStatus;
use App\Livewire\WorkOrders\Show;
use App\Models\Area;
use App\Models\Asset;
use App\Models\Plant;
use App\Models\Provider;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WorkOrderAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_order_shows_collaborator_field_and_can_be_assigned(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $technician = User::factory()->role(UserRole::Tecnico)->create();
        $workOrder = WorkOrder::factory()->create(['execution_type' => WorkOrderExecutionType::Interno]);

        $this->actingAs($admin);

        Livewire::test(Show::class, ['workOrder' => $workOrder])
            ->assertSee('Colaborador asignado')
            ->assertDontSee('Proveedor')
            ->assertDontSee('Colaborador asignado de apoyo')
            ->set('assigned_to', $technician->id)
            ->call('assign')
            ->assertHasNoErrors();

        $this->assertSame($technician->id, $workOrder->fresh()->assigned_to);
    }

    public function test_unassigning_an_internal_order_is_allowed(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $technician = User::factory()->role(UserRole::Tecnico)->create();
        $workOrder = WorkOrder::factory()->create([
            'execution_type' => WorkOrderExecutionType::Interno,
            'assigned_to' => $technician->id,
        ]);

        $this->actingAs($admin);

        Livewire::test(Show::class, ['workOrder' => $workOrder])
            ->set('assigned_to', '')
            ->call('assign')
            ->assertHasNoErrors();

        $this->assertNull($workOrder->fresh()->assigned_to);
    }

    public function test_external_order_shows_provider_and_support_collaborator_fields_and_can_be_assigned(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $supportCollaborator = User::factory()->role(UserRole::Tecnico)->create();
        $provider = Provider::factory()->create();
        $workOrder = WorkOrder::factory()->create(['execution_type' => WorkOrderExecutionType::Externo]);

        $this->actingAs($admin);

        Livewire::test(Show::class, ['workOrder' => $workOrder])
            ->assertSee('Proveedor')
            ->assertSee('Colaborador asignado de apoyo')
            ->set('provider_id', $provider->id)
            ->set('support_collaborator_id', $supportCollaborator->id)
            ->call('assign')
            ->assertHasNoErrors();

        $workOrder->refresh();
        $this->assertSame($provider->id, $workOrder->provider_id);
        $this->assertSame($supportCollaborator->id, $workOrder->support_collaborator_id);
    }

    public function test_external_order_requires_a_provider(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $workOrder = WorkOrder::factory()->create(['execution_type' => WorkOrderExecutionType::Externo]);

        $this->actingAs($admin);

        Livewire::test(Show::class, ['workOrder' => $workOrder])
            ->set('provider_id', '')
            ->call('assign')
            ->assertHasErrors(['provider_id' => 'required']);
    }

    public function test_selecting_a_busy_technician_shows_a_warning(): void
    {
        $plant = Plant::factory()->create();
        $admin = User::factory()->role(UserRole::Admin)->create();
        $busyTechnician = User::factory()->role(UserRole::Tecnico)->create(['plant_id' => $plant->id]);
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create();

        WorkOrder::factory()->create([
            'asset_id' => $asset->id,
            'assigned_to' => $busyTechnician->id,
            'execution_type' => WorkOrderExecutionType::Interno,
            'status' => WorkOrderStatus::EnProgreso,
        ]);

        $workOrder = WorkOrder::factory()->create([
            'asset_id' => $asset->id,
            'execution_type' => WorkOrderExecutionType::Interno,
        ]);

        $this->actingAs($admin);

        Livewire::test(Show::class, ['workOrder' => $workOrder])
            ->set('assigned_to', $busyTechnician->id)
            ->assertSee('ya tiene una orden en curso');
    }

    public function test_selecting_a_free_technician_shows_no_warning(): void
    {
        $plant = Plant::factory()->create();
        $admin = User::factory()->role(UserRole::Admin)->create();
        $freeTechnician = User::factory()->role(UserRole::Tecnico)->create(['plant_id' => $plant->id]);
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create();

        $workOrder = WorkOrder::factory()->create([
            'asset_id' => $asset->id,
            'execution_type' => WorkOrderExecutionType::Interno,
        ]);

        $this->actingAs($admin);

        Livewire::test(Show::class, ['workOrder' => $workOrder])
            ->set('assigned_to', $freeTechnician->id)
            ->assertDontSee('ya tiene una orden en curso');
    }

    public function test_technician_already_in_progress_on_the_current_order_is_not_shown_as_busy(): void
    {
        $plant = Plant::factory()->create();
        $admin = User::factory()->role(UserRole::Admin)->create();
        $technician = User::factory()->role(UserRole::Tecnico)->create(['plant_id' => $plant->id]);
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create();

        $workOrder = WorkOrder::factory()->create([
            'asset_id' => $asset->id,
            'assigned_to' => $technician->id,
            'execution_type' => WorkOrderExecutionType::Interno,
            'status' => WorkOrderStatus::EnProgreso,
        ]);

        $this->actingAs($admin);

        Livewire::test(Show::class, ['workOrder' => $workOrder])
            ->set('assigned_to', $technician->id)
            ->assertDontSee('ya tiene una orden en curso');
    }

    public function test_selecting_a_busy_technician_as_support_collaborator_shows_a_warning(): void
    {
        $plant = Plant::factory()->create();
        $admin = User::factory()->role(UserRole::Admin)->create();
        $busyTechnician = User::factory()->role(UserRole::Tecnico)->create(['plant_id' => $plant->id]);
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create();

        WorkOrder::factory()->create([
            'asset_id' => $asset->id,
            'support_collaborator_id' => $busyTechnician->id,
            'execution_type' => WorkOrderExecutionType::Externo,
            'status' => WorkOrderStatus::EnProgreso,
        ]);

        $workOrder = WorkOrder::factory()->create([
            'asset_id' => $asset->id,
            'execution_type' => WorkOrderExecutionType::Externo,
        ]);

        $this->actingAs($admin);

        Livewire::test(Show::class, ['workOrder' => $workOrder])
            ->set('support_collaborator_id', $busyTechnician->id)
            ->assertSee('ya tiene una orden en curso');
    }

    public function test_assigning_a_busy_technician_still_succeeds(): void
    {
        $plant = Plant::factory()->create();
        $admin = User::factory()->role(UserRole::Admin)->create();
        $busyTechnician = User::factory()->role(UserRole::Tecnico)->create(['plant_id' => $plant->id]);
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create();

        WorkOrder::factory()->create([
            'asset_id' => $asset->id,
            'assigned_to' => $busyTechnician->id,
            'execution_type' => WorkOrderExecutionType::Interno,
            'status' => WorkOrderStatus::EnProgreso,
        ]);

        $workOrder = WorkOrder::factory()->create([
            'asset_id' => $asset->id,
            'execution_type' => WorkOrderExecutionType::Interno,
        ]);

        $this->actingAs($admin);

        Livewire::test(Show::class, ['workOrder' => $workOrder])
            ->set('assigned_to', $busyTechnician->id)
            ->call('assign')
            ->assertHasNoErrors();

        $this->assertSame($busyTechnician->id, $workOrder->fresh()->assigned_to);
    }
}
