<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-calendar-check text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Planes de mantenimiento preventivo</h1>
    </div>
</x-slot>

<div>
    <div class="flex justify-end mb-4">
        @can('create', \App\Models\MaintenancePlan::class)
            <button wire:click="create" class="btn btn-primary">
                <i class="ph ph-plus"></i> Nuevo plan
            </button>
        @endcan
    </div>

    <div class="card elev-sm p-4">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Activo</th>
                        <th>Frecuencia</th>
                        <th>Próxima fecha</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($plans as $plan)
                        <tr wire:key="plan-{{ $plan->id }}">
                            <td>
                                <p class="font-medium text-ink m-0">{{ $plan->name }}</p>
                                @if ($plan->checklistTemplate)
                                    <p class="text-xs text-neutral-500 m-0">Checklist: {{ $plan->checklistTemplate->name }}</p>
                                @endif
                            </td>
                            <td class="text-muted">
                                {{ $plan->asset->code }} — {{ $plan->asset->name }}
                                <p class="text-xs text-neutral-500 m-0">{{ $plan->asset->area->plant->name }}</p>
                            </td>
                            <td class="text-muted">cada {{ $plan->frequency_days }} días</td>
                            <td class="text-muted">
                                {{ $plan->next_due_date->format('d/m/Y') }}
                                @if ($plan->next_due_date->isPast())
                                    <span class="tag tag-accent">Vencido</span>
                                @endif
                            </td>
                            <td>
                                <span class="tag {{ $plan->active ? 'tag-accent' : 'tag-neutral' }}">{{ $plan->active ? 'Activo' : 'Inactivo' }}</span>
                            </td>
                            <td class="text-right whitespace-nowrap">
                                @can('update', $plan)
                                    <button wire:click="edit({{ $plan->id }})" class="btn-ghost text-xs">Editar</button>
                                @endcan
                                @can('delete', $plan)
                                    <button wire:click="delete({{ $plan->id }})" wire:confirm="¿Eliminar este plan?" class="text-xs text-neutral-400 hover:text-ink">Eliminar</button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-8">No hay planes registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $plans->links() }}</div>

    @if ($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto dialog-backdrop grid place-items-center p-4" wire:transition>
        <div class="fixed inset-0" wire:click="closeModal"></div>

        <div class="dialog relative">
            <h2 class="dialog-title">{{ $editing ? 'Editar plan' : 'Nuevo plan' }}</h2>

            <form wire:submit="save" class="flex flex-col gap-4">
                <div class="field">
                    <label>Nombre del plan</label>
                    <input wire:model="name" class="input">
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

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
                    <label>Checklist (opcional)</label>
                    <select wire:model="checklist_template_id" class="input">
                        <option value="">Sin checklist</option>
                        @foreach ($templates as $template)
                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="field">
                        <label>Frecuencia (días)</label>
                        <input wire:model="frequency_days" type="number" min="1" class="input">
                        <x-input-error :messages="$errors->get('frequency_days')" class="mt-1" />
                    </div>
                    <div class="field">
                        <label>Próxima fecha</label>
                        <input wire:model="next_due_date" type="date" class="input">
                        <x-input-error :messages="$errors->get('next_due_date')" class="mt-1" />
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-neutral-400">
                    <input type="checkbox" wire:model="active" class="rounded border-neutral-700 bg-surface text-accent-500 focus:ring-accent-500">
                    Plan activo
                </label>

                <div class="dialog-actions">
                    <button type="button" wire:click="closeModal" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
