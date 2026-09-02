<div>
    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <div class="flex items-center justify-between">
                <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Plantas</h1>
                <button wire:click="createPlant" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">+ Nueva planta</button>
            </div>

            <div class="mt-4 space-y-2">
                @foreach ($plants as $plant)
                    <div wire:key="plant-{{ $plant->id }}"
                        class="rounded-lg p-4 cursor-pointer ring-1 {{ $selectedPlantId === $plant->id ? 'ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'ring-gray-200 dark:ring-gray-700 bg-white dark:bg-gray-800' }}"
                        wire:click="selectPlant({{ $plant->id }})">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $plant->name }} <span class="font-mono text-xs text-gray-400">({{ $plant->code }})</span></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $plant->location }} · {{ $plant->areas_count }} áreas</p>
                            </div>
                            <div class="space-x-3">
                                <button wire:click.stop="editPlant({{ $plant->id }})" class="text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Editar</button>
                                <button wire:click.stop="deletePlant({{ $plant->id }})" wire:confirm="¿Eliminar esta planta y todas sus áreas/activos?" class="text-xs font-medium text-red-600 hover:text-red-500">Eliminar</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Áreas</h2>
                @if ($selectedPlantId)
                    <button wire:click="createArea" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">+ Nueva área</button>
                @endif
            </div>

            @if (! $selectedPlantId)
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Selecciona una planta para gestionar sus áreas.</p>
            @else
                <div class="mt-4 space-y-2">
                    @forelse ($areas as $area)
                        <div wire:key="area-{{ $area->id }}" class="rounded-lg p-4 ring-1 ring-gray-200 dark:ring-gray-700 bg-white dark:bg-gray-800 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $area->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $area->assets_count }} activos</p>
                            </div>
                            <div class="space-x-3">
                                <button wire:click="editArea({{ $area->id }})" class="text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Editar</button>
                                <button wire:click="deleteArea({{ $area->id }})" wire:confirm="¿Eliminar esta área y sus activos?" class="text-xs font-medium text-red-600 hover:text-red-500">Eliminar</button>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">Esta planta no tiene áreas todavía.</p>
                    @endforelse
                </div>
            @endif
        </div>
    </div>

    @if ($showPlantModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:transition>
            <div class="fixed inset-0 bg-gray-900/50" wire:click="$set('showPlantModal', false)"></div>
            <div class="relative mx-auto mt-24 w-full max-w-md rounded-xl bg-white dark:bg-gray-800 p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $editingPlant ? 'Editar planta' : 'Nueva planta' }}</h2>
                <form wire:submit="savePlant" class="mt-4 space-y-4">
                    <div>
                        <x-input-label for="plantName" value="Nombre" />
                        <x-text-input wire:model="plantName" id="plantName" class="mt-1 block w-full text-sm" />
                        <x-input-error :messages="$errors->get('plantName')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="plantLocation" value="Ubicación" />
                        <x-text-input wire:model="plantLocation" id="plantLocation" class="mt-1 block w-full text-sm" />
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <x-secondary-button type="button" wire:click="$set('showPlantModal', false)">Cancelar</x-secondary-button>
                        <x-primary-button>Guardar</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showAreaModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:transition>
            <div class="fixed inset-0 bg-gray-900/50" wire:click="$set('showAreaModal', false)"></div>
            <div class="relative mx-auto mt-24 w-full max-w-md rounded-xl bg-white dark:bg-gray-800 p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $editingArea ? 'Editar área' : 'Nueva área' }}</h2>
                <form wire:submit="saveArea" class="mt-4 space-y-4">
                    <div>
                        <x-input-label for="areaName" value="Nombre" />
                        <x-text-input wire:model="areaName" id="areaName" class="mt-1 block w-full text-sm" />
                        <x-input-error :messages="$errors->get('areaName')" class="mt-1" />
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <x-secondary-button type="button" wire:click="$set('showAreaModal', false)">Cancelar</x-secondary-button>
                        <x-primary-button>Guardar</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
