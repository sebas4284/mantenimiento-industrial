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
                <p class="mt-2 text-xs text-gray-400">Reportada por <span class="text-gray-600 dark:text-gray-300">{{ $workOrder->reportedBy->name }}</span></p>
            </div>

            <div class="flex flex-col items-end gap-3">
                <div class="flex flex-wrap justify-end gap-2">
                    <x-badge :color="$workOrder->status->color()">{{ $workOrder->status->label() }}</x-badge>
                    <x-badge :color="$workOrder->priority->color()">{{ $workOrder->priority->label() }}</x-badge>
                    <x-badge color="zinc">{{ $workOrder->type->label() }}</x-badge>
                    <x-badge :color="$workOrder->execution_type->color()">{{ $workOrder->execution_type->label() }}</x-badge>
                </div>

                <div class="flex gap-2">
                    @can('update', $workOrder)
                        <x-secondary-button wire:click="openEditModal">Editar</x-secondary-button>
                    @endcan
                    <x-secondary-button wire:click="downloadReport">Descargar informe</x-secondary-button>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
        <h2 class="font-semibold text-gray-900 dark:text-gray-100">Asignación</h2>

        @if ($workOrder->provider)
            <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                Proveedor: <a href="{{ route('providers.show', $workOrder->provider) }}" wire:navigate class="font-medium text-indigo-600 hover:text-indigo-500">{{ $workOrder->provider->name }}</a>
            </p>
        @endif

        @if ($workOrder->supportCollaborator)
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                Colaborador de apoyo: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $workOrder->supportCollaborator->name }}</span>
            </p>
        @endif

        @if ($workOrder->assignedTo && $workOrder->execution_type === \App\Enums\WorkOrderExecutionType::Interno)
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                Colaborador asignado: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $workOrder->assignedTo->name }}</span>
            </p>
        @endif

        @can('update', $workOrder)
            <div class="mt-4 border-t border-gray-100 dark:border-gray-700 pt-4">
                <form wire:submit="assign" class="flex flex-wrap items-end gap-3">
                    @if ($workOrder->execution_type === \App\Enums\WorkOrderExecutionType::Externo)
                        <div class="flex-1 min-w-[200px]">
                            <x-input-label for="provider_id" value="Proveedor" />
                            <select wire:model="provider_id" id="provider_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                                <option value="">Sin asignar</option>
                                @foreach ($providers as $prov)
                                    <option value="{{ $prov->id }}">{{ $prov->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('provider_id')" class="mt-1" />
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <x-input-label for="support_collaborator_id" value="Colaborador asignado de apoyo" />
                            <select wire:model.live="support_collaborator_id" id="support_collaborator_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                                <option value="">Sin asignar</option>
                                @foreach ($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('support_collaborator_id')" class="mt-1" />
                            @php $selectedSupport = $technicians->firstWhere('id', (int) $support_collaborator_id); @endphp
                            @if ($selectedSupport && ($selectedSupport->active_assigned_count + $selectedSupport->active_support_count) > 0)
                                <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">⚠ {{ $selectedSupport->name }} ya tiene una orden en curso.</p>
                            @endif
                        </div>
                    @else
                        <div class="flex-1 min-w-[200px]">
                            <x-input-label for="assigned_to" value="Colaborador asignado" />
                            <select wire:model.live="assigned_to" id="assigned_to" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                                <option value="">Sin asignar</option>
                                @foreach ($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('assigned_to')" class="mt-1" />
                            @php $selectedTech = $technicians->firstWhere('id', (int) $assigned_to); @endphp
                            @if ($selectedTech && ($selectedTech->active_assigned_count + $selectedTech->active_support_count) > 0)
                                <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">⚠ {{ $selectedTech->name }} ya tiene una orden en curso.</p>
                            @endif
                        </div>
                    @endif
                    <x-secondary-button type="submit">Asignar</x-secondary-button>
                </form>
            </div>
        @endcan
    </div>

    <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
        <h2 class="font-semibold text-gray-900 dark:text-gray-100">Tiempos</h2>

        <dl class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
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
            <div>
                <dt class="text-gray-400">Duración total</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $workOrder->status->isOpen() ? 'En curso' : \App\Models\WorkOrder::formatDurationMinutes($workOrder->total_minutes) }}</dd>
            </div>
        </dl>

        <dl class="mt-4 grid grid-cols-2 gap-4 text-sm border-t border-gray-100 dark:border-gray-700 pt-4">
            <div>
                <dt class="text-gray-400">Tiempo de espera</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ \App\Models\WorkOrder::formatDurationMinutes($workOrder->wait_minutes) }}</dd>
            </div>
            <div>
                <dt class="text-gray-400">Tiempo de ejecución</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ \App\Models\WorkOrder::formatDurationMinutes($workOrder->repair_minutes) }}</dd>
            </div>
        </dl>
    </div>

    @if ($workOrder->execution_type === \App\Enums\WorkOrderExecutionType::Externo)
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
            <h2 class="font-semibold text-gray-900 dark:text-gray-100">Factura / requerimiento de compra</h2>

            @can('update', $workOrder)
                <form wire:submit="saveInvoiceInfo" class="mt-4 flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[200px]">
                        <x-input-label for="invoice_number" value="N.° factura / requerimiento de compra" />
                        <x-text-input wire:model="invoice_number" id="invoice_number" class="mt-1 block w-full text-sm" />
                        <x-input-error :messages="$errors->get('invoice_number')" class="mt-1" />
                    </div>
                    <div class="w-40">
                        <x-input-label for="amount_paid" value="Monto pagado" />
                        <x-text-input wire:model="amount_paid" id="amount_paid" type="number" step="0.01" min="0" class="mt-1 block w-full text-sm" />
                        <x-input-error :messages="$errors->get('amount_paid')" class="mt-1" />
                    </div>
                    <x-secondary-button type="submit">Guardar</x-secondary-button>
                </form>
            @else
                <dl class="mt-4 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-400">N.° factura / requerimiento de compra</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $workOrder->invoice_number ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Monto pagado</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $workOrder->amount_paid !== null ? '$'.number_format((float) $workOrder->amount_paid, 2) : '—' }}</dd>
                    </div>
                </dl>
            @endcan
        </div>
    @endif

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
            <h2 class="font-semibold text-gray-900 dark:text-gray-100">Descripción de las reparaciones o mantenimientos realizados</h2>

            <form wire:submit="saveResolution" class="mt-3 space-y-3">
                <textarea wire:model="resolution_notes" rows="3" placeholder="Describe las reparaciones o mantenimientos realizados..."
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
            <h2 class="font-semibold text-gray-900 dark:text-gray-100">Relación de insumos o partes que se cambian</h2>

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
        <h2 class="font-semibold text-gray-900 dark:text-gray-100">Evidencia fotográfica</h2>

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

    @if ($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:transition>
            <div class="fixed inset-0 bg-gray-900/50" wire:click="closeEditModal"></div>

            <div class="relative mx-auto mt-16 w-full max-w-lg rounded-xl bg-white dark:bg-gray-800 p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Editar reporte {{ $workOrder->order_number }}</h2>

                <form wire:submit="saveEdit" class="mt-4 space-y-4">
                    <div>
                        <x-input-label for="edit_type" value="Tipo" />
                        <select wire:model="edit_type" id="edit_type" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                            @foreach ($types as $t)
                                <option value="{{ $t->value }}">{{ $t->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('edit_type')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="edit_priority" value="Prioridad" />
                        <select wire:model="edit_priority" id="edit_priority" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                            @foreach ($priorities as $p)
                                <option value="{{ $p->value }}">{{ $p->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('edit_priority')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="edit_execution_type" value="Tipo de ejecución" />
                        <select wire:model="edit_execution_type" id="edit_execution_type" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                            @foreach ($executionTypes as $e)
                                <option value="{{ $e->value }}">{{ $e->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('edit_execution_type')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="edit_failure_description" value="Descripción de la falla" />
                        <textarea wire:model="edit_failure_description" id="edit_failure_description" rows="3"
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm"></textarea>
                        <x-input-error :messages="$errors->get('edit_failure_description')" class="mt-1" />
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-secondary-button type="button" wire:click="closeEditModal">Cancelar</x-secondary-button>
                        <x-primary-button>Guardar</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
