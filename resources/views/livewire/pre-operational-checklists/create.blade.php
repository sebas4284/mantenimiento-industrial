<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-shield-check text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Nueva lista preoperacional</h1>
    </div>
</x-slot>

<div class="max-w-4xl space-y-4">
    <a href="{{ route('pre-operational-checklists.index') }}" wire:navigate class="text-sm text-accent-300">&larr; Volver a listas preoperacionales</a>

    <form wire:submit="save" class="flex flex-col gap-4">
        <div class="card elev-sm p-6">
            <p class="text-sm text-neutral-400">Verificar antes de iniciar la operación que la máquina se encuentra en condiciones seguras y adecuadas de funcionamiento.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="field">
                    <label>Máquina / activo</label>
                    <select wire:model="asset_id" class="input">
                        <option value="">Selecciona un activo</option>
                        @foreach ($assets as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->code }} — {{ $asset->name }} ({{ $asset->area->name }})</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('asset_id')" class="mt-1" />
                </div>

                <div class="field">
                    <label>Fecha y hora</label>
                    @if (auth()->user()->role === \App\Enums\UserRole::Admin)
                        <input wire:model="inspected_at" type="datetime-local" class="input">
                        <x-input-error :messages="$errors->get('inspected_at')" class="mt-1" />
                    @else
                        <input value="{{ now()->format('d/m/Y H:i') }}" disabled class="input opacity-60 cursor-not-allowed">
                        <p class="mt-1 text-xs text-neutral-500">Se registra automáticamente al guardar.</p>
                    @endif
                </div>
            </div>
        </div>

        @foreach ($itemsBySection as $section => $items)
            <div class="card elev-sm p-6">
                <h2 class="text-sm uppercase text-ink m-0">{{ $section }}</h2>

                <div class="mt-1 divide-y divide-neutral-800">
                    @foreach ($items as $item)
                        <div class="flex items-center justify-between gap-4 py-2.5" wire:key="item-{{ $item->id }}">
                            <p class="text-sm text-neutral-300 flex-1 m-0">{{ $item->label }}</p>
                            <div class="flex gap-3 shrink-0">
                                <label class="flex items-center gap-1 text-xs text-neutral-300">
                                    <input type="radio" wire:model="answers.{{ $item->id }}" value="buena"> Buena
                                </label>
                                <label class="flex items-center gap-1 text-xs text-accent-300">
                                    <input type="radio" wire:model="answers.{{ $item->id }}" value="mala"> Mala
                                </label>
                                <label class="flex items-center gap-1 text-xs text-neutral-500">
                                    <input type="radio" wire:model="answers.{{ $item->id }}" value="na"> N/A
                                </label>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('answers.'.$item->id)" class="mb-1" />
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="card elev-sm p-6 gap-4">
            <h2 class="card-title m-0">Resultado final</h2>

            <div class="field">
                <label>Resultado de la inspección</label>
                <select wire:model="result" class="input">
                    <option value="">Selecciona un resultado</option>
                    @foreach ($results as $r)
                        <option value="{{ $r->value }}">{{ $r->label() }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('result')" class="mt-1" />
                <p class="mt-1 text-xs text-accent-300">Si alguna condición crítica de seguridad quedó en MALA, la máquina debe considerarse NO APTA PARA OPERAR hasta que sea evaluada y corregida.</p>
            </div>

            <div class="field">
                <label>Observaciones — descripción de anomalías encontradas</label>
                <textarea wire:model="anomaly_notes" rows="3" class="input"></textarea>
                <x-input-error :messages="$errors->get('anomaly_notes')" class="mt-1" />
            </div>

            <div class="field">
                <label>Acción requerida</label>
                <select wire:model="required_action" class="input">
                    @foreach ($requiredActions as $action)
                        <option value="{{ $action->value }}">{{ $action->label() }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('required_action')" class="mt-1" />
            </div>

            <div class="field">
                <label>Observaciones adicionales</label>
                <textarea wire:model="additional_notes" rows="2" class="input"></textarea>
                <x-input-error :messages="$errors->get('additional_notes')" class="mt-1" />
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('pre-operational-checklists.index') }}" wire:navigate class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar lista preoperacional</button>
        </div>
    </form>
</div>
