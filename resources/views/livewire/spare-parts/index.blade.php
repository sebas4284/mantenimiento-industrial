<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-package text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Inventario de repuestos</h1>
    </div>
</x-slot>

<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <div class="flex flex-wrap items-center gap-3">
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Buscar por código o nombre..." class="input w-72">

            <label class="flex items-center gap-2 text-sm text-neutral-400">
                <input type="checkbox" wire:model.live="lowStockOnly" class="rounded border-neutral-700 bg-surface text-accent-500 focus:ring-accent-500">
                Solo stock bajo
            </label>
        </div>

        @can('create', \App\Models\SparePart::class)
            <button wire:click="create" class="btn btn-primary">
                <i class="ph ph-plus"></i> Nuevo repuesto
            </button>
        @endcan
    </div>

    <div class="card elev-sm p-4">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Repuesto</th>
                        <th>Planta</th>
                        <th>Stock</th>
                        <th>Mínimo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($spareParts as $part)
                        <tr wire:key="part-{{ $part->id }}">
                            <td>
                                <p class="font-medium text-ink m-0">{{ $part->name }}</p>
                                <p class="text-xs font-mono text-neutral-500 m-0">{{ $part->code }}</p>
                            </td>
                            <td class="text-muted">{{ $part->plant->name }}</td>
                            <td class="text-muted">{{ $part->stock_quantity }}</td>
                            <td class="text-muted">
                                {{ $part->minimum_stock }}
                                @if ($part->isLowStock())
                                    <span class="tag tag-accent">Stock bajo</span>
                                @endif
                            </td>
                            <td class="text-right whitespace-nowrap">
                                @can('update', $part)
                                    <button wire:click="edit({{ $part->id }})" class="btn-ghost text-xs">Editar</button>
                                @endcan
                                @can('delete', $part)
                                    <button wire:click="delete({{ $part->id }})" wire:confirm="¿Eliminar este repuesto?" class="text-xs text-neutral-400 hover:text-ink">Eliminar</button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-8">No hay repuestos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $spareParts->links() }}</div>

    @if ($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto dialog-backdrop grid place-items-center p-4" wire:transition>
        <div class="fixed inset-0" wire:click="closeModal"></div>

        <div class="dialog relative">
            <h2 class="dialog-title">{{ $editing ? 'Editar repuesto' : 'Nuevo repuesto' }}</h2>

            <form wire:submit="save" class="flex flex-col gap-4">
                <div class="field">
                    <label>Planta</label>
                    <select wire:model="plant_id" class="input">
                        <option value="">Selecciona una planta</option>
                        @foreach ($plants as $plant)
                            <option value="{{ $plant->id }}">{{ $plant->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('plant_id')" class="mt-1" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="field">
                        <label>Código</label>
                        <input wire:model="code" class="input">
                        <x-input-error :messages="$errors->get('code')" class="mt-1" />
                    </div>
                    <div class="field">
                        <label>Nombre</label>
                        <input wire:model="name" class="input">
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="field">
                        <label>Stock actual</label>
                        <input wire:model="stock_quantity" type="number" min="0" class="input">
                        <x-input-error :messages="$errors->get('stock_quantity')" class="mt-1" />
                    </div>
                    <div class="field">
                        <label>Stock mínimo</label>
                        <input wire:model="minimum_stock" type="number" min="0" class="input">
                        <x-input-error :messages="$errors->get('minimum_stock')" class="mt-1" />
                    </div>
                </div>

                <div class="dialog-actions">
                    <button type="button" wire:click="closeModal" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
