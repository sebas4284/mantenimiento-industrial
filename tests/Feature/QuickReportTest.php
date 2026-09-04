<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\WorkOrderType;
use App\Livewire\WorkOrders\QuickReport;
use App\Models\Area;
use App\Models\Asset;
use App\Models\Plant;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuickReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_page_renders_with_the_asset_already_fixed(): void
    {
        $plant = Plant::factory()->create();
        $tecnico = User::factory()->role(UserRole::Tecnico)->create(['plant_id' => $plant->id]);
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create();

        $this->actingAs($tecnico);

        Livewire::test(QuickReport::class, ['asset' => $asset])
            ->assertSee($asset->code)
            ->assertSee($asset->name)
            ->assertDontSee('Selecciona un activo');
    }

    public function test_submitting_creates_a_work_order_for_that_asset_without_an_asset_field(): void
    {
        $plant = Plant::factory()->create();
        $tecnico = User::factory()->role(UserRole::Tecnico)->create(['plant_id' => $plant->id]);
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create();

        $this->actingAs($tecnico);

        Livewire::test(QuickReport::class, ['asset' => $asset])
            ->set('type', WorkOrderType::Correctivo->value)
            ->set('priority', 'alta')
            ->set('execution_type', 'interno')
            ->set('failure_description', 'Ruido anormal en el motor')
            ->call('report')
            ->assertHasNoErrors()
            ->assertSee('Falla reportada correctamente');

        $this->assertSame(1, WorkOrder::where('asset_id', $asset->id)->count());
    }
}
