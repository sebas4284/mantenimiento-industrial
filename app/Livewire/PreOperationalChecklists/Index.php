<?php

namespace App\Livewire\PreOperationalChecklists;

use App\Exports\PreOperationalChecklistExport;
use App\Models\Asset;
use App\Models\PreOperationalChecklist;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public ?int $assetFilter = null;

    public ?int $selectedYear = null;

    public ?int $selectedMonth = null;

    public string $exportFrom = '';

    public string $exportTo = '';

    public function mount(): void
    {
        $this->assetFilter = request()->integer('asset') ?: null;
    }

    public function selectYear(int $year): void
    {
        $this->selectedYear = $this->selectedYear === $year ? null : $year;
        $this->selectedMonth = null;
        $this->resetPage();
    }

    public function selectMonth(int $year, int $month): void
    {
        $this->selectedYear = $year;
        $this->selectedMonth = $month;
        $this->resetPage();
    }

    public function clearPeriodFilter(): void
    {
        $this->selectedYear = null;
        $this->selectedMonth = null;
        $this->resetPage();
    }

    public function exportExcel()
    {
        $validated = $this->validate([
            'exportFrom' => ['nullable', 'date'],
            'exportTo' => ['nullable', 'date', 'after_or_equal:exportFrom'],
        ]);

        $from = $validated['exportFrom'] ? Carbon::parse($validated['exportFrom'])->startOfDay() : null;
        $to = $validated['exportTo'] ? Carbon::parse($validated['exportTo'])->endOfDay() : null;
        $asset = $this->assetFilter ? Asset::find($this->assetFilter) : null;

        return Excel::download(
            new PreOperationalChecklistExport($asset, $from, $to),
            'listas-preoperacionales.xlsx',
        );
    }

    public function render()
    {
        $checklists = PreOperationalChecklist::query()
            ->with(['asset.area.plant', 'performedBy'])
            ->when($this->assetFilter, fn ($q) => $q->where('asset_id', $this->assetFilter))
            ->when($this->selectedYear, fn ($q) => $q->whereYear('inspected_at', $this->selectedYear))
            ->when($this->selectedMonth, fn ($q) => $q->whereMonth('inspected_at', $this->selectedMonth))
            ->orderByDesc('inspected_at')
            ->paginate(15);

        $periods = PreOperationalChecklist::query()
            ->select('id', 'inspected_at')
            ->get()
            ->groupBy(fn ($c) => $c->inspected_at->format('Y'))
            ->map(fn ($year) => $year->groupBy(fn ($c) => $c->inspected_at->format('n'))->keys()->sortDesc())
            ->sortKeysDesc();

        return view('livewire.pre-operational-checklists.index', [
            'checklists' => $checklists,
            'periods' => $periods,
            'assets' => Asset::orderBy('name')->get(),
        ]);
    }
}
