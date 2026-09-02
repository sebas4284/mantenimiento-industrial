<div>
    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Buscar por nombre o especialidad..."
                class="w-full max-w-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">

            @can('create', \App\Models\Provider::class)
                <button wire:click="create" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Nuevo proveedor
                </button>
            @endcan
        </div>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($providers as $provider)
                <div wire:key="provider-{{ $provider->id }}" class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-5 flex flex-col gap-2">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                                <a href="{{ route('providers.show', $provider) }}" wire:navigate class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ $provider->name }}</a>
                            </h3>
                            @if ($provider->specialty)
                                <x-badge color="zinc">{{ $provider->specialty }}</x-badge>
                            @endif
                        </div>
                    </div>

                    <div class="text-sm text-gray-600 dark:text-gray-300 space-y-0.5">
                        @if ($provider->contact_name)
                            <p>{{ $provider->contact_name }}</p>
                        @endif
                        @if ($provider->phone)
                            <p>{{ $provider->phone }}</p>
                        @endif
                        @if ($provider->email)
                            <p>{{ $provider->email }}</p>
                        @endif
                    </div>

                    <div class="mt-auto flex items-center justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                        @can('update', $provider)
                            <button wire:click="edit({{ $provider->id }})" class="text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Editar</button>
                        @endcan
                        @can('delete', $provider)
                            <button wire:click="delete({{ $provider->id }})" wire:confirm="¿Eliminar este proveedor?" class="text-xs font-medium text-red-600 hover:text-red-500">Eliminar</button>
                        @endcan
                    </div>
                </div>
            @empty
                <p class="col-span-full text-center text-sm text-gray-500 dark:text-gray-400 py-12">No hay proveedores registrados todavía.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $providers->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:transition>
            <div class="fixed inset-0 bg-gray-900/50" wire:click="closeModal"></div>

            <div class="relative mx-auto mt-12 w-full max-w-lg rounded-xl bg-white dark:bg-gray-800 p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $editing ? 'Editar proveedor' : 'Nuevo proveedor' }}</h2>

                <form wire:submit="save" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-input-label for="name" value="Nombre de la empresa" />
                        <x-text-input wire:model="name" id="name" class="mt-1 block w-full text-sm" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="contact_name" value="Persona de contacto" />
                        <x-text-input wire:model="contact_name" id="contact_name" class="mt-1 block w-full text-sm" />
                    </div>

                    <div>
                        <x-input-label for="specialty" value="Especialidad" />
                        <x-text-input wire:model="specialty" id="specialty" class="mt-1 block w-full text-sm" />
                    </div>

                    <div>
                        <x-input-label for="phone" value="Teléfono" />
                        <x-text-input wire:model="phone" id="phone" class="mt-1 block w-full text-sm" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Correo" />
                        <x-text-input wire:model="email" id="email" type="email" class="mt-1 block w-full text-sm" />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-input-label for="address" value="Dirección" />
                        <x-text-input wire:model="address" id="address" class="mt-1 block w-full text-sm" />
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
