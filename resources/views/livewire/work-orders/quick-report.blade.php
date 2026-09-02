<div class="max-w-md mx-auto py-10 px-4">
    <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
        <p class="text-xs font-mono text-gray-400">{{ $asset->code }}</p>
        <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $asset->name }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $asset->area->plant->name }} — {{ $asset->area->name }}</p>

        @if ($submitted)
            <div class="mt-6 rounded-lg bg-green-50 dark:bg-green-900/30 p-4 text-sm text-green-800 dark:text-green-300">
                Falla reportada correctamente. El equipo de mantenimiento ha sido notificado.
            </div>

            <button wire:click="$set('submitted', false)" class="mt-4 text-sm font-medium text-indigo-600 hover:text-indigo-500">
                Reportar otra falla en este equipo
            </button>
        @else
            <form wire:submit="report" class="mt-6 space-y-4">
                <div>
                    <x-input-label for="type" value="Tipo" />
                    <select wire:model="type" id="type" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                        @foreach ($types as $t)
                            <option value="{{ $t->value }}">{{ $t->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('type')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="priority" value="Prioridad" />
                    <select wire:model="priority" id="priority" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                        @foreach ($priorities as $p)
                            <option value="{{ $p->value }}">{{ $p->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input-label for="execution_type" value="Ejecución" />
                    <select wire:model.live="execution_type" id="execution_type" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                        @foreach ($executionTypes as $e)
                            <option value="{{ $e->value }}">{{ $e->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('execution_type')" class="mt-1" />
                </div>

                @if ($execution_type === 'externo')
                    <div>
                        <x-input-label for="provider_id" value="Proveedor" />
                        <select wire:model="provider_id" id="provider_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                            <option value="">Selecciona un proveedor</option>
                            @foreach ($providers as $provider)
                                <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('provider_id')" class="mt-1" />
                    </div>
                @endif

                <div>
                    <x-input-label for="failure_description" value="¿Qué está pasando?" />
                    <textarea wire:model="failure_description" id="failure_description" rows="4" autofocus
                        class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm"
                        placeholder="Describe la falla observada..."></textarea>
                    <x-input-error :messages="$errors->get('failure_description')" class="mt-1" />
                </div>

                <x-primary-button class="w-full justify-center">Crear reporte</x-primary-button>
            </form>
        @endif
    </div>
</div>
