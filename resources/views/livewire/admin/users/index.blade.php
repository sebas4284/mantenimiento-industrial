<div>
    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Usuarios</h1>

            <button wire:click="create" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Nuevo usuario
            </button>
        </div>

        <div class="mt-6 overflow-x-auto rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Correo</th>
                        <th class="px-4 py-3">Rol</th>
                        <th class="px-4 py-3">Planta</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($users as $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $user->email }}</td>
                            <td class="px-4 py-3"><x-badge color="zinc">{{ $user->role->label() }}</x-badge></td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $user->plant?->name ?? 'Todas' }}</td>
                            <td class="px-4 py-3 text-right space-x-3">
                                <button wire:click="edit({{ $user->id }})" class="text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Editar</button>
                                <button wire:click="delete({{ $user->id }})" wire:confirm="¿Eliminar este usuario?" class="text-xs font-medium text-red-600 hover:text-red-500">Eliminar</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $users->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:transition>
            <div class="fixed inset-0 bg-gray-900/50" wire:click="closeModal"></div>

            <div class="relative mx-auto mt-12 w-full max-w-lg rounded-xl bg-white dark:bg-gray-800 p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $editing ? 'Editar usuario' : 'Nuevo usuario' }}</h2>

                <form wire:submit="save" class="mt-4 space-y-4">
                    <div>
                        <x-input-label for="name" value="Nombre" />
                        <x-text-input wire:model="name" id="name" class="mt-1 block w-full text-sm" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Correo" />
                        <x-text-input wire:model="email" id="email" type="email" class="mt-1 block w-full text-sm" />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="password" :value="$editing ? 'Nueva contraseña (opcional)' : 'Contraseña'" />
                        <x-text-input wire:model="password" id="password" type="password" class="mt-1 block w-full text-sm" />
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="role" value="Rol" />
                            <select wire:model.live="role" id="role" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                                @foreach ($roles as $r)
                                    <option value="{{ $r->value }}">{{ $r->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if (! \App\Enums\UserRole::from($role)->seesAllPlants())
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
                        @endif
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
