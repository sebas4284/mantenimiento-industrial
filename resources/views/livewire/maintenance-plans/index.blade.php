<div>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Planes de mantenimiento preventivo</h1>

            @can('create', \App\Models\MaintenancePlan::class)
                <button wire:click="create" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Nuevo plan
                </button>
            @endcan
        </div>

        <div class="mt-6 overflow-x-auto rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                        <th class="px-4 py-3">Plan</th>
                        <th class="px-4 py-3">Activo</th>
                        <th class="px-4 py-3">Frecuencia</th>
                        <th class="px-4 py-3">Próxima fecha</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($plans as $plan)
                        <tr wire:key="plan-{{ $plan->id }}">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $plan->name }}</p>
                                @if ($plan->checklistTemplate)
                                    <p class="text-xs text-gray-400">Checklist: {{ $plan->checklistTemplate->name }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                {{ $plan->asset->code }} — {{ $plan->asset->name }}
                                <p class="text-xs text-gray-400">{{ $plan->asset->area->plant->name }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">cada {{ $plan->frequency_days }} días</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                {{ $plan->next_due_date->format('d/m/Y') }}
                                @if ($plan->next_due_date->isPast())
                                    <x-badge color="red">Vencido</x-badge>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <x-badge :color="$plan->active ? 'green' : 'zinc'">{{ $plan->active ? 'Activo' : 'Inactivo' }}</x-badge>
                            </td>
                            <td class="px-4 py-3 text-right space-x-3">
                                @can('update', $plan)
                                    <button wire:click="edit({{ $plan->id }})" class="text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Editar</button>
                                @endcan
                                @can('delete', $plan)
                                    <button wire:click="delete({{ $plan->id }})" wire:confirm="¿Eliminar este plan?" class="text-xs font-medium text-red-600 hover:text-red-500">Eliminar</button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay planes registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $plans->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:transition>
            <div class="fixed inset-0 bg-gray-900/50" wire:click="closeModal"></div>

            <div class="relative mx-auto mt-12 w-full max-w-lg rounded-xl bg-white dark:bg-gray-800 p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $editing ? 'Editar plan' : 'Nuevo plan' }}</h2>

                <form wire:submit="save" class="mt-4 space-y-4">
                    <div>
                        <x-input-label for="name" value="Nombre del plan" />
                        <x-text-input wire:model="name" id="name" class="mt-1 block w-full text-sm" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

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
                        <x-input-label for="checklist_template_id" value="Checklist (opcional)" />
                        <select wire:model="checklist_template_id" id="checklist_template_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                            <option value="">Sin checklist</option>
                            @foreach ($templates as $template)
                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="frequency_days" value="Frecuencia (días)" />
                            <x-text-input wire:model="frequency_days" id="frequency_days" type="number" min="1" class="mt-1 block w-full text-sm" />
                            <x-input-error :messages="$errors->get('frequency_days')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="next_due_date" value="Próxima fecha" />
                            <x-text-input wire:model="next_due_date" id="next_due_date" type="date" class="mt-1 block w-full text-sm" />
                            <x-input-error :messages="$errors->get('next_due_date')" class="mt-1" />
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" wire:model="active" class="rounded border-gray-300 text-indigo-600 shadow-sm">
                        Plan activo
                    </label>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-secondary-button type="button" wire:click="closeModal">Cancelar</x-secondary-button>
                        <x-primary-button>Guardar</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
