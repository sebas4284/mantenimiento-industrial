<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-gear-six text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Activos</h1>
    </div>
</x-slot>

<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <div class="flex flex-wrap items-center gap-3">
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Buscar por código o nombre..." class="input w-72">

            <select wire:model.live="areaFilter" class="input w-auto">
                <option value="">Todas las áreas</option>
                @foreach ($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->plant->name }} — {{ $area->name }}</option>
                @endforeach
            </select>
        </div>

        @can('create', \App\Models\Asset::class)
            <button wire:click="create" class="btn btn-primary">
                <i class="ph ph-plus"></i> Nuevo activo
            </button>
        @endcan
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($assets as $asset)
            <div wire:key="asset-{{ $asset->id }}" class="card elev-sm p-5 gap-3">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-mono text-neutral-500 m-0">{{ $asset->code }}</p>
                        <h3 class="font-medium text-ink m-0">
                            <a href="{{ route('assets.show', $asset) }}" wire:navigate class="text-ink hover:text-accent-300">{{ $asset->name }}</a>
                        </h3>
                        <p class="text-xs text-neutral-500 m-0">{{ $asset->area->plant->name }} — {{ $asset->area->name }}</p>
                    </div>
                    @if ($asset->qr_code_path)
                        <img src="{{ Storage::disk('public')->url($asset->qr_code_path) }}" alt="QR {{ $asset->code }}" class="h-16 w-16 rounded bg-white p-1">
                    @endif
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="tag tag-{{ $asset->status->tagVariant() }}">{{ $asset->status->label() }}</span>
                    <span class="tag tag-neutral">Criticidad {{ $asset->criticality->value }}</span>
                </div>

                <div class="mt-auto flex items-center justify-between pt-2 border-t border-neutral-800">
                    @if ($asset->qr_code_path)
                        <a href="{{ Storage::disk('public')->url($asset->qr_code_path) }}" target="_blank" class="text-xs text-accent-300">Ver / imprimir QR</a>
                    @else
                        <span class="text-xs text-neutral-500">Sin QR</span>
                    @endif

                    <div class="flex gap-3">
                        @can('update', $asset)
                            <button wire:click="edit({{ $asset->id }})" class="btn-ghost text-xs">Editar</button>
                        @endcan
                        @can('delete', $asset)
                            <button wire:click="delete({{ $asset->id }})" wire:confirm="¿Eliminar este activo?" class="text-xs text-neutral-400 hover:text-ink">Eliminar</button>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <p class="col-span-full text-center text-muted py-12">No hay activos registrados todavía.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $assets->links() }}</div>
</div>

@if ($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto dialog-backdrop grid place-items-center p-4" wire:transition>
        <div class="fixed inset-0" wire:click="closeModal"></div>

        <div class="dialog relative" style="width:min(560px, 100%);">
            <h2 class="dialog-title">{{ $editing ? 'Editar activo' : 'Nuevo activo' }}</h2>

            <form wire:submit="save" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="field sm:col-span-2">
                    <label>Área</label>
                    <select wire:model="area_id" class="input">
                        <option value="">Selecciona un área</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id }}">{{ $area->plant->name }} — {{ $area->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('area_id')" class="mt-1" />
                </div>

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

                <div class="field">
                    <label>Fabricante</label>
                    <input wire:model="manufacturer" class="input">
                </div>

                <div class="field">
                    <label>Modelo</label>
                    <input wire:model="model" class="input">
                </div>

                <div class="field">
                    <label>Número de serie</label>
                    <input wire:model="serial_number" class="input">
                </div>

                <div class="field">
                    <label>Criticidad</label>
                    <select wire:model="criticality" class="input">
                        @foreach ($criticalities as $c)
                            <option value="{{ $c->value }}">{{ $c->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label>Estado</label>
                    <select wire:model="status" class="input">
                        @foreach ($statuses as $s)
                            <option value="{{ $s->value }}">{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field sm:col-span-2">
                    <label>Foto (opcional)</label>
                    <input type="file" wire:model="photo" class="input">
                    <x-input-error :messages="$errors->get('photo')" class="mt-1" />
                </div>

                <div class="dialog-actions sm:col-span-2">
                    <button type="button" wire:click="closeModal" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endif
