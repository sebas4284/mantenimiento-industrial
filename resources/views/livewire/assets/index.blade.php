<div>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-1 gap-3 min-w-[260px]">
                <input wire:model.live.debounce.400ms="search" type="text" placeholder="Buscar por código o nombre..."
                    class="w-full max-w-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">

                <select wire:model.live="areaFilter" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm text-sm">
                    <option value="">Todas las áreas</option>
                    @foreach ($areas as $area)
                        <option value="{{ $area->id }}">{{ $area->plant->name }} — {{ $area->name }}</option>
                    @endforeach
                </select>
            </div>

            @can('create', \App\Models\Asset::class)
                <button wire:click="create" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Nuevo activo
                </button>
            @endcan
        </div>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($assets as $asset)
                <div wire:key="asset-{{ $asset->id }}" class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-5 flex flex-col gap-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-mono text-gray-400">{{ $asset->code }}</p>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                                <a href="{{ route('assets.show', $asset) }}" wire:navigate class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ $asset->name }}</a>
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $asset->area->plant->name }} — {{ $asset->area->name }}</p>
                        </div>
                        @if ($asset->qr_code_path)
                            <img src="{{ Storage::disk('public')->url($asset->qr_code_path) }}" alt="QR {{ $asset->code }}" class="h-16 w-16 rounded bg-white p-1 ring-1 ring-gray-200">
                        @endif
                    </div>

                    @php $assetDisplayStatus = $asset->computedStatus($asset->active_work_orders_count > 0); @endphp
                    <div class="flex flex-wrap gap-2">
                        <x-badge :color="$assetDisplayStatus->color()">{{ $assetDisplayStatus->label() }}</x-badge>
                        <x-badge color="zinc">Criticidad {{ $asset->criticality->value }}</x-badge>
                    </div>

                    <div class="mt-auto flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700">
                        @if ($asset->qr_code_path)
                            <a href="{{ Storage::disk('public')->url($asset->qr_code_path) }}" target="_blank" class="text-xs font-medium text-indigo-600 hover:text-indigo-500">Ver / imprimir QR</a>
                        @else
                            <span class="text-xs text-gray-400">Sin QR</span>
                        @endif

                        <div class="flex gap-3">
                            @can('update', $asset)
                                <button wire:click="edit({{ $asset->id }})" class="text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Editar</button>
                            @endcan
                            @can('delete', $asset)
                                <button wire:click="delete({{ $asset->id }})" wire:confirm="¿Eliminar este activo?" class="text-xs font-medium text-red-600 hover:text-red-500">Eliminar</button>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <p class="col-span-full text-center text-sm text-gray-500 dark:text-gray-400 py-12">No hay activos registrados todavía.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $assets->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:transition>
            <div class="fixed inset-0 bg-gray-900/50" wire:click="closeModal"></div>

            <div class="relative mx-auto mt-12 w-full max-w-xl rounded-xl bg-white dark:bg-gray-800 p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ $editing ? 'Editar activo' : 'Nuevo activo' }}
                </h2>

                <form wire:submit="save" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-input-label for="area_id" value="Área" />
                        <select wire:model="area_id" id="area_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                            <option value="">Selecciona un área</option>
                            @foreach ($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->plant->name }} — {{ $area->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('area_id')" class="mt-1" />
                    </div>

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

                    <div>
                        <x-input-label for="manufacturer" value="Fabricante" />
                        <x-text-input wire:model="manufacturer" id="manufacturer" class="mt-1 block w-full text-sm" />
                    </div>

                    <div>
                        <x-input-label for="model" value="Modelo" />
                        <x-text-input wire:model="model" id="model" class="mt-1 block w-full text-sm" />
                    </div>

                    <div>
                        <x-input-label for="serial_number" value="Número de serie" />
                        <x-text-input wire:model="serial_number" id="serial_number" class="mt-1 block w-full text-sm" />
                    </div>

                    <div>
                        <x-input-label for="criticality" value="Criticidad" />
                        <select wire:model="criticality" id="criticality" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                            @foreach ($criticalities as $c)
                                <option value="{{ $c->value }}">{{ $c->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="status" value="Estado" />
                        <select wire:model="status" id="status" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                            @foreach ($statuses as $s)
                                <option value="{{ $s->value }}">{{ $s->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <x-input-label for="photo" value="Foto (opcional)" />
                        <input type="file" wire:model="photo" id="photo" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400">
                        <x-input-error :messages="$errors->get('photo')" class="mt-1" />
                    </div>

                    <div class="sm:col-span-2 flex justify-end gap-3 pt-2">
                        <x-secondary-button type="button" wire:click="closeModal">Cancelar</x-secondary-button>
                        <x-primary-button>Guardar</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
