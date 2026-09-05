<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-clipboard-text text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Crear reporte</h1>
    </div>
</x-slot>

<div class="grid place-items-center py-10">
    <div class="dialog">
        <div>
            <p class="text-xs font-mono text-neutral-500">{{ $asset->code }}</p>
            <h2 class="m-0 text-lg text-ink">{{ $asset->name }}</h2>
            <p class="text-xs text-neutral-400">{{ $asset->area->plant->name }} — {{ $asset->area->name }}</p>
        </div>

        @if ($submitted)
            <div class="rounded-md p-3 text-sm" style="background: color-mix(in srgb, var(--color-accent) 15%, transparent); color: var(--color-accent-200);">
                Falla reportada correctamente. El equipo de mantenimiento ha sido notificado.
            </div>

            <button wire:click="$set('submitted', false)" class="text-sm text-accent-300 text-left">
                Reportar otra falla en este equipo
            </button>
        @else
            <form wire:submit="report" class="flex flex-col gap-4">
                <div class="field">
                    <label>Tipo</label>
                    <select wire:model="type" class="input">
                        @foreach ($types as $t)
                            <option value="{{ $t->value }}">{{ $t->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('type')" class="mt-1" />
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
                    <label>Descripción de la falla</label>
                    <textarea wire:model="failure_description" rows="4" class="input" autofocus placeholder="Describe la falla observada..."></textarea>
                    <x-input-error :messages="$errors->get('failure_description')" class="mt-1" />
                </div>

                <div class="dialog-actions">
                    <button type="submit" class="btn btn-primary">Crear reporte</button>
                </div>
            </form>
        @endif
    </div>
</div>
