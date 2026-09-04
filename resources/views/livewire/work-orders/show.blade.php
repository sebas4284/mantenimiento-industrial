<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-clipboard-text text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Orden {{ $workOrder->order_number }}</h1>
    </div>
</x-slot>

<div class="space-y-4">
    @php
        $executionTagClass = fn ($type) => $type === \App\Enums\WorkOrderExecutionType::Externo ? 'tag-outline' : 'tag-neutral';
    @endphp

    <a href="{{ route('work-orders.index') }}" wire:navigate class="text-sm text-accent-300">&larr; Volver a órdenes</a>

    <div class="card elev-sm p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm font-mono text-accent-300 m-0">{{ $workOrder->order_number }}</p>
                <p class="text-xs font-mono text-neutral-500 m-0">{{ $workOrder->asset->code }} · {{ $workOrder->asset->area->plant->name }} — {{ $workOrder->asset->area->name }}</p>
                <h2 class="text-xl text-ink m-0">{{ $workOrder->asset->name }}</h2>
                <p class="mt-1 text-sm text-neutral-300">{{ $workOrder->failure_description ?? 'Mantenimiento preventivo programado' }}</p>
                <p class="mt-2 text-xs text-neutral-500">Reportada por <span class="text-neutral-300">{{ $workOrder->reportedBy->name }}</span></p>
            </div>

            <div class="flex flex-col items-end gap-3">
                <div class="flex flex-wrap justify-end gap-2">
                    <span class="tag tag-{{ $workOrder->status->tagVariant() }}">{{ $workOrder->status->label() }}</span>
                    <span class="tag tag-{{ $workOrder->priority->tagVariant() }}">{{ $workOrder->priority->label() }}</span>
                    <span class="tag tag-neutral">{{ $workOrder->type->label() }}</span>
                    <span class="tag {{ $executionTagClass($workOrder->execution_type) }}">{{ $workOrder->execution_type->label() }}</span>
                </div>

                <div class="flex gap-2">
                    @can('update', $workOrder)
                        <button wire:click="openEditModal" class="btn btn-secondary">Editar</button>
                    @endcan
                    <button wire:click="downloadReport" class="btn btn-secondary">Descargar informe</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card elev-sm p-6">
        <h2 class="card-title m-0">Asignación</h2>

        @if ($workOrder->provider)
            <p class="mt-3 text-sm text-neutral-300">
                Proveedor: <a href="{{ route('providers.show', $workOrder->provider) }}" wire:navigate class="font-medium text-accent-300">{{ $workOrder->provider->name }}</a>
            </p>
        @endif

        @if ($workOrder->supportCollaborator)
            <p class="mt-1 text-sm text-neutral-300">
                Colaborador de apoyo: <span class="font-medium text-ink">{{ $workOrder->supportCollaborator->name }}</span>
            </p>
        @endif

        @if ($workOrder->assignedTo && $workOrder->execution_type === \App\Enums\WorkOrderExecutionType::Interno)
            <p class="mt-1 text-sm text-neutral-300">
                Colaborador asignado: <span class="font-medium text-ink">{{ $workOrder->assignedTo->name }}</span>
            </p>
        @endif

        @can('update', $workOrder)
            <div class="mt-4 border-t border-neutral-800 pt-4">
                <form wire:submit="assign" class="flex flex-wrap items-end gap-3">
                    @if ($workOrder->execution_type === \App\Enums\WorkOrderExecutionType::Externo)
                        <div class="field flex-1 min-w-[200px]">
                            <label>Proveedor</label>
                            <select wire:model="provider_id" class="input">
                                <option value="">Sin asignar</option>
                                @foreach ($providers as $prov)
                                    <option value="{{ $prov->id }}">{{ $prov->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('provider_id')" class="mt-1" />
                        </div>
                        <div class="field flex-1 min-w-[200px]">
                            <label>Colaborador asignado de apoyo</label>
                            <select wire:model.live="support_collaborator_id" class="input">
                                <option value="">Sin asignar</option>
                                @foreach ($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('support_collaborator_id')" class="mt-1" />
                            @php $selectedSupport = $technicians->firstWhere('id', (int) $support_collaborator_id); @endphp
                            @if ($selectedSupport && ($selectedSupport->active_assigned_count + $selectedSupport->active_support_count) > 0)
                                <p class="mt-1 text-xs text-accent-300">⚠ {{ $selectedSupport->name }} ya tiene una orden en curso.</p>
                            @endif
                        </div>
                    @else
                        <div class="field flex-1 min-w-[200px]">
                            <label>Colaborador asignado</label>
                            <select wire:model.live="assigned_to" class="input">
                                <option value="">Sin asignar</option>
                                @foreach ($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('assigned_to')" class="mt-1" />
                            @php $selectedTech = $technicians->firstWhere('id', (int) $assigned_to); @endphp
                            @if ($selectedTech && ($selectedTech->active_assigned_count + $selectedTech->active_support_count) > 0)
                                <p class="mt-1 text-xs text-accent-300">⚠ {{ $selectedTech->name }} ya tiene una orden en curso.</p>
                            @endif
                        </div>
                    @endif
                    <button type="submit" class="btn btn-secondary">Asignar</button>
                </form>
            </div>
        @endcan
    </div>

    <div class="card elev-sm p-6">
        <h2 class="card-title m-0">Tiempos</h2>

        <dl class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <dt class="text-neutral-500">Abierta</dt>
                <dd class="text-ink">{{ $workOrder->opened_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-neutral-500">Iniciada</dt>
                <dd class="text-ink">{{ $workOrder->started_at?->format('d/m/Y H:i') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-neutral-500">Completada</dt>
                <dd class="text-ink">{{ $workOrder->completed_at?->format('d/m/Y H:i') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-neutral-500">Duración total</dt>
                <dd class="text-ink">{{ $workOrder->status->isOpen() ? 'En curso' : \App\Models\WorkOrder::formatDurationMinutes($workOrder->total_minutes) }}</dd>
            </div>
        </dl>

        <dl class="mt-4 grid grid-cols-2 gap-4 text-sm border-t border-neutral-800 pt-4">
            <div>
                <dt class="text-neutral-500">Tiempo de espera</dt>
                <dd class="text-ink">{{ \App\Models\WorkOrder::formatDurationMinutes($workOrder->wait_minutes) }}</dd>
            </div>
            <div>
                <dt class="text-neutral-500">Tiempo de ejecución</dt>
                <dd class="text-ink">{{ \App\Models\WorkOrder::formatDurationMinutes($workOrder->repair_minutes) }}</dd>
            </div>
        </dl>
    </div>

    @if ($workOrder->execution_type === \App\Enums\WorkOrderExecutionType::Externo)
        <div class="card elev-sm p-6">
            <h2 class="card-title m-0">Factura / requerimiento de compra</h2>

            @can('update', $workOrder)
                <form wire:submit="saveInvoiceInfo" class="mt-4 flex flex-wrap items-end gap-3">
                    <div class="field flex-1 min-w-[200px]">
                        <label>N.° factura / requerimiento de compra</label>
                        <input wire:model="invoice_number" class="input">
                        <x-input-error :messages="$errors->get('invoice_number')" class="mt-1" />
                    </div>
                    <div class="field w-40">
                        <label>Monto pagado</label>
                        <input wire:model="amount_paid" type="number" step="0.01" min="0" class="input">
                        <x-input-error :messages="$errors->get('amount_paid')" class="mt-1" />
                    </div>
                    <button type="submit" class="btn btn-secondary">Guardar</button>
                </form>
            @else
                <dl class="mt-4 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-neutral-500">N.° factura / requerimiento de compra</dt>
                        <dd class="text-ink">{{ $workOrder->invoice_number ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-500">Monto pagado</dt>
                        <dd class="text-ink">{{ $workOrder->amount_paid !== null ? '$'.number_format((float) $workOrder->amount_paid, 2) : '—' }}</dd>
                    </div>
                </dl>
            @endcan
        </div>
    @endif

    @if ($workOrder->maintenancePlan?->checklistTemplate)
        <div class="card elev-sm p-6">
            <h2 class="card-title m-0">Checklist — {{ $workOrder->maintenancePlan->checklistTemplate->name }}</h2>

            <form wire:submit="saveChecklist" class="mt-4 flex flex-col gap-4">
                @foreach ($workOrder->maintenancePlan->checklistTemplate->items as $item)
                    <div class="flex items-start gap-4 border-b border-neutral-800 pb-3" wire:key="item-{{ $item->id }}">
                        <div class="flex-1">
                            <p class="text-sm text-neutral-200 m-0">{{ $item->label }}</p>
                            <input type="text" wire:model="checklist.{{ $item->id }}.notes" placeholder="Notas (opcional)" class="input mt-1 text-xs">
                        </div>
                        <div class="flex gap-2 shrink-0 pt-1">
                            <label class="flex items-center gap-1 text-xs text-neutral-300">
                                <input type="radio" wire:model="checklist.{{ $item->id }}.passed" value="1"> OK
                            </label>
                            <label class="flex items-center gap-1 text-xs text-accent-300">
                                <input type="radio" wire:model="checklist.{{ $item->id }}.passed" value="0"> Falla
                            </label>
                        </div>
                    </div>
                @endforeach

                <button type="submit" class="btn btn-primary">Guardar checklist</button>
            </form>
        </div>
    @endif

    @can('update', $workOrder)
        <div class="card elev-sm p-6">
            <h2 class="card-title m-0">Descripción de las reparaciones o mantenimientos realizados</h2>

            <form wire:submit="saveResolution" class="mt-3 flex flex-col gap-3">
                <textarea wire:model="resolution_notes" rows="3" placeholder="Describe las reparaciones o mantenimientos realizados..." class="input"></textarea>

                <div class="flex gap-3">
                    <button type="submit" class="btn btn-secondary">Guardar notas</button>
                    @if ($workOrder->status->isOpen())
                        <button type="button" wire:click="complete" wire:confirm="¿Marcar esta orden como completada?" class="btn btn-primary">Completar orden</button>
                    @endif
                </div>
            </form>
        </div>
    @endcan

    @can('registerUsage', \App\Models\SparePart::class)
        <div class="card elev-sm p-6">
            <h2 class="card-title m-0">Relación de insumos o partes que se cambian</h2>

            <div class="mt-3 flex flex-col gap-2">
                @forelse ($workOrder->sparePartUsages as $usage)
                    <div class="flex items-center justify-between text-sm border-b border-neutral-800 pb-2" wire:key="usage-{{ $usage->id }}">
                        <span class="text-neutral-200">{{ $usage->sparePart->name }} <span class="text-neutral-500 font-mono text-xs">{{ $usage->sparePart->code }}</span></span>
                        <span class="text-neutral-300">x{{ $usage->quantity }}</span>
                    </div>
                @empty
                    <p class="text-sm text-neutral-500">No se han registrado repuestos.</p>
                @endforelse
            </div>

            <form wire:submit="addSparePartUsage" class="mt-4 flex flex-wrap items-end gap-3">
                <div class="field flex-1 min-w-[200px]">
                    <label>Repuesto</label>
                    <select wire:model="spare_part_id" class="input">
                        <option value="">Selecciona un repuesto</option>
                        @foreach ($spareParts as $part)
                            <option value="{{ $part->id }}">{{ $part->name }} ({{ $part->stock_quantity }} disponibles)</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('spare_part_id')" class="mt-1" />
                </div>
                <div class="field w-24">
                    <label>Cantidad</label>
                    <input wire:model="spare_part_quantity" type="number" min="1" class="input">
                    <x-input-error :messages="$errors->get('spare_part_quantity')" class="mt-1" />
                </div>
                <button type="submit" class="btn btn-secondary">Registrar</button>
            </form>
        </div>
    @endcan

    <div class="card elev-sm p-6">
        <h2 class="card-title m-0">Evidencia fotográfica</h2>

        <div class="mt-3 flex flex-wrap gap-3">
            @foreach ($workOrder->attachments as $attachment)
                <a href="{{ Storage::disk('public')->url($attachment->path) }}" target="_blank">
                    <img src="{{ Storage::disk('public')->url($attachment->path) }}" class="h-20 w-20 rounded-md object-cover ring-1 ring-neutral-800">
                </a>
            @endforeach
        </div>

        @can('update', $workOrder)
            <form wire:submit="uploadPhoto" class="mt-4 flex items-center gap-3">
                <input type="file" wire:model="newPhoto" class="text-sm text-neutral-400">
                <button type="submit" class="btn btn-secondary">Subir foto</button>
            </form>
            <x-input-error :messages="$errors->get('newPhoto')" class="mt-1" />
        @endcan
    </div>

    @if ($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto dialog-backdrop grid place-items-center p-4" wire:transition>
            <div class="fixed inset-0" wire:click="closeEditModal"></div>

            <div class="dialog relative">
                <h2 class="dialog-title">Editar reporte {{ $workOrder->order_number }}</h2>

                <form wire:submit="saveEdit" class="flex flex-col gap-4">
                    <div class="field">
                        <label>Tipo</label>
                        <select wire:model="edit_type" class="input">
                            @foreach ($types as $t)
                                <option value="{{ $t->value }}">{{ $t->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('edit_type')" class="mt-1" />
                    </div>

                    <div class="field">
                        <label>Prioridad</label>
                        <select wire:model="edit_priority" class="input">
                            @foreach ($priorities as $p)
                                <option value="{{ $p->value }}">{{ $p->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('edit_priority')" class="mt-1" />
                    </div>

                    <div class="field">
                        <label>Tipo de ejecución</label>
                        <select wire:model="edit_execution_type" class="input">
                            @foreach ($executionTypes as $e)
                                <option value="{{ $e->value }}">{{ $e->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('edit_execution_type')" class="mt-1" />
                    </div>

                    <div class="field">
                        <label>Descripción de la falla</label>
                        <textarea wire:model="edit_failure_description" rows="3" class="input"></textarea>
                        <x-input-error :messages="$errors->get('edit_failure_description')" class="mt-1" />
                    </div>

                    <div class="dialog-actions">
                        <button type="button" wire:click="closeEditModal" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
