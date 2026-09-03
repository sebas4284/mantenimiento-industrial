<div>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <input wire:model.live.debounce.400ms="search" type="text" placeholder="Buscar por N.° de orden, activo o descripción..."
                    class="w-72 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">

                <select wire:model.live="typeFilter" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm text-sm">
                    <option value="">Todos los tipos</option>
                    <option value="correctivo">Correctivo</option>
                    <option value="preventivo">Preventivo</option>
                </select>
            </div>

            @can('create', \App\Models\WorkOrder::class)
                <button wire:click="create" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Crear reporte
                </button>
            @endcan
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
            @foreach ($columns as $column)
                <div class="rounded-xl bg-gray-50 dark:bg-gray-900/40 ring-1 ring-gray-200 dark:ring-gray-800 p-3">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center justify-between">
                        {{ $column->label() }}
                        <span class="text-xs font-normal text-gray-400">{{ $workOrdersByStatus->get($column->value, collect())->count() }}</span>
                    </h3>

                    <div class="mt-3 space-y-3">
                        @forelse ($workOrdersByStatus->get($column->value, collect()) as $wo)
                            <div wire:key="wo-{{ $wo->id }}" class="rounded-lg bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-3">
                                <a href="{{ route('work-orders.show', $wo) }}" wire:navigate class="block">
                                    <p class="text-sm font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $wo->order_number }}</p>
                                    <p class="text-xs font-mono text-gray-400">{{ $wo->asset->code }}</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $wo->asset->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mt-1">{{ $wo->failure_description ?? $wo->type->label() }}</p>
                                </a>

                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    <x-badge :color="$wo->priority->color()">{{ $wo->priority->label() }}</x-badge>
                                    <x-badge color="zinc">{{ $wo->type->label() }}</x-badge>
                                </div>

                                <p class="mt-2 text-[11px] text-gray-400">Abierta {{ $wo->opened_at->diffForHumans() }}</p>

                                <div class="mt-2 flex flex-wrap gap-2 border-t border-gray-100 dark:border-gray-700 pt-2">
                                    @can('update', $wo)
                                        @if ($column === \App\Enums\WorkOrderStatus::Abierta)
                                            <button wire:click="take({{ $wo->id }})" class="text-xs font-medium text-indigo-600 hover:text-indigo-500">Iniciar</button>
                                        @endif
                                        @if ($column === \App\Enums\WorkOrderStatus::EnProgreso)
                                            <button wire:click="transition({{ $wo->id }}, 'en_espera')" class="text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900">Pausar</button>
                                            <button wire:click="transition({{ $wo->id }}, 'completada')" class="text-xs font-medium text-green-600 hover:text-green-500">Completar</button>
                                        @endif
                                        @if ($column === \App\Enums\WorkOrderStatus::EnEspera)
                                            <button wire:click="transition({{ $wo->id }}, 'en_progreso')" class="text-xs font-medium text-indigo-600 hover:text-indigo-500">Reanudar</button>
                                        @endif
                                        @if ($column->isOpen())
                                            <button wire:click="transition({{ $wo->id }}, 'cancelada')" wire:confirm="¿Cancelar esta orden?" class="text-xs font-medium text-red-600 hover:text-red-500">Cancelar</button>
                                        @endif
                                    @endcan
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 text-center py-6">Sin órdenes</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8 rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h2 class="font-semibold text-gray-900 dark:text-gray-100">Historial (completadas y canceladas)</h2>

                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <x-input-label for="dateFrom" value="Desde" />
                        <input wire:model.live="dateFrom" type="date" id="dateFrom" class="mt-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                    </div>
                    <div>
                        <x-input-label for="dateTo" value="Hasta" />
                        <input wire:model.live="dateTo" type="date" id="dateTo" class="mt-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                    </div>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto rounded-xl ring-1 ring-gray-200 dark:ring-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                        <tr class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                            <th class="px-4 py-3">N° Orden</th>
                            <th class="px-4 py-3">Activo</th>
                            <th class="px-4 py-3">Descripción</th>
                            <th class="px-4 py-3">Prioridad</th>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Abierta</th>
                            <th class="px-4 py-3">Completada</th>
                            <th class="px-4 py-3">Duración total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($historial as $wo)
                            <tr wire:key="historial-{{ $wo->id }}" class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900/30" onclick="window.location='{{ route('work-orders.show', $wo) }}'">
                                <td class="px-4 py-3 font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $wo->order_number }}</td>
                                <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $wo->asset->code }} — {{ $wo->asset->name }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300 max-w-xs truncate">{{ $wo->failure_description ?? $wo->type->label() }}</td>
                                <td class="px-4 py-3"><x-badge :color="$wo->priority->color()">{{ $wo->priority->label() }}</x-badge></td>
                                <td class="px-4 py-3"><x-badge color="zinc">{{ $wo->type->label() }}</x-badge></td>
                                <td class="px-4 py-3"><x-badge :color="$wo->status->color()">{{ $wo->status->label() }}</x-badge></td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $wo->opened_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $wo->completed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ \App\Models\WorkOrder::formatDurationMinutes($wo->total_minutes) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay órdenes completadas o canceladas en este rango.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $historial->links() }}</div>
        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:transition>
            <div class="fixed inset-0 bg-gray-900/50" wire:click="closeModal"></div>

            <div class="relative mx-auto mt-16 w-full max-w-lg rounded-xl bg-white dark:bg-gray-800 p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Crear reporte</h2>

                <form wire:submit="save" class="mt-4 space-y-4">
                    <div>
                        <x-input-label for="asset_id" value="Activo" />
                        <select wire:model="asset_id" id="asset_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                            <option value="">Selecciona un activo</option>
                            @foreach ($assets as $asset)
                                <option value="{{ $asset->id }}">{{ $asset->code }} — {{ $asset->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('asset_id')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="type" value="Tipo" />
                        <select wire:model="type" id="type" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                            @foreach ($types as $t)
                                <option value="{{ $t->value }}">{{ $t->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="priority" value="Prioridad" />
                        <select wire:model="priority" id="priority" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                            @foreach ($priorities as $p)
                                <option value="{{ $p->value }}">{{ $p->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="execution_type" value="Ejecución" />
                        <select wire:model.live="execution_type" id="execution_type" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                            @foreach ($executionTypes as $e)
                                <option value="{{ $e->value }}">{{ $e->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('execution_type')" class="mt-1" />
                    </div>

                    @if ($execution_type === 'externo')
                        <div>
                            <x-input-label for="provider_id" value="Proveedor" />
                            <select wire:model="provider_id" id="provider_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                                <option value="">Selecciona un proveedor</option>
                                @foreach ($providers as $provider)
                                    <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('provider_id')" class="mt-1" />
                        </div>
                    @endif

                    <div>
                        <x-input-label for="failure_description" value="Descripción de la falla" />
                        <textarea wire:model="failure_description" id="failure_description" rows="4" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm"></textarea>
                        <x-input-error :messages="$errors->get('failure_description')" class="mt-1" />
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-secondary-button type="button" wire:click="closeModal">Cancelar</x-secondary-button>
                        <x-primary-button>Crear reporte</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
