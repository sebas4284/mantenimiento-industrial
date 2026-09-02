<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\WorkOrderExecutionType;
use App\Enums\WorkOrderStatus;
use App\Livewire\Team\Index;
use App\Livewire\Team\Show;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_technician_cannot_view_team_index(): void
    {
        $technician = User::factory()->role(UserRole::Tecnico)->create();

        $this->actingAs($technician)->get(route('team.index'))->assertForbidden();
    }

    public function test_admin_can_view_team_index_with_availability_badges(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $busyTechnician = User::factory()->role(UserRole::Tecnico)->create(['name' => 'Tecnico Ocupado']);
        $freeTechnician = User::factory()->role(UserRole::Tecnico)->create(['name' => 'Tecnico Libre']);

        WorkOrder::factory()->create([
            'assigned_to' => $busyTechnician->id,
            'execution_type' => WorkOrderExecutionType::Interno,
            'status' => WorkOrderStatus::EnProgreso,
        ]);

        $this->actingAs($admin);

        Livewire::test(Index::class)
            ->assertSee('Tecnico Ocupado')
            ->assertSee('Tecnico Libre')
            ->assertSee('Ocupado')
            ->assertSee('Disponible');
    }

    public function test_supervisor_only_sees_collaborators_from_their_own_plant(): void
    {
        $ownPlantTechnician = User::factory()->role(UserRole::Tecnico)->create(['name' => 'Del Equipo']);
        $supervisor = User::factory()->role(UserRole::Supervisor)->create(['plant_id' => $ownPlantTechnician->plant_id]);
        $otherPlantTechnician = User::factory()->role(UserRole::Tecnico)->create(['name' => 'De Otra Planta']);

        $this->actingAs($supervisor);

        Livewire::test(Index::class)
            ->assertSee('Del Equipo')
            ->assertDontSee('De Otra Planta');
    }

    public function test_collaborator_profile_shows_history_with_principal_and_support_roles(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $technician = User::factory()->role(UserRole::Tecnico)->create();

        $principalOrder = WorkOrder::factory()->create([
            'assigned_to' => $technician->id,
            'execution_type' => WorkOrderExecutionType::Interno,
            'status' => WorkOrderStatus::Completada,
        ]);

        $supportOrder = WorkOrder::factory()->create([
            'execution_type' => WorkOrderExecutionType::Externo,
            'support_collaborator_id' => $technician->id,
            'status' => WorkOrderStatus::Completada,
        ]);

        $this->actingAs($admin);

        Livewire::test(Show::class, ['member' => $technician])
            ->assertSee($principalOrder->order_number)
            ->assertSee($supportOrder->order_number)
            ->assertSee('Principal')
            ->assertSee('Apoyo');
    }
}
