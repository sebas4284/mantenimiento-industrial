<div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('assets.index') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-500">&larr; Volver a activos</a>

        <button wire:click="openHistory" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            Ver historial de mantenimiento
        </button>
    </div>

    <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
            <div class="sm:col-span-1">
                @if ($asset->photo_path)
                    <img src="{{ Storage::disk('public')->url($asset->photo_path) }}" alt="{{ $asset->name }}"
                        class="w-full aspect-square object-cover rounded-lg ring-1 ring-gray-200 dark:ring-gray-700">
                @else
                    <div class="w-full aspect-square rounded-lg bg-gray-100 dark:bg-gray-900 ring-1 ring-gray-200 dark:ring-gray-700 flex items-center justify-center">
                        <svg class="h-16 w-16 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 21h18a1.5 1.5 0 001.5-1.5V4.5A1.5 1.5 0 0021 3H3a1.5 1.5 0 00-1.5 1.5v15A1.5 1.5 0 003 21z" />
                        </svg>
                    </div>
                @endif
            </div>

            <div class="sm:col-span-2">
                <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $asset->name }}</h1>

                <div class="mt-3 flex flex-wrap gap-2">
                    <x-badge :color="$asset->status->color()">{{ $asset->status->label() }}</x-badge>
                    <x-badge color="zinc">Criticidad {{ $asset->criticality->value }}</x-badge>
                </div>

                <dl class="mt-6 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-400">Código</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $asset->code }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Estado</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $asset->status->label() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Planta</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $asset->area->plant->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Área</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $asset->area->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Fabricante</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $asset->manufacturer ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Modelo</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $asset->model ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Número de serie</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $asset->serial_number ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Criticidad</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $asset->criticality->label() }}</dd>
                    </div>
                </dl>
            </div>

            <div class="sm:col-span-1">
                @if ($asset->qr_code_path)
                    <img src="{{ Storage::disk('public')->url($asset->qr_code_path) }}" alt="QR {{ $asset->code }}"
                        class="w-full aspect-square object-contain rounded-lg bg-white ring-1 ring-gray-200 dark:ring-gray-700 p-2">
                    <a href="{{ Storage::disk('public')->url($asset->qr_code_path) }}" target="_blank" class="mt-2 block text-center text-xs font-medium text-indigo-600 hover:text-indigo-500">Ver / imprimir QR</a>
                @else
                    <div class="w-full aspect-square rounded-lg bg-gray-100 dark:bg-gray-900 ring-1 ring-gray-200 dark:ring-gray-700 flex items-center justify-center">
                        <span class="text-xs text-gray-400">Sin QR</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="font-semibold text-gray-900 dark:text-gray-100">Historial de mantenimiento</h2>
                <p class="text-xs text-gray-400 mt-0.5">Descarga un Excel con los correctivos y preventivos de este activo.</p>
            </div>

            <form wire:submit="exportHistory" class="flex flex-wrap items-end gap-3">
                <div>
                    <x-input-label for="exportFrom" value="Desde" />
                    <x-text-input wire:model="exportFrom" id="exportFrom" type="date" class="mt-1 block text-sm" />
                </div>
                <div>
                    <x-input-label for="exportTo" value="Hasta" />
                    <x-text-input wire:model="exportTo" id="exportTo" type="date" class="mt-1 block text-sm" />
                </div>
                <x-secondary-button type="submit">
                    <svg class="h-4 w-4 mr-1.5 -ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Descargar Excel
                </x-secondary-button>
            </form>
        </div>
        <x-input-error :messages="$errors->get('exportTo')" class="mt-2" />
    </div>

    <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
        <h2 class="font-semibold text-gray-900 dark:text-gray-100">Mantenimiento correctivo</h2>

        <div class="mt-4 overflow-x-auto rounded-xl ring-1 ring-gray-200 dark:ring-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                        <th class="px-4 py-3">N° Orden</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Descripción</th>
                        <th class="px-4 py-3">Prioridad</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Técnico</th>
                        <th class="px-4 py-3">Completada</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($correctivos as $wo)
                        <tr wire:key="correctivo-{{ $wo->id }}" class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900/30" onclick="window.location='{{ route('work-orders.show', $wo) }}'">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ $wo->order_number }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $wo->opened_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $wo->failure_description ?? '—' }}</td>
                            <td class="px-4 py-3"><x-badge :color="$wo->priority->color()">{{ $wo->priority->label() }}</x-badge></td>
                            <td class="px-4 py-3"><x-badge :color="$wo->status->color()">{{ $wo->status->label() }}</x-badge></td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $wo->assignedTo->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $wo->completed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay mantenimientos correctivos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
        <h2 class="font-semibold text-gray-900 dark:text-gray-100">Mantenimiento preventivo</h2>

        <div class="mt-4 overflow-x-auto rounded-xl ring-1 ring-gray-200 dark:ring-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                        <th class="px-4 py-3">N° Orden</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Plan</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Técnico</th>
                        <th class="px-4 py-3">Completada</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($preventivos as $wo)
                        <tr wire:key="preventivo-{{ $wo->id }}" class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900/30" onclick="window.location='{{ route('work-orders.show', $wo) }}'">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ $wo->order_number }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $wo->opened_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $wo->maintenancePlan?->name ?? 'Mantenimiento preventivo' }}</td>
                            <td class="px-4 py-3"><x-badge :color="$wo->status->color()">{{ $wo->status->label() }}</x-badge></td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $wo->assignedTo->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $wo->completed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay mantenimientos preventivos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="font-semibold text-gray-900 dark:text-gray-100">Listas preoperacionales</h2>
                <p class="text-xs text-gray-400 mt-0.5">Inspecciones de seguridad registradas antes de iniciar turno.</p>
            </div>

            <form wire:submit="exportPreOperationalChecklists" class="flex flex-wrap items-end gap-3">
                <div>
                    <x-input-label for="preopExportFrom" value="Desde" />
                    <x-text-input wire:model="preopExportFrom" id="preopExportFrom" type="date" class="mt-1 block text-sm" />
                </div>
                <div>
                    <x-input-label for="preopExportTo" value="Hasta" />
                    <x-text-input wire:model="preopExportTo" id="preopExportTo" type="date" class="mt-1 block text-sm" />
                </div>
                <x-secondary-button type="submit">
                    <svg class="h-4 w-4 mr-1.5 -ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Descargar Excel
                </x-secondary-button>
            </form>
        </div>
        <x-input-error :messages="$errors->get('preopExportTo')" class="mt-2" />

        <div class="mt-4 overflow-x-auto rounded-xl ring-1 ring-gray-200 dark:ring-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Resultado</th>
                        <th class="px-4 py-3">Acción requerida</th>
                        <th class="px-4 py-3">Responsable</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($preOperationalChecklists as $checklist)
                        <tr wire:key="preop-{{ $checklist->id }}" class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900/30" onclick="window.location='{{ route('pre-operational-checklists.show', $checklist) }}'">
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $checklist->inspected_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3"><x-badge :color="$checklist->result->color()">{{ $checklist->result->label() }}</x-badge></td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $checklist->required_action->label() }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $checklist->performedBy->name }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay listas preoperacionales registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <a href="{{ route('pre-operational-checklists.index', ['asset' => $asset->id]) }}" wire:navigate class="mt-3 inline-block text-xs font-medium text-indigo-600 hover:text-indigo-500">Ver todas las listas preoperacionales de este activo &rarr;</a>
    </div>

    @if ($showHistory)
        <div class="fixed inset-0 z-50" wire:transition>
            <div class="fixed inset-0 bg-gray-900/50" wire:click="closeHistory"></div>

            <div class="fixed inset-y-0 right-0 w-full max-w-md bg-white dark:bg-gray-800 shadow-xl overflow-y-auto">
                <div class="flex items-center justify-between p-6 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-gray-100">Historial de mantenimiento</h2>
                    <button wire:click="closeHistory" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl leading-none">&times;</button>
                </div>

                <div class="p-6 space-y-4">
                    @forelse ($workOrders as $wo)
                        <a href="{{ route('work-orders.show', $wo) }}" wire:navigate
                            class="block rounded-lg ring-1 ring-gray-200 dark:ring-gray-700 p-4 hover:ring-indigo-300 dark:hover:ring-indigo-600">
                            <p class="font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ $wo->order_number }}</p>
                            <div class="mt-1 flex flex-wrap gap-1.5">
                                <x-badge :color="$wo->status->color()">{{ $wo->status->label() }}</x-badge>
                                <x-badge :color="$wo->priority->color()">{{ $wo->priority->label() }}</x-badge>
                                <x-badge color="zinc">{{ $wo->type->label() }}</x-badge>
                            </div>
                            <p class="mt-2 text-sm text-gray-800 dark:text-gray-200">{{ $wo->failure_description ?? 'Mantenimiento preventivo programado' }}</p>
                            <p class="mt-1 text-xs text-gray-400">
                                Abierta {{ $wo->opened_at->format('d/m/Y H:i') }}
                                @if ($wo->completed_at)
                                    · Completada {{ $wo->completed_at->format('d/m/Y H:i') }}
                                @endif
                            </p>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">Este activo no tiene mantenimientos registrados todavía.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
