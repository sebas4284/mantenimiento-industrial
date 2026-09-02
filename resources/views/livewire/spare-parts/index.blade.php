<div>
    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-1 gap-3 min-w-[260px]">
                <input wire:model.live.debounce.400ms="search" type="text" placeholder="Buscar por código o nombre..."
                    class="w-full max-w-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">

                <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <input type="checkbox" wire:model.live="lowStockOnly" class="rounded border-gray-300 text-indigo-600 shadow-sm">
                    Solo stock bajo
                </label>
            </div>

            @can('create', \App\Models\SparePart::class)
                <button wire:click="create" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Nuevo repuesto
                </button>
            @endcan
        </div>

        <div class="mt-6 overflow-x-auto rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                        <th class="px-4 py-3">Repuesto</th>
                        <th class="px-4 py-3">Planta</th>
                        <th class="px-4 py-3">Stock</th>
                        <th class="px-4 py-3">Mínimo</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($spareParts as $part)
                        <tr wire:key="part-{{ $part->id }}">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $part->name }}</p>
                                <p class="text-xs font-mono text-gray-400">{{ $part->code }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $part->plant->name }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $part->stock_quantity }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                {{ $part->minimum_stock }}
                                @if ($part->isLowStock())
                                    <x-badge color="red">Stock bajo</x-badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-3">
                                @can('update', $part)
                                    <button wire:click="edit({{ $part->id }})" class="text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Editar</button>
                                @endcan
                                @can('delete', $part)
                                    <button wire:click="delete({{ $part->id }})" wire:confirm="¿Eliminar este repuesto?" class="text-xs font-medium text-red-600 hover:text-red-500">Eliminar</button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay repuestos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $spareParts->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:transition>
            <div class="fixed inset-0 bg-gray-900/50" wire:click="closeModal"></div>

            <div class="relative mx-auto mt-16 w-full max-w-md rounded-xl bg-white dark:bg-gray-800 p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $editing ? 'Editar repuesto' : 'Nuevo repuesto' }}</h2>

                <form wire:submit="save" class="mt-4 space-y-4">
                    <div>
                        <x-input-label for="plant_id" value="Planta" />
                        <select wire:model="plant_id" id="plant_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                            <option value="">Selecciona una planta</option>
                            @foreach ($plants as $plant)
                                <option value="{{ $plant->id }}">{{ $plant->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('plant_id')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="code" value="Código" />
                            <x-text-input wire:model="code" id="code" class="mt-1 block w-full text-sm" />
                            <x-input-error :messages="$errors->get('code')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="name" value="Nombre" />
                            <x-text-input wire:model="name" id="name" class="mt-1 block w-full text-sm" />
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="stock_quantity" value="Stock actual" />
                            <x-text-input wire:model="stock_quantity" id="stock_quantity" type="number" min="0" class="mt-1 block w-full text-sm" />
                            <x-input-error :messages="$errors->get('stock_quantity')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="minimum_stock" value="Stock mínimo" />
                            <x-text-input wire:model="minimum_stock" id="minimum_stock" type="number" min="0" class="mt-1 block w-full text-sm" />
                            <x-input-error :messages="$errors->get('minimum_stock')" class="mt-1" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-secondary-button type="button" wire:click="closeModal">Cancelar</x-secondary-button>
                        <x-primary-button>Guardar</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
