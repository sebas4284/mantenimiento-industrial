<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-users-three text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">{{ $member->name }}</h1>
    </div>
</x-slot>

<div class="space-y-4">
    <a href="{{ route('team.index') }}" wire:navigate class="text-sm text-accent-300">&larr; Volver a equipo de trabajo</a>

    <div class="card elev-sm p-6">
        <div class="flex gap-2">
            <span class="tag tag-neutral">{{ $member->role->label() }}</span>
            <span class="tag {{ $isBusy ? 'tag-accent' : 'tag-neutral' }}">{{ $isBusy ? 'Ocupado' : 'Disponible' }}</span>
        </div>

        <dl class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <dt class="text-neutral-500">Correo</dt>
                <dd class="text-ink">{{ $member->email }}</dd>
            </div>
            <div>
                <dt class="text-neutral-500">Planta</dt>
                <dd class="text-ink">{{ $member->plant->name ?? '—' }}</dd>
            </div>
        </dl>
    </div>

    <div class="card elev-sm p-6">
        <h2 class="card-title m-0 mb-3">Historial de mantenimientos realizados</h2>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° Orden</th><th>Activo</th><th>Planta</th><th>Fecha</th><th>Rol</th><th>Estado</th><th>Duración total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($workOrders as $wo)
                        <tr wire:key="wo-{{ $wo->id }}" class="cursor-pointer" onclick="window.location='{{ route('work-orders.show', $wo) }}'">
                            <td class="font-mono text-xs text-accent-300">{{ $wo->order_number }}</td>
                            <td class="text-ink">{{ $wo->asset->code }} — {{ $wo->asset->name }}</td>
                            <td class="text-muted">{{ $wo->asset->area->plant->name }}</td>
                            <td class="text-muted">{{ $wo->opened_at->format('d/m/Y H:i') }}</td>
                            <td><span class="tag tag-neutral">{{ $wo->collaboratorRole }}</span></td>
                            <td><span class="tag tag-{{ $wo->status->tagVariant() }}">{{ $wo->status->label() }}</span></td>
                            <td class="text-muted">{{ $wo->status->isOpen() ? 'En curso' : \App\Models\WorkOrder::formatDurationMinutes($wo->total_minutes) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-8">Este colaborador no tiene mantenimientos registrados todavía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
