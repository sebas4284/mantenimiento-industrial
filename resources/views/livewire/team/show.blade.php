<div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
    <div>
        <a href="{{ route('team.index') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-500">&larr; Volver a equipo de trabajo</a>
    </div>

    <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $member->name }}</h1>
                <div class="mt-2 flex gap-2">
                    <x-badge color="zinc">{{ $member->role->label() }}</x-badge>
                    <x-badge :color="$isBusy ? 'amber' : 'green'">{{ $isBusy ? 'Ocupado' : 'Disponible' }}</x-badge>
                </div>
            </div>
        </div>

        <dl class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <dt class="text-gray-400">Correo</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $member->email }}</dd>
            </div>
            <div>
                <dt class="text-gray-400">Planta</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $member->plant->name ?? '—' }}</dd>
            </div>
        </dl>
    </div>

    <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
        <h2 class="font-semibold text-gray-900 dark:text-gray-100">Historial de mantenimientos realizados</h2>

        <div class="mt-4 overflow-x-auto rounded-xl ring-1 ring-gray-200 dark:ring-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                        <th class="px-4 py-3">N° Orden</th>
                        <th class="px-4 py-3">Activo</th>
                        <th class="px-4 py-3">Planta</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Rol</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Duración total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($workOrders as $wo)
                        <tr wire:key="wo-{{ $wo->id }}" class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900/30" onclick="window.location='{{ route('work-orders.show', $wo) }}'">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ $wo->order_number }}</td>
                            <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $wo->asset->code }} — {{ $wo->asset->name }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $wo->asset->area->plant->name }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $wo->opened_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3"><x-badge color="zinc">{{ $wo->collaboratorRole }}</x-badge></td>
                            <td class="px-4 py-3"><x-badge :color="$wo->status->color()">{{ $wo->status->label() }}</x-badge></td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                {{ $wo->status->isOpen() ? 'En curso' : \App\Models\WorkOrder::formatDurationMinutes($wo->total_minutes) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Este colaborador no tiene mantenimientos registrados todavía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
