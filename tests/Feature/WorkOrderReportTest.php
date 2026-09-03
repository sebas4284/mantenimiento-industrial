<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\WorkOrderExecutionType;
use App\Enums\WorkOrderPriority;
use App\Enums\WorkOrderType;
use App\Livewire\WorkOrders\Show;
use App\Models\Area;
use App\Models\Asset;
use App\Models\Plant;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WorkOrderReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_edit_general_fields(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $workOrder = WorkOrder::factory()->create([
            'type' => WorkOrderType::Correctivo,
            'priority' => WorkOrderPriority::Media,
            'execution_type' => WorkOrderExecutionType::Interno,
            'failure_description' => 'Falla original',
        ]);

        $this->actingAs($admin);

        Livewire::test(Show::class, ['workOrder' => $workOrder])
            ->set('edit_type', WorkOrderType::Preventivo->value)
            ->set('edit_priority', WorkOrderPriority::Urgente->value)
            ->set('edit_execution_type', WorkOrderExecutionType::Externo->value)
            ->set('edit_failure_description', 'Descripción actualizada')
            ->call('saveEdit')
            ->assertHasNoErrors();

        $workOrder->refresh();
        $this->assertSame(WorkOrderType::Preventivo, $workOrder->type);
        $this->assertSame(WorkOrderPriority::Urgente, $workOrder->priority);
        $this->assertSame(WorkOrderExecutionType::Externo, $workOrder->execution_type);
        $this->assertSame('Descripción actualizada', $workOrder->failure_description);
    }

    public function test_editing_requires_a_failure_description(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $workOrder = WorkOrder::factory()->create(['execution_type' => WorkOrderExecutionType::Interno]);

        $this->actingAs($admin);

        Livewire::test(Show::class, ['workOrder' => $workOrder])
            ->set('edit_failure_description', '')
            ->call('saveEdit')
            ->assertHasErrors(['edit_failure_description' => 'required']);
    }

    public function test_an_operator_cannot_edit_the_report(): void
    {
        $plant = Plant::factory()->create();
        $operator = User::factory()->role(UserRole::Operador)->create(['plant_id' => $plant->id]);
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create();
        $workOrder = WorkOrder::factory()->create([
            'asset_id' => $asset->id,
            'execution_type' => WorkOrderExecutionType::Interno,
        ]);

        $this->actingAs($operator);

        Livewire::test(Show::class, ['workOrder' => $workOrder])
            ->call('saveEdit')
            ->assertForbidden();
    }

    public function test_invoice_number_and_amount_paid_can_be_saved_on_an_external_order(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $workOrder = WorkOrder::factory()->create(['execution_type' => WorkOrderExecutionType::Externo]);

        $this->actingAs($admin);

        Livewire::test(Show::class, ['workOrder' => $workOrder])
            ->assertSee('Factura / requerimiento de compra')
            ->set('invoice_number', 'FAC-00123')
            ->set('amount_paid', '150.50')
            ->call('saveInvoiceInfo')
            ->assertHasNoErrors();

        $workOrder->refresh();
        $this->assertSame('FAC-00123', $workOrder->invoice_number);
        $this->assertEquals(150.50, (float) $workOrder->amount_paid);
    }

    public function test_invoice_section_is_hidden_on_internal_orders(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $workOrder = WorkOrder::factory()->create(['execution_type' => WorkOrderExecutionType::Interno]);

        $this->actingAs($admin);

        Livewire::test(Show::class, ['workOrder' => $workOrder])
            ->assertDontSee('Factura / requerimiento de compra');
    }

    public function test_downloading_the_report_returns_a_pdf(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $workOrder = WorkOrder::factory()->create(['execution_type' => WorkOrderExecutionType::Interno]);

        $this->actingAs($admin);

        Livewire::test(Show::class, ['workOrder' => $workOrder])
            ->call('downloadReport')
            ->assertFileDownloaded("orden-{$workOrder->order_number}.pdf");
    }
}
