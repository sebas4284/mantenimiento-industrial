<div>
    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Checklists reutilizables</h1>

            @can('create', \App\Models\ChecklistTemplate::class)
                <button wire:click="create" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Nuevo checklist
                </button>
            @endcan
        </div>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            @forelse ($templates as $template)
                <div wire:key="template-{{ $template->id }}" class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $template->name }}</h3>
                            <p class="text-xs text-gray-400">{{ $template->items_count }} puntos de verificación</p>
                        </div>
                        <div class="space-x-3">
                            @can('update', $template)
                                <button wire:click="edit({{ $template->id }})" class="text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Editar</button>
                            @endcan
                            @can('delete', $template)
                                <button wire:click="delete({{ $template->id }})" wire:confirm="¿Eliminar este checklist?" class="text-xs font-medium text-red-600 hover:text-red-500">Eliminar</button>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <p class="col-span-full text-center text-sm text-gray-500 dark:text-gray-400 py-12">No hay checklists registrados.</p>
            @endforelse
        </div>

        <div class="mt-4">{{ $templates->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:transition>
            <div class="fixed inset-0 bg-gray-900/50" wire:click="closeModal"></div>

            <div class="relative mx-auto mt-12 w-full max-w-lg rounded-xl bg-white dark:bg-gray-800 p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $editing ? 'Editar checklist' : 'Nuevo checklist' }}</h2>

                <form wire:submit="save" class="mt-4 space-y-4">
                    <div>
                        <x-input-label for="name" value="Nombre del checklist" />
                        <x-text-input wire:model="name" id="name" class="mt-1 block w-full text-sm" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label value="Puntos de verificación" />
                        <div class="mt-2 space-y-2">
                            @foreach ($items as $index => $item)
                                <div class="flex gap-2" wire:key="item-{{ $index }}">
                                    <x-text-input wire:model="items.{{ $index }}.label" class="block w-full text-sm" placeholder="Ej. Revisar nivel de aceite" />
                                    <button type="button" wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-400 px-2">&times;</button>
                                </div>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('items')" class="mt-1" />

                        <button type="button" wire:click="addItem" class="mt-2 text-xs font-medium text-indigo-600 hover:text-indigo-500">+ Agregar punto</button>
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
