<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-list-checks text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Checklists reutilizables</h1>
    </div>
</x-slot>

<div>
    <div class="flex justify-end mb-4">
        @can('create', \App\Models\ChecklistTemplate::class)
            <button wire:click="create" class="btn btn-primary">
                <i class="ph ph-plus"></i> Nuevo checklist
            </button>
        @endcan
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @forelse ($templates as $template)
            <div wire:key="template-{{ $template->id }}" class="card elev-sm p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-medium text-ink m-0">{{ $template->name }}</h3>
                        <p class="text-xs text-neutral-500 m-0">{{ $template->items_count }} puntos de verificación</p>
                    </div>
                    <div class="flex gap-3">
                        @can('update', $template)
                            <button wire:click="edit({{ $template->id }})" class="btn-ghost text-xs">Editar</button>
                        @endcan
                        @can('delete', $template)
                            <button wire:click="delete({{ $template->id }})" wire:confirm="¿Eliminar este checklist?" class="text-xs text-neutral-400 hover:text-ink">Eliminar</button>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <p class="col-span-full text-center text-muted py-12">No hay checklists registrados.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $templates->links() }}</div>
</div>

@if ($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto dialog-backdrop grid place-items-center p-4" wire:transition>
        <div class="fixed inset-0" wire:click="closeModal"></div>

        <div class="dialog relative">
            <h2 class="dialog-title">{{ $editing ? 'Editar checklist' : 'Nuevo checklist' }}</h2>

            <form wire:submit="save" class="flex flex-col gap-4">
                <div class="field">
                    <label>Nombre del checklist</label>
                    <input wire:model="name" class="input">
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div class="field">
                    <label>Puntos de verificación</label>
                    <div class="mt-1 flex flex-col gap-2">
                        @foreach ($items as $index => $item)
                            <div class="flex gap-2" wire:key="item-{{ $index }}">
                                <input wire:model="items.{{ $index }}.label" class="input" placeholder="Ej. Revisar nivel de aceite">
                                <button type="button" wire:click="removeItem({{ $index }})" class="text-neutral-400 hover:text-ink px-2">&times;</button>
                            </div>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('items')" class="mt-1" />

                    <button type="button" wire:click="addItem" class="btn-ghost text-xs mt-2">+ Agregar punto</button>
                </div>

                <div class="dialog-actions">
                    <button type="button" wire:click="closeModal" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endif
