<?php

namespace App\Livewire;

use App\Enums\WorkOrderPriority;
use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderType;
use App\Models\Asset;
use App\Models\Plant;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public int $period = 90;

    public ?int $plantFilter = null;

    /** @var array{labels: array<int, string>, values: array<int, int>} */
    public array $paretoData = ['labels' => [], 'values' => []];

    /** @var array{labels: array<int, string>, correctivo: array<int, int>, preventivo: array<int, int>} */
    public array $trendData = ['labels' => [], 'correctivo' => [], 'preventivo' => []];

    public function render()
    {
        $start = Carbon::now()->subDays($this->period)->startOfDay();
        $end = Carbon::now()->endOfDay();
        $previousEnd = $start->copy()->subSecond();
        $previousStart = $previousEnd->copy()->subDays($this->period)->startOfDay();

        $current = $this->periodMetrics($start, $end);
        $previous = $this->periodMetrics($previousStart, $previousEnd);

        $backlog = $this->backlogByPriority();
        $backlogTotal = $backlog->sum();
        $backlogInProgressOrWaiting = $this->scopedWorkOrders()
            ->whereIn('status', [WorkOrderStatus::EnProgreso, WorkOrderStatus::EnEspera])
            ->count();

        $this->paretoData = $this->paretoOfFailures($start, $end);
        $this->trendData = $this->monthlyTrend();

        return view('livewire.dashboard', [
            'isMultiPlant' => Auth::user()->role->seesAllPlants(),
            'plants' => Auth::user()->role->seesAllPlants() ? Plant::orderBy('name')->get() : collect(),
            'mtbfHours' => $current['mtbf'],
            'mtbfDelta' => $this->percentDelta($current['mtbf'], $previous['mtbf']),
            'mttrHours' => $current['mttr'],
            'mttrDelta' => $this->percentDelta($current['mttr'], $previous['mttr']),
            'availability' => $current['availability'],
            'availabilityDelta' => $this->percentDelta($current['availability'], $previous['availability']),
            'preventiveCompliance' => $current['preventiveCompliance'],
            'preventiveComplianceDelta' => $this->percentDelta($current['preventiveCompliance'], $previous['preventiveCompliance']),
            'backlogTotal' => $backlogTotal,
            'backlogByPriority' => $backlog,
            'backlogRingPct' => $backlogTotal > 0 ? (int) round($backlogInProgressOrWaiting / $backlogTotal * 100) : 0,
            'topAssets' => $this->topAssetsWithFailures($start, $end),
            'attentionWorkOrder' => $this->attentionWorkOrder(),
        ]);
    }

    private function scopedWorkOrders(): Builder
    {
        return WorkOrder::query()->when(
            $this->plantFilter,
            fn (Builder $q) => $q->whereHas('asset.area', fn (Builder $q2) => $q2->where('plant_id', $this->plantFilter))
        );
    }

    private function scopedAssets(): Builder
    {
        return Asset::query()->when(
            $this->plantFilter,
            fn (Builder $q) => $q->whereHas('area', fn (Builder $q2) => $q2->where('plant_id', $this->plantFilter))
        );
    }

    /**
     * @return array{mtbf: ?float, mttr: ?float, availability: ?float, preventiveCompliance: ?float}
     */
    private function periodMetrics(Carbon $start, Carbon $end): array
    {
        $totalAssets = $this->scopedAssets()->count();
        $failuresCount = $this->scopedWorkOrders()
            ->where('type', WorkOrderType::Correctivo)
            ->whereBetween('opened_at', [$start, $end])
            ->count();

        $periodDays = max($start->diffInDays($end), 1);

        $mtbf = $failuresCount > 0
            ? round(($periodDays * 24 * max($totalAssets, 1)) / $failuresCount, 1)
            : null;

        $mttr = $this->averageRepairHours($start, $end);

        $availability = $mtbf && $mttr
            ? round($mtbf / ($mtbf + $mttr) * 100, 1)
            : null;

        return [
            'mtbf' => $mtbf,
            'mttr' => $mttr,
            'availability' => $availability,
            'preventiveCompliance' => $this->preventiveCompliance($start, $end),
        ];
    }

    private function percentDelta(?float $current, ?float $previous): ?float
    {
        if ($current === null || $previous === null || $previous == 0.0) {
            return null;
        }

        return round(($current - $previous) / $previous * 100, 1);
    }

    private function averageRepairHours(Carbon $start, Carbon $end): ?float
    {
        $minutes = $this->scopedWorkOrders()
            ->where('type', WorkOrderType::Correctivo)
            ->where('status', WorkOrderStatus::Completada)
            ->whereBetween('completed_at', [$start, $end])
            ->get()
            ->avg(fn (WorkOrder $wo) => $wo->repair_minutes);

        return $minutes ? round($minutes / 60, 1) : null;
    }

    private function preventiveCompliance(Carbon $start, Carbon $end): ?float
    {
        $scheduled = $this->scopedWorkOrders()
            ->where('type', WorkOrderType::Preventivo)
            ->whereBetween('opened_at', [$start, $end])
            ->count();

        if ($scheduled === 0) {
            return null;
        }

        $completed = $this->scopedWorkOrders()
            ->where('type', WorkOrderType::Preventivo)
            ->where('status', WorkOrderStatus::Completada)
            ->whereBetween('opened_at', [$start, $end])
            ->count();

        return round($completed / $scheduled * 100, 1);
    }

    private function backlogByPriority(): Collection
    {
        return $this->scopedWorkOrders()
            ->whereIn('status', [WorkOrderStatus::Abierta, WorkOrderStatus::EnProgreso, WorkOrderStatus::EnEspera])
            ->get()
            ->countBy(fn (WorkOrder $wo) => $wo->priority->value);
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    private function paretoOfFailures(Carbon $start, Carbon $end): array
    {
        $rows = $this->scopedWorkOrders()
            ->where('type', WorkOrderType::Correctivo)
            ->whereBetween('opened_at', [$start, $end])
            ->select('asset_id', DB::raw('count(*) as total'))
            ->groupBy('asset_id')
            ->orderByDesc('total')
            ->limit(8)
            ->with('asset')
            ->get();

        return [
            'labels' => $rows->map(fn ($row) => $row->asset->code)->all(),
            'values' => $rows->pluck('total')->all(),
        ];
    }

    /**
     * @return Collection<int, array{name: string, code: string, technician: string, fails: int}>
     */
    private function topAssetsWithFailures(Carbon $start, Carbon $end): Collection
    {
        $rows = $this->scopedWorkOrders()
            ->where('type', WorkOrderType::Correctivo)
            ->whereBetween('opened_at', [$start, $end])
            ->select('asset_id', DB::raw('count(*) as total'))
            ->groupBy('asset_id')
            ->orderByDesc('total')
            ->limit(3)
            ->with(['asset.workOrders' => fn ($q) => $q
                ->where('type', WorkOrderType::Correctivo)
                ->whereBetween('opened_at', [$start, $end])
                ->whereNotNull('assigned_to')
                ->latest('opened_at')
                ->with('assignedTo'),
            ])
            ->get();

        return $rows->map(fn ($row) => [
            'name' => $row->asset->name,
            'code' => $row->asset->code,
            'technician' => $row->asset->workOrders->first()?->assignedTo?->name ?? '—',
            'fails' => $row->total,
        ]);
    }

    /**
     * The oldest still-open order at the highest priority tier that currently has any —
     * checked as separate queries (Urgente, then Alta, ...) rather than a single
     * `ORDER BY FIELD(...)` so this stays portable to the SQLite test database.
     * WorkOrderPriority::cases() is declared Baja, Media, Alta, Urgente, so walking
     * the reversed array checks highest-priority first.
     */
    private function attentionWorkOrder(): ?WorkOrder
    {
        foreach (array_reverse(WorkOrderPriority::cases()) as $priority) {
            $workOrder = $this->scopedWorkOrders()
                ->whereIn('status', [WorkOrderStatus::Abierta, WorkOrderStatus::EnProgreso, WorkOrderStatus::EnEspera])
                ->where('priority', $priority)
                ->oldest('opened_at')
                ->with('asset')
                ->first();

            if ($workOrder) {
                return $workOrder;
            }
        }

        return null;
    }

    /**
     * @return array{labels: array<int, string>, correctivo: array<int, int>, preventivo: array<int, int>}
     */
    private function monthlyTrend(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->subMonths($i)->startOfMonth());

        $byMonthAndType = $this->scopedWorkOrders()
            ->where('opened_at', '>=', $months->first())
            ->get(['opened_at', 'type'])
            ->groupBy(fn (WorkOrder $wo) => $wo->opened_at->format('Y-m'));

        return [
            'labels' => $months->map(fn (Carbon $m) => $m->translatedFormat('M Y'))->all(),
            'correctivo' => $months->map(
                fn (Carbon $m) => $byMonthAndType->get($m->format('Y-m'), collect())
                    ->where('type', WorkOrderType::Correctivo)->count()
            )->all(),
            'preventivo' => $months->map(
                fn (Carbon $m) => $byMonthAndType->get($m->format('Y-m'), collect())
                    ->where('type', WorkOrderType::Preventivo)->count()
            )->all(),
        ];
    }
}
