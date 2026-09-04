<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-gear-six text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">{{ $asset->name }}</h1>
    </div>
</x-slot>

<div class="space-y-4">
    @php
        $statusTagClass = fn ($status) => match ($status) {
            \App\Enums\WorkOrderStatus::Completada => 'tag-neutral',
            \App\Enums\WorkOrderStatus::Cancelada => 'tag-outline',
            default => 'tag-accent',
        };
        $priorityTagClass = fn ($priority) => match ($priority) {
            \App\Enums\WorkOrderPriority::Urgente, \App\Enums\WorkOrderPriority::Alta => 'tag-accent',
            \App\Enums\WorkOrderPriority::Media => 'tag-outline',
            \App\Enums\WorkOrderPriority::Baja => 'tag-neutral',
        };
        $assetStatusTagClass = fn ($status) => match ($status) {
            \App\Enums\AssetStatus::Operativo => 'tag-accent',
            \App\Enums\AssetStatus::Mantenimiento => 'tag-outline',
            \App\Enums\AssetStatus::Inactivo => 'tag-neutral',
        };
        $preopResultTagClass = fn ($result) => match ($result) {
            \App\Enums\PreOperationalResult::Apto => 'tag-neutral',
            \App\Enums\PreOperationalResult::NoApto => 'tag-accent',
        };
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('assets.index') }}" wire:navigate class="text-sm text-accent-300">&larr; Volver a activos</a>

        <div class="flex gap-3">
            @can('update', $asset)
                <button wire:click="openEditModal" class="btn btn-secondary">
                    <i class="ph ph-pencil-simple"></i> Editar
                </button>
            @endcan
            <button wire:click="openHistory" class="btn btn-secondary">
                <i class="ph ph-clock-counter-clockwise"></i> Ver historial
            </button>
        </div>
    </div>

    <div class="card elev-sm p-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
            <div class="sm:col-span-1">
                @if ($asset->photo_path)
                    <img src="{{ Storage::disk('public')->url($asset->photo_path) }}" alt="{{ $asset->name }}"
                        class="w-full aspect-square object-cover rounded-md border border-neutral-800">
                @else
                    <div class="w-full aspect-square rounded-md bg-neutral-900 border border-neutral-800 flex items-center justify-center text-neutral-600">
                        <i class="ph ph-image text-4xl"></i>
                    </div>
                @endif
            </div>

            <div class="sm:col-span-2">
                <h2 class="m-0 text-xl text-ink">{{ $asset->name }}</h2>

                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="tag {{ $assetStatusTagClass($displayStatus) }}">{{ $displayStatus->label() }}</span>
                    <span class="tag tag-neutral">Criticidad {{ $asset->criticality->value }}</span>
                </div>

                <dl class="mt-6 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-neutral-500">Código</dt>
                        <dd class="text-ink">{{ $asset->code }}</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-500">Planta</dt>
                        <dd class="text-ink">{{ $asset->area->plant->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-500">Área</dt>
                        <dd class="text-ink">{{ $asset->area->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-500">Fabricante</dt>
                        <dd class="text-ink">{{ $asset->manufacturer ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-500">Modelo</dt>
                        <dd class="text-ink">{{ $asset->model ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-500">Número de serie</dt>
                        <dd class="text-ink">{{ $asset->serial_number ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="sm:col-span-1">
                @if ($asset->qr_code_path)
                    <img src="{{ Storage::disk('public')->url($asset->qr_code_path) }}" alt="QR {{ $asset->code }}"
                        class="w-full aspect-square object-contain rounded-md bg-white p-2">
                    <a href="{{ Storage::disk('public')->url($asset->qr_code_path) }}" target="_blank" class="mt-2 block text-center text-xs text-accent-300">Ver / imprimir QR</a>
                @else
                    <div class="w-full aspect-square rounded-md bg-neutral-900 border border-neutral-800 flex items-center justify-center text-xs text-neutral-500">
                        Sin QR
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="card elev-sm p-4 text-center">
            <p class="text-xs text-neutral-400 m-0">MTBF</p>
            <p class="mt-1 font-medium text-xl text-ink">{{ $mtbfHours !== null ? "{$mtbfHours} h" : '—' }}</p>
        </div>
        <div class="card elev-sm p-4 text-center">
            <p class="text-xs text-neutral-400 m-0">MTTR</p>
            <p class="mt-1 font-medium text-xl text-ink">{{ $mttrHours !== null ? "{$mttrHours} h" : '—' }}</p>
        </div>
        <div class="card elev-sm p-4 text-center">
            <p class="text-xs text-neutral-400 m-0">Disponibilidad</p>
            <p class="mt-1 font-medium text-xl text-ink">{{ $availabilityPercent !== null ? "{$availabilityPercent}%" : '—' }}</p>
        </div>
        <div class="card elev-sm p-4 text-center">
            <p class="text-xs text-neutral-400 m-0">% Correctivo</p>
            <p class="mt-1 font-medium text-xl text-ink">{{ $correctivoPercent !== null ? "{$correctivoPercent}%" : '—' }}</p>
        </div>
        <div class="card elev-sm p-4 text-center">
            <p class="text-xs text-neutral-400 m-0">% Preventivo</p>
            <p class="mt-1 font-medium text-xl text-ink">{{ $preventivoPercent !== null ? "{$preventivoPercent}%" : '—' }}</p>
        </div>
    </div>

    <div class="card elev-sm p-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="card-title m-0">Historial de mantenimiento</h2>
                <p class="text-xs text-neutral-400 mt-0.5">Descarga un Excel con los correctivos y preventivos de este activo.</p>
            </div>

            <form wire:submit="exportHistory" class="flex flex-wrap items-end gap-3">
                <div class="field">
                    <label>Desde</label>
                    <input wire:model="exportFrom" type="date" class="input">
                </div>
                <div class="field">
                    <label>Hasta</label>
                    <input wire:model="exportTo" type="date" class="input">
                </div>
                <button type="submit" class="btn btn-secondary">
                    <i class="ph ph-download-simple"></i> Descargar Excel
                </button>
            </form>
        </div>
        <x-input-error :messages="$errors->get('exportTo')" class="mt-2" />
    </div>

    <div class="card elev-sm p-6">
        <h2 class="card-title m-0 mb-3">Mantenimiento correctivo</h2>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° Orden</th><th>Fecha</th><th>Descripción</th><th>Prioridad</th><th>Estado</th><th>Técnico</th><th>Completada</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($correctivos as $wo)
                        <tr wire:key="correctivo-{{ $wo->id }}" class="cursor-pointer" onclick="window.location='{{ route('work-orders.show', $wo) }}'">
                            <td class="font-mono text-xs text-accent-300">{{ $wo->order_number }}</td>
                            <td class="text-muted">{{ $wo->opened_at->format('d/m/Y H:i') }}</td>
                            <td class="text-ink">{{ $wo->failure_description ?? '—' }}</td>
                            <td><span class="tag {{ $priorityTagClass($wo->priority) }}">{{ $wo->priority->label() }}</span></td>
                            <td><span class="tag {{ $statusTagClass($wo->status) }}">{{ $wo->status->label() }}</span></td>
                            <td class="text-muted">{{ $wo->assignedTo->name ?? '—' }}</td>
                            <td class="text-muted">{{ $wo->completed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-8">No hay mantenimientos correctivos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card elev-sm p-6">
        <h2 class="card-title m-0 mb-3">Mantenimiento preventivo</h2>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° Orden</th><th>Fecha</th><th>Plan</th><th>Estado</th><th>Técnico</th><th>Completada</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($preventivos as $wo)
                        <tr wire:key="preventivo-{{ $wo->id }}" class="cursor-pointer" onclick="window.location='{{ route('work-orders.show', $wo) }}'">
                            <td class="font-mono text-xs text-accent-300">{{ $wo->order_number }}</td>
                            <td class="text-muted">{{ $wo->opened_at->format('d/m/Y H:i') }}</td>
                            <td class="text-ink">{{ $wo->maintenancePlan?->name ?? 'Mantenimiento preventivo' }}</td>
                            <td><span class="tag {{ $statusTagClass($wo->status) }}">{{ $wo->status->label() }}</span></td>
                            <td class="text-muted">{{ $wo->assignedTo->name ?? '—' }}</td>
                            <td class="text-muted">{{ $wo->completed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-8">No hay mantenimientos preventivos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card elev-sm p-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="card-title m-0">Listas preoperacionales</h2>
                <p class="text-xs text-neutral-400 mt-0.5">Inspecciones de seguridad registradas antes de iniciar turno.</p>
            </div>

            <form wire:submit="exportPreOperationalChecklists" class="flex flex-wrap items-end gap-3">
                <div class="field">
                    <label>Desde</label>
                    <input wire:model="preopExportFrom" type="date" class="input">
                </div>
                <div class="field">
                    <label>Hasta</label>
                    <input wire:model="preopExportTo" type="date" class="input">
                </div>
                <button type="submit" class="btn btn-secondary">
                    <i class="ph ph-download-simple"></i> Descargar Excel
                </button>
            </form>
        </div>
        <x-input-error :messages="$errors->get('preopExportTo')" class="mt-2" />

        <div class="overflow-x-auto">
            <table class="table mt-4">
                <thead>
                    <tr>
                        <th>Fecha</th><th>Resultado</th><th>Acción requerida</th><th>Responsable</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($preOperationalChecklists as $checklist)
                        <tr wire:key="preop-{{ $checklist->id }}" class="cursor-pointer" onclick="window.location='{{ route('pre-operational-checklists.show', $checklist) }}'">
                            <td class="text-muted">{{ $checklist->inspected_at->format('d/m/Y H:i') }}</td>
                            <td><span class="tag {{ $preopResultTagClass($checklist->result) }}">{{ $checklist->result->label() }}</span></td>
                            <td class="text-muted">{{ $checklist->required_action->label() }}</td>
                            <td class="text-muted">{{ $checklist->performedBy->name }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-8">No hay listas preoperacionales registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <a href="{{ route('pre-operational-checklists.index', ['asset' => $asset->id]) }}" wire:navigate class="mt-3 inline-block text-xs text-accent-300">Ver todas las listas preoperacionales de este activo &rarr;</a>
    </div>

    @if ($showHistory)
        <div class="fixed inset-0 z-50" wire:transition>
            <div class="fixed inset-0" style="background: color-mix(in srgb, var(--color-neutral-900) 60%, transparent);" wire:click="closeHistory"></div>

            <div class="fixed inset-y-0 right-0 w-full max-w-md bg-surface shadow-lg overflow-y-auto">
                <div class="flex items-center justify-between p-6 border-b border-neutral-800">
                    <h2 class="card-title m-0">Historial de mantenimiento</h2>
                    <button wire:click="closeHistory" class="text-neutral-400 hover:text-ink text-xl leading-none">&times;</button>
                </div>

                <div class="p-6 space-y-4">
                    @forelse ($workOrders as $wo)
                        <a href="{{ route('work-orders.show', $wo) }}" wire:navigate
                            class="block rounded-md border border-neutral-800 p-4 hover:border-accent-600">
                            <p class="font-mono text-xs text-accent-300 m-0">{{ $wo->order_number }}</p>
                            <div class="mt-1 flex flex-wrap gap-1.5">
                                <span class="tag {{ $statusTagClass($wo->status) }}">{{ $wo->status->label() }}</span>
                                <span class="tag {{ $priorityTagClass($wo->priority) }}">{{ $wo->priority->label() }}</span>
                                <span class="tag tag-neutral">{{ $wo->type->label() }}</span>
                            </div>
                            <p class="mt-2 text-sm text-ink">{{ $wo->failure_description ?? 'Mantenimiento preventivo programado' }}</p>
                            <p class="mt-1 text-xs text-neutral-500">
                                Abierta {{ $wo->opened_at->format('d/m/Y H:i') }}
                                @if ($wo->completed_at)
                                    · Completada {{ $wo->completed_at->format('d/m/Y H:i') }}
                                @endif
                            </p>
                        </a>
                    @empty
                        <p class="text-sm text-neutral-400">Este activo no tiene mantenimientos registrados todavía.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    @if ($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto dialog-backdrop grid place-items-center p-4" wire:transition>
            <div class="fixed inset-0" wire:click="closeEditModal"></div>

            <div class="dialog relative">
                <h2 class="dialog-title">Editar activo</h2>

                <form wire:submit="saveEdit" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="field sm:col-span-2">
                        <label>Área</label>
                        <select wire:model="edit_area_id" class="input">
                            <option value="">Selecciona un área</option>
                            @foreach ($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->plant->name }} — {{ $area->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('edit_area_id')" class="mt-1" />
                    </div>

                    <div class="field">
                        <label>Código</label>
                        <input wire:model="edit_code" class="input">
                        <x-input-error :messages="$errors->get('edit_code')" class="mt-1" />
                    </div>

                    <div class="field">
                        <label>Nombre</label>
                        <input wire:model="edit_name" class="input">
                        <x-input-error :messages="$errors->get('edit_name')" class="mt-1" />
                    </div>

                    <div class="field">
                        <label>Fabricante</label>
                        <input wire:model="edit_manufacturer" class="input">
                    </div>

                    <div class="field">
                        <label>Modelo</label>
                        <input wire:model="edit_model" class="input">
                    </div>

                    <div class="field">
                        <label>Número de serie</label>
                        <input wire:model="edit_serial_number" class="input">
                    </div>

                    <div class="field">
                        <label>Criticidad</label>
                        <select wire:model="edit_criticality" class="input">
                            @foreach ($criticalities as $c)
                                <option value="{{ $c->value }}">{{ $c->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label>Estado</label>
                        <select wire:model="edit_status" class="input">
                            @foreach ($editStatuses as $s)
                                <option value="{{ $s->value }}">{{ $s->label() }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-neutral-500 mt-1">"En mantenimiento" se calcula solo mientras el activo tenga una orden en progreso.</p>
                    </div>

                    <div class="field sm:col-span-2">
                        <label>Foto (opcional)</label>
                        <input type="file" wire:model="edit_photo" class="input">
                        <x-input-error :messages="$errors->get('edit_photo')" class="mt-1" />
                    </div>

                    <div class="dialog-actions sm:col-span-2">
                        <button type="button" wire:click="closeEditModal" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
