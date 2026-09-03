<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
    <div>
        <a href="{{ route('pre-operational-checklists.index') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-500">&larr; Volver a listas preoperacionales</a>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
            <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Nueva lista preoperacional</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Verificar antes de iniciar la operación que la máquina se encuentra en condiciones seguras y adecuadas de funcionamiento.</p>

            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="asset_id" value="Máquina / activo" />
                    <select wire:model="asset_id" id="asset_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                        <option value="">Selecciona un activo</option>
                        @foreach ($assets as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->code }} — {{ $asset->name }} ({{ $asset->area->name }})</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('asset_id')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="inspected_at" value="Fecha y hora" />
                    <x-text-input wire:model="inspected_at" id="inspected_at" type="datetime-local" class="mt-1 block w-full text-sm" />
                    <x-input-error :messages="$errors->get('inspected_at')" class="mt-1" />
                </div>
            </div>
        </div>

        @foreach ($itemsBySection as $section => $items)
            <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
                <h2 class="font-semibold text-gray-900 dark:text-gray-100 uppercase text-sm">{{ $section }}</h2>

                <div class="mt-3 divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($items as $item)
                        <div class="flex items-center justify-between gap-4 py-2.5" wire:key="item-{{ $item->id }}">
                            <p class="text-sm text-gray-700 dark:text-gray-300 flex-1">{{ $item->label }}</p>
                            <div class="flex gap-3 shrink-0">
                                <label class="inline-flex items-center gap-1 text-xs text-green-700 dark:text-green-400">
                                    <input type="radio" wire:model="answers.{{ $item->id }}" value="buena"> Buena
                                </label>
                                <label class="inline-flex items-center gap-1 text-xs text-red-700 dark:text-red-400">
                                    <input type="radio" wire:model="answers.{{ $item->id }}" value="mala"> Mala
                                </label>
                                <label class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                    <input type="radio" wire:model="answers.{{ $item->id }}" value="na"> N/A
                                </label>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('answers.'.$item->id)" class="mb-1" />
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6 space-y-4">
            <h2 class="font-semibold text-gray-900 dark:text-gray-100">Resultado final</h2>

            <div>
                <x-input-label for="result" value="Resultado de la inspección" />
                <select wire:model="result" id="result" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                    <option value="">Selecciona un resultado</option>
                    @foreach ($results as $r)
                        <option value="{{ $r->value }}">{{ $r->label() }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('result')" class="mt-1" />
                <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">Si alguna condición crítica de seguridad quedó en MALA, la máquina debe considerarse NO APTA PARA OPERAR hasta que sea evaluada y corregida.</p>
            </div>

            <div>
                <x-input-label for="anomaly_notes" value="Observaciones — descripción de anomalías encontradas" />
                <textarea wire:model="anomaly_notes" id="anomaly_notes" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm"></textarea>
                <x-input-error :messages="$errors->get('anomaly_notes')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="required_action" value="Acción requerida" />
                <select wire:model="required_action" id="required_action" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm">
                    @foreach ($requiredActions as $action)
                        <option value="{{ $action->value }}">{{ $action->label() }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('required_action')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="additional_notes" value="Observaciones adicionales" />
                <textarea wire:model="additional_notes" id="additional_notes" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-sm"></textarea>
                <x-input-error :messages="$errors->get('additional_notes')" class="mt-1" />
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('pre-operational-checklists.index') }}" wire:navigate>
                <x-secondary-button type="button">Cancelar</x-secondary-button>
            </a>
            <x-primary-button>Guardar lista preoperacional</x-primary-button>
        </div>
    </form>
</div>
