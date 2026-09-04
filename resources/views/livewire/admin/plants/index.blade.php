<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-factory text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Plantas</h1>
    </div>
</x-slot>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <div class="flex justify-end mb-3">
            <button wire:click="createPlant" class="btn-ghost text-sm">+ Nueva planta</button>
        </div>

        <div class="flex flex-col gap-2">
            @foreach ($plants as $plant)
                <div wire:key="plant-{{ $plant->id }}"
                    class="card elev-sm p-4 cursor-pointer {{ $selectedPlantId === $plant->id ? 'border border-accent-600' : '' }}"
                    wire:click="selectPlant({{ $plant->id }})">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-ink m-0">{{ $plant->name }} <span class="font-mono text-xs text-neutral-500">({{ $plant->code }})</span></p>
                            <p class="text-xs text-neutral-500 m-0">{{ $plant->location }} · {{ $plant->areas_count }} áreas</p>
                        </div>
                        <div class="flex gap-3">
                            <button wire:click.stop="editPlant({{ $plant->id }})" class="btn-ghost text-xs">Editar</button>
                            <button wire:click.stop="deletePlant({{ $plant->id }})" wire:confirm="¿Eliminar esta planta y todas sus áreas/activos?" class="text-xs text-neutral-400 hover:text-ink">Eliminar</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="card-title m-0">Áreas</h2>
            @if ($selectedPlantId)
                <button wire:click="createArea" class="btn-ghost text-sm">+ Nueva área</button>
            @endif
        </div>

        @if (! $selectedPlantId)
            <p class="text-sm text-neutral-400">Selecciona una planta para gestionar sus áreas.</p>
        @else
            <div class="flex flex-col gap-2">
                @forelse ($areas as $area)
                    <div wire:key="area-{{ $area->id }}" class="card elev-sm p-4 flex-row items-center justify-between">
                        <div>
                            <p class="font-medium text-ink m-0">{{ $area->name }}</p>
                            <p class="text-xs text-neutral-500 m-0">{{ $area->assets_count }} activos</p>
                        </div>
                        <div class="flex gap-3">
                            <button wire:click="editArea({{ $area->id }})" class="btn-ghost text-xs">Editar</button>
                            <button wire:click="deleteArea({{ $area->id }})" wire:confirm="¿Eliminar esta área y sus activos?" class="text-xs text-neutral-400 hover:text-ink">Eliminar</button>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-neutral-400">Esta planta no tiene áreas todavía.</p>
                @endforelse
            </div>
        @endif
    </div>
</div>

@if ($showPlantModal)
    <div class="fixed inset-0 z-50 overflow-y-auto dialog-backdrop grid place-items-center p-4" wire:transition>
        <div class="fixed inset-0" wire:click="$set('showPlantModal', false)"></div>
        <div class="dialog relative">
            <h2 class="dialog-title">{{ $editingPlant ? 'Editar planta' : 'Nueva planta' }}</h2>
            <form wire:submit="savePlant" class="flex flex-col gap-4">
                <div class="field">
                    <label>Nombre</label>
                    <input wire:model="plantName" class="input">
                    <x-input-error :messages="$errors->get('plantName')" class="mt-1" />
                </div>
                <div class="field">
                    <label>Ubicación</label>
                    <input wire:model="plantLocation" class="input">
                </div>
                <div class="dialog-actions">
                    <button type="button" wire:click="$set('showPlantModal', false)" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endif

@if ($showAreaModal)
    <div class="fixed inset-0 z-50 overflow-y-auto dialog-backdrop grid place-items-center p-4" wire:transition>
        <div class="fixed inset-0" wire:click="$set('showAreaModal', false)"></div>
        <div class="dialog relative">
            <h2 class="dialog-title">{{ $editingArea ? 'Editar área' : 'Nueva área' }}</h2>
            <form wire:submit="saveArea" class="flex flex-col gap-4">
                <div class="field">
                    <label>Nombre</label>
                    <input wire:model="areaName" class="input">
                    <x-input-error :messages="$errors->get('areaName')" class="mt-1" />
                </div>
                <div class="dialog-actions">
                    <button type="button" wire:click="$set('showAreaModal', false)" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endif
