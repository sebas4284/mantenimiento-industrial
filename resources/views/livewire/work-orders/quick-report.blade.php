<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-warning-circle text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Reportar falla</h1>
    </div>
</x-slot>

<div class="max-w-md">
    <div class="card elev-sm p-6">
        <p class="text-xs font-mono text-neutral-500 m-0">{{ $asset->code }}</p>
        <h2 class="text-lg text-ink m-0">{{ $asset->name }}</h2>
        <p class="text-sm text-neutral-400 m-0">{{ $asset->area->plant->name }} — {{ $asset->area->name }}</p>

        @if ($submitted)
            <div class="mt-6 rounded-md border border-accent-600 p-4 text-sm text-ink">
                Falla reportada correctamente. El equipo de mantenimiento ha sido notificado.
            </div>

            <button wire:click="$set('submitted', false)" class="btn-ghost text-sm mt-4">
                Reportar otra falla en este equipo
            </button>
        @else
            <form wire:submit="report" class="mt-6 flex flex-col gap-4">
                <div class="field">
                    <label>Tipo</label>
                    <select wire:model="type" class="input">
                        @foreach ($types as $t)
                            <option value="{{ $t->value }}">{{ $t->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label>Prioridad</label>
                    <select wire:model="priority" class="input">
                        @foreach ($priorities as $p)
                            <option value="{{ $p->value }}">{{ $p->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label>Ejecución</label>
                    <select wire:model.live="execution_type" class="input">
                        @foreach ($executionTypes as $e)
                            <option value="{{ $e->value }}">{{ $e->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('execution_type')" class="mt-1" />
                </div>

                @if ($execution_type === 'externo')
                    <div class="field">
                        <label>Proveedor</label>
                        <select wire:model="provider_id" class="input">
                            <option value="">Selecciona un proveedor</option>
                            @foreach ($providers as $provider)
                                <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('provider_id')" class="mt-1" />
                    </div>
                @endif

                <div class="field">
                    <label>¿Qué está pasando?</label>
                    <textarea wire:model="failure_description" rows="4" autofocus class="input" placeholder="Describe la falla observada..."></textarea>
                    <x-input-error :messages="$errors->get('failure_description')" class="mt-1" />
                </div>

                <button type="submit" class="btn btn-primary w-full justify-center">Crear reporte</button>
            </form>
        @endif
    </div>
</div>
