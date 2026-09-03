<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-squares-four text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Análisis de mantenimiento</h1>
    </div>

    <div class="flex items-center gap-3">
        @if ($isMultiPlant)
            <select wire:model.live="plantFilter" class="input w-auto">
                <option value="">Todas las plantas</option>
                @foreach ($plants as $plant)
                    <option value="{{ $plant->id }}">{{ $plant->name }}</option>
                @endforeach
            </select>
        @endif

        <select wire:model.live="period" class="input w-auto">
            <option value="30">Últimos 30 días</option>
            <option value="90">Últimos 90 días</option>
            <option value="180">Últimos 180 días</option>
            <option value="365">Último año</option>
        </select>
    </div>
</x-slot>

<div class="space-y-4">
    @php
        $priorityTagClass = fn ($priority) => match ($priority) {
            \App\Enums\WorkOrderPriority::Urgente, \App\Enums\WorkOrderPriority::Alta => 'tag-accent',
            \App\Enums\WorkOrderPriority::Media => 'tag-outline',
            \App\Enums\WorkOrderPriority::Baja => 'tag-neutral',
        };
        $kpis = [
            ['icon' => 'ph-gauge', 'label' => 'MTBF', 'value' => $mtbfHours, 'unit' => 'h', 'sub' => 'Tiempo medio entre fallas', 'delta' => $mtbfDelta, 'invert' => false],
            ['icon' => 'ph-wrench', 'label' => 'MTTR', 'value' => $mttrHours, 'unit' => 'h', 'sub' => 'Tiempo medio de reparación', 'delta' => $mttrDelta, 'invert' => true],
            ['icon' => 'ph-shield-check', 'label' => 'Disponibilidad', 'value' => $availability, 'unit' => '%', 'sub' => 'Basada en MTBF / MTTR', 'delta' => $availabilityDelta, 'invert' => false],
            ['icon' => 'ph-calendar-check', 'label' => 'Cumplimiento preventivo', 'value' => $preventiveCompliance, 'unit' => '%', 'sub' => 'Preventivas completadas / programadas', 'delta' => $preventiveComplianceDelta, 'invert' => false],
        ];
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($kpis as $kpi)
            <div class="card elev-sm p-4">
                <div class="w-8 h-8 rounded bg-accent-500/20 flex items-center justify-center text-accent-300">
                    <i class="ph {{ $kpi['icon'] }} text-base"></i>
                </div>
                <div class="mt-2 font-medium text-2xl text-ink">
                    {{ $kpi['value'] ?? '—' }}<span class="text-sm font-normal text-neutral-400"> {{ $kpi['unit'] }}</span>
                </div>
                <div class="text-xs text-neutral-400">{{ $kpi['label'] }}</div>
                <div class="text-xs text-neutral-400">{{ $kpi['sub'] }}</div>
                @if ($kpi['delta'] !== null)
                    @php $isGood = $kpi['invert'] ? $kpi['delta'] <= 0 : $kpi['delta'] >= 0; @endphp
                    <div class="flex items-center gap-1 text-xs mt-1 {{ $isGood ? 'text-accent-300' : 'text-neutral-400' }}">
                        <i class="ph {{ $kpi['delta'] >= 0 ? 'ph-arrow-up-right' : 'ph-arrow-down-right' }}"></i>
                        {{ $kpi['delta'] > 0 ? '+' : '' }}{{ $kpi['delta'] }}% <span class="text-neutral-500">vs periodo anterior</span>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-stretch">
        <div class="card elev-sm p-4 bg-section">
            <p class="text-xs text-neutral-300">Backlog de órdenes abiertas</p>
            <p class="mt-1 font-medium text-2xl text-ink">{{ $backlogTotal }}</p>

            <div class="mt-4 flex items-center gap-4">
                <div class="w-[76px] h-[76px] rounded-full flex items-center justify-center shrink-0"
                    style="background: conic-gradient(var(--color-accent-400) 0% {{ $backlogRingPct }}%, var(--color-neutral-700) {{ $backlogRingPct }}% 100%);">
                    <div class="w-14 h-14 rounded-full bg-section flex items-center justify-center text-sm font-medium text-ink">
                        {{ $backlogRingPct }}%
                    </div>
                </div>
                <p class="text-xs text-neutral-400">en progreso o en espera del total abierto</p>
            </div>

            <div class="mt-3 pt-3 border-t border-neutral-800 space-y-1.5">
                @forelse ($backlogByPriority as $priority => $count)
                    <div class="flex items-center justify-between text-sm">
                        <span class="tag {{ $priorityTagClass(\App\Enums\WorkOrderPriority::from($priority)) }}">{{ \App\Enums\WorkOrderPriority::from($priority)->label() }}</span>
                        <span class="text-neutral-300">{{ $count }}</span>
                    </div>
                @empty
                    <p class="text-xs text-neutral-400">Sin órdenes pendientes.</p>
                @endforelse
            </div>
        </div>

        <div class="card elev-sm p-4 lg:col-span-2">
            <p class="card-title mb-2">Correctivo vs. preventivo — últimos 6 meses</p>
            <div wire:ignore x-data="trendChart(@entangle('trendData'))">
                <div x-ref="chart"></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-stretch">
        <div class="card elev-sm p-4 lg:col-span-2">
            <p class="card-title mb-2">Pareto de fallas — equipos con más correctivos</p>
            <div wire:ignore x-data="paretoChart(@entangle('paretoData'))">
                <div x-ref="chart"></div>
            </div>
        </div>

        <div class="card elev-sm p-4">
            <div class="flex items-center gap-2 mb-3">
                <i class="ph ph-warning text-accent-300"></i>
                <p class="card-title m-0">Atención</p>
            </div>
            @if ($attentionWorkOrder)
                <div class="border border-neutral-800 rounded-md p-3 flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs text-ink">{{ $attentionWorkOrder->order_number }} · {{ $attentionWorkOrder->asset->code }}</span>
                        <span class="tag {{ $priorityTagClass($attentionWorkOrder->priority) }}">{{ $attentionWorkOrder->priority->label() }}</span>
                    </div>
                    <p class="m-0 text-xs text-neutral-400">{{ $attentionWorkOrder->failure_description ?? $attentionWorkOrder->type->label() }} — abierta {{ $attentionWorkOrder->opened_at->diffForHumans() }}</p>
                    <a href="{{ route('work-orders.show', $attentionWorkOrder) }}" wire:navigate class="text-xs text-accent-300">Reasignar técnico →</a>
                </div>
            @else
                <p class="text-xs text-neutral-400">Sin alertas activas.</p>
            @endif
        </div>
    </div>

    <div class="card elev-sm p-4">
        <p class="card-title mb-3">Top equipos con fallas</p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @forelse ($topAssets as $asset)
                <div class="border border-neutral-800 rounded-md p-3 flex flex-col gap-2">
                    <div class="flex items-center gap-2">
                        <div class="w-[30px] h-[30px] rounded bg-neutral-800 flex items-center justify-center text-accent-300">
                            <i class="ph ph-gear-six"></i>
                        </div>
                        <div>
                            <div class="text-sm text-ink">{{ $asset['name'] }}</div>
                            <div class="text-[11px] font-mono text-neutral-500">{{ $asset['code'] }}</div>
                        </div>
                    </div>
                    <div class="flex justify-between text-xs text-neutral-400">
                        <span>{{ $asset['technician'] }}</span>
                        <span>{{ $asset['fails'] }} fallas</span>
                    </div>
                </div>
            @empty
                <p class="text-xs text-neutral-400 col-span-full">Sin correctivos en el periodo seleccionado.</p>
            @endforelse
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('paretoChart', (data) => ({
        chart: null,
        data,
        init() {
            this.chart = new ApexCharts(this.$refs.chart, this.options());
            this.chart.render();
            this.$watch('data', () => {
                this.chart.updateOptions(this.options());
            });
        },
        options() {
            return {
                chart: { type: 'bar', height: 280, toolbar: { show: false }, background: 'transparent' },
                series: [{ name: 'Fallas', data: this.data.values }],
                xaxis: { categories: this.data.labels, labels: { style: { colors: '#9397ab' } } },
                yaxis: { labels: { style: { colors: '#9397ab' } } },
                colors: ['#968ae0'],
                plotOptions: { bar: { borderRadius: 6 } },
                grid: { borderColor: '#3f424d' },
                theme: { mode: 'dark' },
            };
        },
    }));

    Alpine.data('trendChart', (data) => ({
        chart: null,
        data,
        init() {
            this.chart = new ApexCharts(this.$refs.chart, this.options());
            this.chart.render();
            this.$watch('data', () => {
                this.chart.updateOptions(this.options());
            });
        },
        options() {
            return {
                chart: { type: 'line', height: 280, toolbar: { show: false }, background: 'transparent' },
                series: [
                    { name: 'Correctivo', data: this.data.correctivo },
                    { name: 'Preventivo', data: this.data.preventivo },
                ],
                xaxis: { categories: this.data.labels, labels: { style: { colors: '#9397ab' } } },
                yaxis: { labels: { style: { colors: '#9397ab' } } },
                colors: ['#b5abfc', '#75798c'],
                stroke: { curve: 'smooth', width: [2.5, 2], dashArray: [0, 5] },
                fill: { type: ['gradient', 'solid'], gradient: { opacityFrom: 0.35, opacityTo: 0 } },
                grid: { borderColor: '#3f424d' },
                theme: { mode: 'dark' },
            };
        },
    }));
</script>
@endscript
