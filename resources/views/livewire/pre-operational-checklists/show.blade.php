<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-shield-check text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">{{ $preOperationalChecklist->asset->name }}</h1>
    </div>
</x-slot>

<div class="max-w-4xl space-y-4">
    <a href="{{ route('pre-operational-checklists.index') }}" wire:navigate class="text-sm text-accent-300">&larr; Volver a listas preoperacionales</a>

    @php
        $answerTagClass = fn ($answer) => match ($answer) {
            \App\Enums\PreOperationalAnswer::Buena => 'tag-neutral',
            \App\Enums\PreOperationalAnswer::Mala => 'tag-accent',
            \App\Enums\PreOperationalAnswer::Na => 'tag-outline',
        };
    @endphp

    <div class="card elev-sm p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <p class="text-xs font-mono text-neutral-500 m-0">{{ $preOperationalChecklist->asset->code }} · {{ $preOperationalChecklist->asset->area->plant->name }} — {{ $preOperationalChecklist->asset->area->name }}</p>
            <span class="tag tag-{{ $preOperationalChecklist->result->tagVariant() }}">{{ $preOperationalChecklist->result->label() }}</span>
        </div>

        <dl class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <dt class="text-neutral-500">Fecha y hora</dt>
                <dd class="text-ink">{{ $preOperationalChecklist->inspected_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-neutral-500">Responsable</dt>
                <dd class="text-ink">{{ $preOperationalChecklist->performedBy->name }}</dd>
            </div>
            <div>
                <dt class="text-neutral-500">Acción requerida</dt>
                <dd class="text-ink">{{ $preOperationalChecklist->required_action->label() }}</dd>
            </div>
        </dl>

        @if ($preOperationalChecklist->anomaly_notes)
            <div class="mt-4 border-t border-neutral-800 pt-4">
                <dt class="text-neutral-500 text-sm">Observaciones — anomalías encontradas</dt>
                <dd class="mt-1 text-sm text-ink">{{ $preOperationalChecklist->anomaly_notes }}</dd>
            </div>
        @endif

        @if ($preOperationalChecklist->additional_notes)
            <div class="mt-3">
                <dt class="text-neutral-500 text-sm">Observaciones adicionales</dt>
                <dd class="mt-1 text-sm text-ink">{{ $preOperationalChecklist->additional_notes }}</dd>
            </div>
        @endif
    </div>

    @foreach ($answersBySection as $section => $answers)
        <div class="card elev-sm p-6">
            <h2 class="text-sm uppercase text-ink m-0">{{ $section }}</h2>

            <div class="mt-1 divide-y divide-neutral-800">
                @foreach ($answers as $answer)
                    <div class="flex items-center justify-between gap-4 py-2.5">
                        <p class="text-sm text-neutral-300 flex-1 m-0">{{ $answer->item->label }}</p>
                        <span class="tag {{ $answerTagClass($answer->answer) }}">{{ $answer->answer->label() }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
