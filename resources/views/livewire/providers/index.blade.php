<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-truck text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Proveedores</h1>
    </div>
</x-slot>

<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <input wire:model.live.debounce.400ms="search" type="text" placeholder="Buscar por nombre o especialidad..." class="input max-w-sm">

        @can('create', \App\Models\Provider::class)
            <button wire:click="create" class="btn btn-primary">
                <i class="ph ph-plus"></i> Nuevo proveedor
            </button>
        @endcan
    </div>

    <div class="card elev-sm p-4">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Proveedor</th>
                        <th>Especialidad</th>
                        <th>Contacto</th>
                        <th>Órdenes activas</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($providers as $provider)
                        <tr wire:key="provider-{{ $provider->id }}">
                            <td><a href="{{ route('providers.show', $provider) }}" wire:navigate class="text-accent-300 hover:text-accent-200">{{ $provider->name }}</a></td>
                            <td class="text-muted">{{ $provider->specialty ?? '—' }}</td>
                            <td class="text-muted">{{ $provider->contact_name ?? $provider->email ?? '—' }}</td>
                            <td>{{ $provider->active_work_orders_count }}</td>
                            <td class="text-right whitespace-nowrap">
                                @can('update', $provider)
                                    <button wire:click="edit({{ $provider->id }})" class="btn-ghost text-xs">Editar</button>
                                @endcan
                                @can('delete', $provider)
                                    <button wire:click="delete({{ $provider->id }})" wire:confirm="¿Eliminar este proveedor?" class="text-xs text-neutral-400 hover:text-ink">Eliminar</button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-8">No hay proveedores registrados todavía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $providers->links() }}</div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto dialog-backdrop grid place-items-center p-4" wire:transition>
            <div class="fixed inset-0" wire:click="closeModal"></div>

            <div class="dialog relative">
                <h2 class="dialog-title">{{ $editing ? 'Editar proveedor' : 'Nuevo proveedor' }}</h2>

                <form wire:submit="save" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="field sm:col-span-2">
                        <label>Nombre de la empresa</label>
                        <input wire:model="name" class="input">
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div class="field">
                        <label>Persona de contacto</label>
                        <input wire:model="contact_name" class="input">
                    </div>

                    <div class="field">
                        <label>Especialidad</label>
                        <input wire:model="specialty" class="input">
                    </div>

                    <div class="field">
                        <label>Teléfono</label>
                        <input wire:model="phone" class="input">
                    </div>

                    <div class="field">
                        <label>Correo</label>
                        <input wire:model="email" type="email" class="input">
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div class="field sm:col-span-2">
                        <label>Dirección</label>
                        <input wire:model="address" class="input">
                    </div>

                    <div class="dialog-actions sm:col-span-2">
                        <button type="button" wire:click="closeModal" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
