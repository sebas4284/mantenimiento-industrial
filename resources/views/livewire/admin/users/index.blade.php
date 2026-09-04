<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-users text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Usuarios</h1>
    </div>
</x-slot>

<div>
    <div class="flex justify-end mb-4">
        <button wire:click="create" class="btn btn-primary">
            <i class="ph ph-plus"></i> Nuevo usuario
        </button>
    </div>

    <div class="card elev-sm p-4">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Planta</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td class="font-medium text-ink">{{ $user->name }}</td>
                            <td class="text-muted">{{ $user->email }}</td>
                            <td><span class="tag tag-neutral">{{ $user->role->label() }}</span></td>
                            <td class="text-muted">{{ $user->plant?->name ?? 'Todas' }}</td>
                            <td class="text-right whitespace-nowrap">
                                <button wire:click="edit({{ $user->id }})" class="btn-ghost text-xs">Editar</button>
                                <button wire:click="delete({{ $user->id }})" wire:confirm="¿Eliminar este usuario?" class="text-xs text-neutral-400 hover:text-ink">Eliminar</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</div>

@if ($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto dialog-backdrop grid place-items-center p-4" wire:transition>
        <div class="fixed inset-0" wire:click="closeModal"></div>

        <div class="dialog relative">
            <h2 class="dialog-title">{{ $editing ? 'Editar usuario' : 'Nuevo usuario' }}</h2>

            <form wire:submit="save" class="flex flex-col gap-4">
                <div class="field">
                    <label>Nombre</label>
                    <input wire:model="name" class="input">
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div class="field">
                    <label>Correo</label>
                    <input wire:model="email" type="email" class="input">
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <div class="field">
                    <label>{{ $editing ? 'Nueva contraseña (opcional)' : 'Contraseña' }}</label>
                    <input wire:model="password" type="password" class="input">
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="field">
                        <label>Rol</label>
                        <select wire:model.live="role" class="input">
                            @foreach ($roles as $r)
                                <option value="{{ $r->value }}">{{ $r->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if (! \App\Enums\UserRole::from($role)->seesAllPlants())
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
                    @endif
                </div>

                <div class="dialog-actions">
                    <button type="button" wire:click="closeModal" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endif
