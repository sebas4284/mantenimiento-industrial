<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
    <div>
        <a href="{{ route('pre-operational-checklists.index') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-500">&larr; Volver a listas preoperacionales</a>
    </div>

    <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-mono text-gray-400">{{ $preOperationalChecklist->asset->code }} · {{ $preOperationalChecklist->asset->area->plant->name }} — {{ $preOperationalChecklist->asset->area->name }}</p>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $preOperationalChecklist->asset->name }}</h1>
            </div>
            <x-badge :color="$preOperationalChecklist->result->color()">{{ $preOperationalChecklist->result->label() }}</x-badge>
        </div>

        <dl class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <dt class="text-gray-400">Fecha y hora</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $preOperationalChecklist->inspected_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-gray-400">Responsable</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $preOperationalChecklist->performedBy->name }}</dd>
            </div>
            <div>
                <dt class="text-gray-400">Acción requerida</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $preOperationalChecklist->required_action->label() }}</dd>
            </div>
        </dl>

        @if ($preOperationalChecklist->anomaly_notes)
            <div class="mt-4 border-t border-gray-100 dark:border-gray-700 pt-4">
                <dt class="text-gray-400 text-sm">Observaciones — anomalías encontradas</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $preOperationalChecklist->anomaly_notes }}</dd>
            </div>
        @endif

        @if ($preOperationalChecklist->additional_notes)
            <div class="mt-3">
                <dt class="text-gray-400 text-sm">Observaciones adicionales</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $preOperationalChecklist->additional_notes }}</dd>
            </div>
        @endif
    </div>

    @foreach ($answersBySection as $section => $answers)
        <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
            <h2 class="font-semibold text-gray-900 dark:text-gray-100 uppercase text-sm">{{ $section }}</h2>

            <div class="mt-3 divide-y divide-gray-100 dark:divide-gray-700">
                @foreach ($answers as $answer)
                    <div class="flex items-center justify-between gap-4 py-2.5">
                        <p class="text-sm text-gray-700 dark:text-gray-300 flex-1">{{ $answer->item->label }}</p>
                        <x-badge :color="$answer->answer->color()">{{ $answer->answer->label() }}</x-badge>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
