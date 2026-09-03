<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-clipboard-text text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Órdenes de trabajo</h1>
    </div>
</x-slot>

<div class="space-y-4">
    @php
        $priorityTagClass = fn ($priority) => match ($priority) {
            \App\Enums\WorkOrderPriority::Urgente, \App\Enums\WorkOrderPriority::Alta => 'tag-accent',
            \App\Enums\WorkOrderPriority::Media => 'tag-outline',
            \App\Enums\WorkOrderPriority::Baja => 'tag-neutral',
        };
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-3">
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Buscar por N.° de orden, activo o descripción..." class="input w-72">

            <select wire:model.live="typeFilter" class="input w-auto">
                <option value="">Todos los tipos</option>
                <option value="correctivo">Correctivo</option>
                <option value="preventivo">Preventivo</option>
            </select>
        </div>

        @can('create', \App\Models\WorkOrder::class)
            <button wire:click="create" class="btn btn-primary">
                <i class="ph ph-plus"></i> Crear reporte
            </button>
        @endcan
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-start">
        @foreach ($columns as $column)
            <div class="bg-neutral-900 border border-neutral-800 rounded-md p-3 flex flex-col gap-3">
                <h3 class="text-sm text-neutral-300 flex items-center justify-between m-0">
                    {{ $column->label() }}
                    <span class="text-xs text-neutral-500">{{ $workOrdersByStatus->get($column->value, collect())->count() }}</span>
                </h3>

                <div class="flex flex-col gap-3">
                    @forelse ($workOrdersByStatus->get($column->value, collect()) as $wo)
                        <div wire:key="wo-{{ $wo->id }}" class="card elev-sm p-3 gap-1.5">
                            <a href="{{ route('work-orders.show', $wo) }}" wire:navigate class="block">
                                <p class="m-0 font-mono text-xs text-accent-300">{{ $wo->order_number }}</p>
                                <p class="m-0 font-mono text-xs text-neutral-500">{{ $wo->asset->code }}</p>
                                <p class="m-0 text-sm text-ink">{{ $wo->asset->name }}</p>
                                <p class="mt-1 text-xs text-neutral-400 line-clamp-2">{{ $wo->failure_description ?? $wo->type->label() }}</p>
                            </a>

                            <div class="flex flex-wrap gap-1.5">
                                <span class="tag {{ $priorityTagClass($wo->priority) }}">{{ $wo->priority->label() }}</span>
                                <span class="tag tag-neutral">{{ $wo->type->label() }}</span>
                            </div>

                            <p class="mt-1 text-[11px] text-neutral-500">Abierta {{ $wo->opened_at->diffForHumans() }}</p>

                            <div class="mt-1 flex flex-wrap gap-3 border-t border-neutral-800 pt-2">
                                @can('update', $wo)
                                    @if ($column === \App\Enums\WorkOrderStatus::Abierta)
                                        <button wire:click="take({{ $wo->id }})" class="btn-ghost text-xs">Iniciar</button>
                                    @endif
                                    @if ($column === \App\Enums\WorkOrderStatus::EnProgreso)
                                        <button wire:click="transition({{ $wo->id }}, 'en_espera')" class="text-xs text-neutral-300 hover:text-ink">Pausar</button>
                                        <button wire:click="transition({{ $wo->id }}, 'completada')" class="btn-ghost text-xs">Completar</button>
                                    @endif
                                    @if ($column === \App\Enums\WorkOrderStatus::EnEspera)
                                        <button wire:click="transition({{ $wo->id }}, 'en_progreso')" class="btn-ghost text-xs">Reanudar</button>
                                    @endif
                                    @if ($column->isOpen())
                                        <button wire:click="transition({{ $wo->id }}, 'cancelada')" wire:confirm="¿Cancelar esta orden?" class="text-xs text-neutral-400 hover:text-ink">Cancelar</button>
                                    @endif
                                @endcan
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-neutral-500 text-center py-6">Sin órdenes</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <div class="card elev-sm p-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="card-title m-0">Historial (completadas y canceladas)</h2>

            <div class="flex flex-wrap items-end gap-3">
                <div class="field">
                    <label>Desde</label>
                    <input wire:model.live="dateFrom" type="date" class="input">
                </div>
                <div class="field">
                    <label>Hasta</label>
                    <input wire:model.live="dateTo" type="date" class="input">
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table mt-4">
                <thead>
                    <tr>
                        <th>N° Orden</th><th>Activo</th><th>Descripción</th><th>Prioridad</th><th>Tipo</th><th>Estado</th><th>Abierta</th><th>Completada</th><th>Duración total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($historial as $wo)
                        <tr wire:key="historial-{{ $wo->id }}" class="cursor-pointer" onclick="window.location='{{ route('work-orders.show', $wo) }}'">
                            <td class="font-mono text-xs text-accent-300">{{ $wo->order_number }}</td>
                            <td class="text-ink">{{ $wo->asset->code }} — {{ $wo->asset->name }}</td>
                            <td class="text-muted max-w-xs truncate">{{ $wo->failure_description ?? $wo->type->label() }}</td>
                            <td><span class="tag {{ $priorityTagClass($wo->priority) }}">{{ $wo->priority->label() }}</span></td>
                            <td><span class="tag tag-neutral">{{ $wo->type->label() }}</span></td>
                            <td><span class="tag {{ $wo->status === \App\Enums\WorkOrderStatus::Completada ? 'tag-neutral' : 'tag-outline' }}">{{ $wo->status->label() }}</span></td>
                            <td class="text-muted">{{ $wo->opened_at->format('d/m/Y H:i') }}</td>
                            <td class="text-muted">{{ $wo->completed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="text-muted">{{ \App\Models\WorkOrder::formatDurationMinutes($wo->total_minutes) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-8">No hay órdenes completadas o canceladas en este rango.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $historial->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto dialog-backdrop grid place-items-center p-4" wire:transition>
            <div class="fixed inset-0" wire:click="closeModal"></div>

            <div class="dialog relative">
                <h2 class="dialog-title">Crear reporte</h2>

                <form wire:submit="save" class="flex flex-col gap-4">
                    <div class="field">
                        <label>Activo</label>
                        <select wire:model="asset_id" class="input">
                            <option value="">Selecciona un activo</option>
                            @foreach ($assets as $asset)
                                <option value="{{ $asset->id }}">{{ $asset->code }} — {{ $asset->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('asset_id')" class="mt-1" />
                    </div>

                    <div class="field">
                        <label>Tipo</label>
                        <select wire:model="type" class="input">
                            @foreach ($types as $t)
                                <option value="{{ $t->value }}">{{ $t->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-1" />
                    </div>

                    <div class="field">
                        <label>Prioridad</label>
                        <select wire:model="priority" class="input">
                            @foreach ($priorities as $p)
                                <option value="{{ $p->value }}">{{ $p->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label>Ejecución</label>
                        <select wire:model.live="execution_type" class="input">
                            @foreach ($executionTypes as $e)
                                <option value="{{ $e->value }}">{{ $e->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('execution_type')" class="mt-1" />
                    </div>

                    @if ($execution_type === 'externo')
                        <div class="field">
                            <label>Proveedor</label>
                            <select wire:model="provider_id" class="input">
                                <option value="">Selecciona un proveedor</option>
                                @foreach ($providers as $provider)
                                    <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('provider_id')" class="mt-1" />
                        </div>
                    @endif

                    <div class="field">
                        <label>Descripción de la falla</label>
                        <textarea wire:model="failure_description" rows="4" class="input"></textarea>
                        <x-input-error :messages="$errors->get('failure_description')" class="mt-1" />
                    </div>

                    <div class="dialog-actions">
                        <button type="button" wire:click="closeModal" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear reporte</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
