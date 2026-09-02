<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Análisis de mantenimiento</h1>

        <div class="flex gap-3">
            @if ($isMultiPlant)
                <select wire:model.live="plantFilter" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm text-sm">
                    <option value="">Todas las plantas</option>
                    @foreach ($plants as $plant)
                        <option value="{{ $plant->id }}">{{ $plant->name }}</option>
                    @endforeach
                </select>
            @endif

            <select wire:model.live="period" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm text-sm">
                <option value="30">Últimos 30 días</option>
                <option value="90">Últimos 90 días</option>
                <option value="180">Últimos 180 días</option>
                <option value="365">Último año</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-5">
            <p class="text-xs text-gray-400">MTBF</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $mtbfHours ?? '—' }}<span class="text-sm font-normal text-gray-400"> h</span></p>
            <p class="text-xs text-gray-400 mt-1">Tiempo medio entre fallas</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-5">
            <p class="text-xs text-gray-400">MTTR</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $mttrHours ?? '—' }}<span class="text-sm font-normal text-gray-400"> h</span></p>
            <p class="text-xs text-gray-400 mt-1">Tiempo medio de reparación</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-5">
            <p class="text-xs text-gray-400">Disponibilidad</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $availability ?? '—' }}<span class="text-sm font-normal text-gray-400">%</span></p>
            <p class="text-xs text-gray-400 mt-1">Basada en MTBF / MTTR</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-5">
            <p class="text-xs text-gray-400">Cumplimiento preventivo</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $preventiveCompliance ?? '—' }}<span class="text-sm font-normal text-gray-400">%</span></p>
            <p class="text-xs text-gray-400 mt-1">Preventivas completadas / programadas</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-5 lg:col-span-1">
            <p class="text-xs text-gray-400">Backlog de órdenes abiertas</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $backlogTotal }}</p>

            <div class="mt-3 space-y-1.5">
                @forelse ($backlogByPriority as $priority => $count)
                    <div class="flex items-center justify-between text-sm">
                        <x-badge :color="\App\Enums\WorkOrderPriority::from($priority)->color()">{{ \App\Enums\WorkOrderPriority::from($priority)->label() }}</x-badge>
                        <span class="text-gray-600 dark:text-gray-300">{{ $count }}</span>
                    </div>
                @empty
                    <p class="text-xs text-gray-400">Sin órdenes pendientes.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-5 lg:col-span-2">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pareto de fallas — equipos con más correctivos</p>
            <div
                wire:ignore
                x-data="paretoChart(@entangle('paretoData'))"
            >
                <div x-ref="chart"></div>
            </div>
        </div>
    </div>

    <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-5">
        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tendencia correctivo vs. preventivo (últimos 6 meses)</p>
        <div
            wire:ignore
            x-data="trendChart(@entangle('trendData'))"
        >
            <div x-ref="chart"></div>
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
                chart: { type: 'bar', height: 280, toolbar: { show: false } },
                series: [{ name: 'Fallas', data: this.data.values }],
                xaxis: { categories: this.data.labels },
                colors: ['#6366f1'],
                plotOptions: { bar: { borderRadius: 4 } },
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
                chart: { type: 'line', height: 280, toolbar: { show: false } },
                series: [
                    { name: 'Correctivo', data: this.data.correctivo },
                    { name: 'Preventivo', data: this.data.preventivo },
                ],
                xaxis: { categories: this.data.labels },
                colors: ['#ef4444', '#22c55e'],
                stroke: { curve: 'smooth', width: 3 },
            };
        },
    }));
</script>
@endscript
