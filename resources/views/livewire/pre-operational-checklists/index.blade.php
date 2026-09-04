<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-shield-check text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Listas preoperacionales</h1>
    </div>
</x-slot>

<div>
    <div class="flex items-center justify-between gap-4 mb-4">
        <p class="text-sm text-neutral-400 m-0">Inspecciones de seguridad registradas antes de iniciar turno</p>

        @can('create', \App\Models\PreOperationalChecklist::class)
            <a href="{{ route('pre-operational-checklists.create') }}" wire:navigate class="btn btn-primary">
                <i class="ph ph-plus"></i> Nueva lista
            </a>
        @endcan
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
        <div class="lg:col-span-3 flex flex-col gap-4">
            <div class="card elev-sm p-4">
                <div class="flex flex-wrap items-end gap-3">
                    <select wire:model.live="assetFilter" class="input w-auto">
                        <option value="">Todos los activos</option>
                        @foreach ($assets as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->code }} — {{ $asset->name }}</option>
                        @endforeach
                    </select>

                    @if ($selectedYear)
                        <button wire:click="clearPeriodFilter" class="text-xs text-neutral-400 hover:text-ink">
                            Quitar filtro de {{ $selectedMonth ? \Carbon\Carbon::create()->month($selectedMonth)->translatedFormat('F') : '' }} {{ $selectedYear }} &times;
                        </button>
                    @endif

                    <div class="ml-auto flex flex-wrap items-end gap-3">
                        <div class="field">
                            <label>Desde</label>
                            <input wire:model="exportFrom" type="date" class="input">
                        </div>
                        <div class="field">
                            <label>Hasta</label>
                            <input wire:model="exportTo" type="date" class="input">
                        </div>
                        <button wire:click="exportExcel" class="btn btn-secondary">Descargar Excel</button>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('exportTo')" class="mt-2" />
            </div>

            <div class="card elev-sm p-4">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th><th>Activo</th><th>Resultado</th><th>Acción requerida</th><th>Responsable</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($checklists as $checklist)
                                <tr wire:key="pc-{{ $checklist->id }}" class="cursor-pointer" onclick="window.location='{{ route('pre-operational-checklists.show', $checklist) }}'">
                                    <td class="text-muted">{{ $checklist->inspected_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-ink">{{ $checklist->asset->code }} — {{ $checklist->asset->name }}</td>
                                    <td><span class="tag tag-{{ $checklist->result->tagVariant() }}">{{ $checklist->result->label() }}</span></td>
                                    <td class="text-muted">{{ $checklist->required_action->label() }}</td>
                                    <td class="text-muted">{{ $checklist->performedBy->name }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-8">No hay listas preoperacionales registradas todavía.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $checklists->links() }}</div>
            </div>
        </div>

        <div class="card elev-sm p-4">
            <h3 class="card-title m-0">Filtrar por periodo</h3>

            <div class="mt-3 flex flex-col gap-1">
                @forelse ($periods as $year => $months)
                    <div>
                        <button wire:click="selectYear({{ $year }})" class="w-full flex items-center justify-between text-sm py-1.5 px-2 rounded-md {{ (int) $selectedYear === (int) $year ? 'bg-accent-500/20 text-accent-300' : 'text-neutral-400 hover:text-ink' }}">
                            {{ $year }}
                            <i class="ph ph-caret-right text-xs transition-transform {{ (int) $selectedYear === (int) $year ? 'rotate-90' : '' }}"></i>
                        </button>

                        @if ((int) $selectedYear === (int) $year)
                            <div class="ml-3 mt-1 flex flex-col gap-0.5">
                                @foreach ($months as $month)
                                    <button wire:click="selectMonth({{ $year }}, {{ $month }})" class="w-full text-left text-xs py-1 px-2 rounded-md {{ (int) $selectedMonth === (int) $month ? 'bg-accent-500/20 text-accent-300' : 'text-neutral-500 hover:text-ink' }}">
                                        {{ \Carbon\Carbon::create()->month((int) $month)->translatedFormat('F') }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-neutral-500">Sin registros todavía.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
