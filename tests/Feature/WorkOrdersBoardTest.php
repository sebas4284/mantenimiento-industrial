<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\WorkOrderPriority;
use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderType;
use App\Livewire\WorkOrders\Index;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class WorkOrdersBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_board_excludes_completed_and_cancelled_orders(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        WorkOrder::factory()->create(['status' => WorkOrderStatus::Completada]);
        WorkOrder::factory()->create(['status' => WorkOrderStatus::Cancelada]);
        $open = WorkOrder::factory()->create(['status' => WorkOrderStatus::Abierta]);

        $this->actingAs($admin);

        $component = Livewire::test(Index::class);

        $columnValues = collect($component->viewData('columns'))->map(fn ($c) => $c->value)->all();
        $this->assertEqualsCanonicalizing(['abierta', 'en_progreso', 'en_espera'], $columnValues);

        $board = $component->viewData('workOrdersByStatus');
        $this->assertFalse($board->has('completada'));
        $this->assertFalse($board->has('cancelada'));
        $this->assertTrue($board->get('abierta')->contains('id', $open->id));
    }

    public function test_urgent_correctivo_orders_are_sorted_above_older_low_priority_ones(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();

        $old = WorkOrder::factory()->create([
            'status' => WorkOrderStatus::Abierta,
            'priority' => WorkOrderPriority::Baja,
            'type' => WorkOrderType::Preventivo,
            'opened_at' => Carbon::now()->subDays(10),
        ]);

        $urgent = WorkOrder::factory()->create([
            'status' => WorkOrderStatus::Abierta,
            'priority' => WorkOrderPriority::Urgente,
            'type' => WorkOrderType::Correctivo,
            'opened_at' => Carbon::now()->subHour(),
        ]);

        $this->actingAs($admin);

        $abierta = Livewire::test(Index::class)->viewData('workOrdersByStatus')->get('abierta');

        $this->assertSame($urgent->id, $abierta->first()->id);
        $this->assertSame($old->id, $abierta->last()->id);
    }

    public function test_within_the_same_priority_and_type_the_oldest_order_comes_first(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();

        $newer = WorkOrder::factory()->create([
            'status' => WorkOrderStatus::Abierta,
            'priority' => WorkOrderPriority::Media,
            'type' => WorkOrderType::Correctivo,
            'opened_at' => Carbon::now()->subDay(),
        ]);

        $older = WorkOrder::factory()->create([
            'status' => WorkOrderStatus::Abierta,
            'priority' => WorkOrderPriority::Media,
            'type' => WorkOrderType::Correctivo,
            'opened_at' => Carbon::now()->subDays(5),
        ]);

        $this->actingAs($admin);

        $abierta = Livewire::test(Index::class)->viewData('workOrdersByStatus')->get('abierta');

        $this->assertSame($older->id, $abierta->first()->id);
        $this->assertSame($newer->id, $abierta->last()->id);
    }

    public function test_historial_only_lists_completed_and_cancelled_orders_and_respects_date_range(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();

        $inRange = WorkOrder::factory()->create([
            'status' => WorkOrderStatus::Completada,
            'opened_at' => Carbon::parse('2026-06-15 10:00:00'),
        ]);

        $outOfRange = WorkOrder::factory()->create([
            'status' => WorkOrderStatus::Cancelada,
            'opened_at' => Carbon::parse('2026-01-01 10:00:00'),
        ]);

        WorkOrder::factory()->create(['status' => WorkOrderStatus::Abierta]);

        $this->actingAs($admin);

        $historial = Livewire::test(Index::class)
            ->set('dateFrom', '2026-06-01')
            ->set('dateTo', '2026-06-30')
            ->viewData('historial');

        $ids = collect($historial->items())->pluck('id');
        $this->assertTrue($ids->contains($inRange->id));
        $this->assertFalse($ids->contains($outOfRange->id));
    }

    public function test_the_search_box_filters_both_the_board_and_the_historial(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();

        $match = WorkOrder::factory()->create([
            'status' => WorkOrderStatus::Abierta,
            'failure_description' => 'Fuga de aceite en el compresor principal',
        ]);

        $completedMatch = WorkOrder::factory()->create([
            'status' => WorkOrderStatus::Completada,
            'failure_description' => 'Fuga de aceite detectada en banda',
        ]);

        $noMatch = WorkOrder::factory()->create([
            'status' => WorkOrderStatus::Abierta,
            'failure_description' => 'Ruido anormal en el motor',
        ]);

        $this->actingAs($admin);

        $component = Livewire::test(Index::class)->set('search', 'fuga de aceite');

        $abierta = $component->viewData('workOrdersByStatus')->get('abierta', collect());
        $this->assertTrue($abierta->contains('id', $match->id));
        $this->assertFalse($abierta->contains('id', $noMatch->id));

        $historialIds = collect($component->viewData('historial')->items())->pluck('id');
        $this->assertTrue($historialIds->contains($completedMatch->id));
    }
}
