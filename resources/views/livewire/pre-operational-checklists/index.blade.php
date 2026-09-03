<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Listas preoperacionales</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Inspecciones de seguridad registradas antes de iniciar turno</p>
        </div>

        @can('create', \App\Models\PreOperationalChecklist::class)
            <a href="{{ route('pre-operational-checklists.create') }}" wire:navigate class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Nueva lista
            </a>
        @endcan
    </div>

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
        <div class="lg:col-span-3 space-y-4">
            <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-4">
                <div class="flex flex-wrap items-end gap-3">
                    <select wire:model.live="assetFilter" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                        <option value="">Todos los activos</option>
                        @foreach ($assets as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->code }} — {{ $asset->name }}</option>
                        @endforeach
                    </select>

                    @if ($selectedYear)
                        <button wire:click="clearPeriodFilter" class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            Quitar filtro de {{ $selectedMonth ? \Carbon\Carbon::create()->month($selectedMonth)->translatedFormat('F') : '' }} {{ $selectedYear }} &times;
                        </button>
                    @endif

                    <div class="ml-auto flex flex-wrap items-end gap-3">
                        <div>
                            <x-input-label for="exportFrom" value="Desde" />
                            <x-text-input wire:model="exportFrom" id="exportFrom" type="date" class="mt-1 block text-sm" />
                        </div>
                        <div>
                            <x-input-label for="exportTo" value="Hasta" />
                            <x-text-input wire:model="exportTo" id="exportTo" type="date" class="mt-1 block text-sm" />
                        </div>
                        <x-secondary-button wire:click="exportExcel">Descargar Excel</x-secondary-button>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('exportTo')" class="mt-2" />
            </div>

            <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/40">
                            <tr class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                <th class="px-4 py-3">Fecha</th>
                                <th class="px-4 py-3">Activo</th>
                                <th class="px-4 py-3">Resultado</th>
                                <th class="px-4 py-3">Acción requerida</th>
                                <th class="px-4 py-3">Responsable</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse ($checklists as $checklist)
                                <tr wire:key="pc-{{ $checklist->id }}" class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900/30" onclick="window.location='{{ route('pre-operational-checklists.show', $checklist) }}'">
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $checklist->inspected_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $checklist->asset->code }} — {{ $checklist->asset->name }}</td>
                                    <td class="px-4 py-3"><x-badge :color="$checklist->result->color()">{{ $checklist->result->label() }}</x-badge></td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $checklist->required_action->label() }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $checklist->performedBy->name }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay listas preoperacionales registradas todavía.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4">{{ $checklists->links() }}</div>
            </div>
        </div>

        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-4">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Filtrar por periodo</h3>

            <div class="mt-3 space-y-1">
                @forelse ($periods as $year => $months)
                    <div>
                        <button wire:click="selectYear({{ $year }})" class="w-full flex items-center justify-between text-sm py-1.5 px-2 rounded-lg {{ (int) $selectedYear === (int) $year ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900/40' }}">
                            {{ $year }}
                            <svg class="h-4 w-4 transition-transform {{ (int) $selectedYear === (int) $year ? 'rotate-90' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </button>

                        @if ((int) $selectedYear === (int) $year)
                            <div class="ml-3 mt-1 space-y-0.5">
                                @foreach ($months as $month)
                                    <button wire:click="selectMonth({{ $year }}, {{ $month }})" class="w-full text-left text-xs py-1 px-2 rounded-lg {{ (int) $selectedMonth === (int) $month ? 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 font-medium' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-900/40' }}">
                                        {{ \Carbon\Carbon::create()->month((int) $month)->translatedFormat('F') }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-gray-400">Sin registros todavía.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
