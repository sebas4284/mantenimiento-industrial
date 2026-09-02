<div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
    <div>
        <a href="{{ route('work-orders.index') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-500">&larr; Volver a órdenes</a>
    </div>

    <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm font-mono font-semibold text-indigo-600 dark:text-indigo-400">{{ $workOrder->order_number }}</p>
                <p class="text-xs font-mono text-gray-400">{{ $workOrder->asset->code }} · {{ $workOrder->asset->area->plant->name }} — {{ $workOrder->asset->area->name }}</p>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $workOrder->asset->name }}</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $workOrder->failure_description ?? 'Mantenimiento preventivo programado' }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-badge :color="$workOrder->status->color()">{{ $workOrder->status->label() }}</x-badge>
                <x-badge :color="$workOrder->priority->color()">{{ $workOrder->priority->label() }}</x-badge>
                <x-badge color="zinc">{{ $workOrder->type->label() }}</x-badge>
                <x-badge :color="$workOrder->execution_type->color()">{{ $workOrder->execution_type->label() }}</x-badge>
            </div>
        </div>

        @if ($workOrder->provider)
            <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                Proveedor: <a href="{{ route('providers.show', $workOrder->provider) }}" wire:navigate class="font-medium text-indigo-600 hover:text-indigo-500">{{ $workOrder->provider->name }}</a>
            </p>
        @endif

        <dl class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <dt class="text-gray-400">Reportada por</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $workOrder->reportedBy->name }}</dd>
            </div>
            <div>
                <dt class="text-gray-400">Abierta</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $workOrder->opened_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-gray-400">Iniciada</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $workOrder->started_at?->format('d/m/Y H:i') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-400">Completada</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $workOrder->completed_at?->format('d/m/Y H:i') ?? '—' }}</dd>
            </div>
        </dl>

        <dl class="mt-4 grid grid-cols-3 gap-4 text-sm border-t border-gray-100 dark:border-gray-700 pt-4">
            <div>
                <dt class="text-gray-400">Tiempo de espera</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ \App\Models\WorkOrder::formatDurationMinutes($workOrder->wait_minutes) }}</dd>
            </div>
            <div>
                <dt class="text-gray-400">Tiempo de ejecución</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ \App\Models\WorkOrder::formatDurationMinutes($workOrder->repair_minutes) }}</dd>
            </div>
            <div>
                <dt class="text-gray-400">Duración total</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $workOrder->status->isOpen() ? 'En curso' : \App\Models\WorkOrder::formatDurationMinutes($workOrder->total_minutes) }}</dd>
            </div>
        </dl>

        @can('update', $workOrder)
            <div class="mt-6 border-t border-gray-100 dark:border-gray-700 pt-4">
                <form wire:submit="assign" class="flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[200px]">
                        <x-input-label for="assigned_to" value="Asignar técnico" />
                        <select wire:model="assigned_to" id="assigned_to" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                            <option value="">Sin asignar</option>
                            @foreach ($technicians as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-secondary-button type="submit">Asignar</x-secondary-button>
                </form>
            </div>
        @endcan
    </div>

    @if ($workOrder->maintenancePlan?->checklistTemplate)
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
            <h2 class="font-semibold text-gray-900 dark:text-gray-100">Checklist — {{ $workOrder->maintenancePlan->checklistTemplate->name }}</h2>

            <form wire:submit="saveChecklist" class="mt-4 space-y-4">
                @foreach ($workOrder->maintenancePlan->checklistTemplate->items as $item)
                    <div class="flex items-start gap-4 border-b border-gray-100 dark:border-gray-700 pb-3" wire:key="item-{{ $item->id }}">
                        <div class="flex-1">
                            <p class="text-sm text-gray-800 dark:text-gray-200">{{ $item->label }}</p>
                            <input type="text" wire:model="checklist.{{ $item->id }}.notes" placeholder="Notas (opcional)"
                                class="mt-1 w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm">
                        </div>
                        <div class="flex gap-2 shrink-0 pt-1">
                            <label class="inline-flex items-center gap-1 text-xs text-green-700 dark:text-green-400">
                                <input type="radio" wire:model="checklist.{{ $item->id }}.passed" value="1"> OK
                            </label>
                            <label class="inline-flex items-center gap-1 text-xs text-red-700 dark:text-red-400">
                                <input type="radio" wire:model="checklist.{{ $item->id }}.passed" value="0"> Falla
                            </label>
                        </div>
                    </div>
                @endforeach

                <x-primary-button>Guardar checklist</x-primary-button>
            </form>
        </div>
    @endif

    @can('update', $workOrder)
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
            <h2 class="font-semibold text-gray-900 dark:text-gray-100">Resolución</h2>

            <form wire:submit="saveResolution" class="mt-3 space-y-3">
                <textarea wire:model="resolution_notes" rows="3" placeholder="Notas de resolución..."
                    class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm"></textarea>

                <div class="flex gap-3">
                    <x-secondary-button type="submit">Guardar notas</x-secondary-button>
                    @if ($workOrder->status->isOpen())
                        <x-primary-button type="button" wire:click="complete" wire:confirm="¿Marcar esta orden como completada?">Completar orden</x-primary-button>
                    @endif
                </div>
            </form>
        </div>
    @endcan

    @can('registerUsage', \App\Models\SparePart::class)
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
            <h2 class="font-semibold text-gray-900 dark:text-gray-100">Repuestos utilizados</h2>

            <div class="mt-3 space-y-2">
                @forelse ($workOrder->sparePartUsages as $usage)
                    <div class="flex items-center justify-between text-sm border-b border-gray-100 dark:border-gray-700 pb-2" wire:key="usage-{{ $usage->id }}">
                        <span class="text-gray-800 dark:text-gray-200">{{ $usage->sparePart->name }} <span class="text-gray-400 font-mono text-xs">{{ $usage->sparePart->code }}</span></span>
                        <span class="text-gray-600 dark:text-gray-300">x{{ $usage->quantity }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No se han registrado repuestos.</p>
                @endforelse
            </div>

            <form wire:submit="addSparePartUsage" class="mt-4 flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <x-input-label for="spare_part_id" value="Repuesto" />
                    <select wire:model="spare_part_id" id="spare_part_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                        <option value="">Selecciona un repuesto</option>
                        @foreach ($spareParts as $part)
                            <option value="{{ $part->id }}">{{ $part->name }} ({{ $part->stock_quantity }} disponibles)</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('spare_part_id')" class="mt-1" />
                </div>
                <div class="w-24">
                    <x-input-label for="spare_part_quantity" value="Cantidad" />
                    <x-text-input wire:model="spare_part_quantity" id="spare_part_quantity" type="number" min="1" class="mt-1 block w-full text-sm" />
                    <x-input-error :messages="$errors->get('spare_part_quantity')" class="mt-1" />
                </div>
                <x-secondary-button type="submit">Registrar</x-secondary-button>
            </form>
        </div>
    @endcan

    <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
        <h2 class="font-semibold text-gray-900 dark:text-gray-100">Fotos adjuntas</h2>

        <div class="mt-3 flex flex-wrap gap-3">
            @foreach ($workOrder->attachments as $attachment)
                <a href="{{ Storage::disk('public')->url($attachment->path) }}" target="_blank">
                    <img src="{{ Storage::disk('public')->url($attachment->path) }}" class="h-20 w-20 rounded-lg object-cover ring-1 ring-gray-200">
                </a>
            @endforeach
        </div>

        @can('update', $workOrder)
            <form wire:submit="uploadPhoto" class="mt-4 flex items-center gap-3">
                <input type="file" wire:model="newPhoto" class="text-sm text-gray-500 dark:text-gray-400">
                <x-secondary-button type="submit">Subir foto</x-secondary-button>
            </form>
            <x-input-error :messages="$errors->get('newPhoto')" class="mt-1" />
        @endcan
    </div>
</div>
