<?php

namespace App\Livewire;

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

        $totalAssets = $this->scopedAssets()->count();
        $failuresCount = $this->scopedWorkOrders()
            ->where('type', WorkOrderType::Correctivo)
            ->whereBetween('opened_at', [$start, $end])
            ->count();

        $mtbfHours = $failuresCount > 0
            ? round(($this->period * 24 * max($totalAssets, 1)) / $failuresCount, 1)
            : null;

        $mttrHours = $this->averageRepairHours($start, $end);

        $availability = $mtbfHours && $mttrHours
            ? round($mtbfHours / ($mtbfHours + $mttrHours) * 100, 1)
            : null;

        $preventiveCompliance = $this->preventiveCompliance($start, $end);
        $backlog = $this->backlogByPriority();

        $this->paretoData = $this->paretoOfFailures($start, $end);
        $this->trendData = $this->monthlyTrend();

        return view('livewire.dashboard', [
            'isMultiPlant' => Auth::user()->role->seesAllPlants(),
            'plants' => Auth::user()->role->seesAllPlants() ? Plant::orderBy('name')->get() : collect(),
            'totalAssets' => $totalAssets,
            'failuresCount' => $failuresCount,
            'mtbfHours' => $mtbfHours,
            'mttrHours' => $mttrHours,
            'availability' => $availability,
            'preventiveCompliance' => $preventiveCompliance,
            'backlogTotal' => $backlog->sum(),
            'backlogByPriority' => $backlog,
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

    private function averageRepairHours(Carbon $start, Carbon $end): ?float
    {
        $minutes = $this->scopedWorkOrders()
            ->where('type', WorkOrderType::Correctivo)
            ->where('status', WorkOrderStatus::Completada)
            ->whereBetween('completed_at', [$start, $end])
            ->get()
            ->avg(fn (WorkOrder $wo) => $wo->started_at->diffInMinutes($wo->completed_at));

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
