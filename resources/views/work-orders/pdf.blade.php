<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $workOrder->order_number }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        h2 { font-size: 13px; margin: 18px 0 6px; padding-bottom: 4px; border-bottom: 1px solid #d1d5db; }
        .muted { color: #6b7280; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; background: #f3f4f6; color: #374151; margin-right: 4px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        table.grid td { padding: 4px 8px 4px 0; vertical-align: top; width: 25%; }
        table.list td, table.list th { padding: 4px 6px; border-bottom: 1px solid #e5e7eb; text-align: left; font-size: 10px; }
        .photos img { width: 90px; height: 90px; object-fit: cover; margin: 0 6px 6px 0; border: 1px solid #d1d5db; }
    </style>
</head>
<body>
    <h1>{{ $workOrder->order_number }} — {{ $workOrder->asset->name }}</h1>
    <p class="muted">{{ $workOrder->asset->code }} · {{ $workOrder->asset->area->plant->name }} — {{ $workOrder->asset->area->name }}</p>
    <p>
        <span class="badge">{{ $workOrder->status->label() }}</span>
        <span class="badge">{{ $workOrder->priority->label() }}</span>
        <span class="badge">{{ $workOrder->type->label() }}</span>
        <span class="badge">{{ $workOrder->execution_type->label() }}</span>
    </p>
    <p><strong>Descripción de la falla:</strong> {{ $workOrder->failure_description ?? 'Mantenimiento preventivo programado' }}</p>
    <p class="muted">Reportada por {{ $workOrder->reportedBy->name }}</p>

    <h2>Asignación</h2>
    <table class="grid">
        <tr>
            @if ($workOrder->execution_type === \App\Enums\WorkOrderExecutionType::Externo)
                <td><span class="muted">Proveedor</span><br>{{ $workOrder->provider->name ?? '—' }}</td>
                <td><span class="muted">Colaborador de apoyo</span><br>{{ $workOrder->supportCollaborator->name ?? '—' }}</td>
            @else
                <td><span class="muted">Colaborador asignado</span><br>{{ $workOrder->assignedTo->name ?? '—' }}</td>
            @endif
        </tr>
    </table>

    <h2>Tiempos</h2>
    <table class="grid">
        <tr>
            <td><span class="muted">Abierta</span><br>{{ $workOrder->opened_at->format('d/m/Y H:i') }}</td>
            <td><span class="muted">Iniciada</span><br>{{ $workOrder->started_at?->format('d/m/Y H:i') ?? '—' }}</td>
            <td><span class="muted">Completada</span><br>{{ $workOrder->completed_at?->format('d/m/Y H:i') ?? '—' }}</td>
            <td><span class="muted">Duración total</span><br>{{ $workOrder->status->isOpen() ? 'En curso' : \App\Models\WorkOrder::formatDurationMinutes($workOrder->total_minutes) }}</td>
        </tr>
        <tr>
            <td><span class="muted">Tiempo de espera</span><br>{{ \App\Models\WorkOrder::formatDurationMinutes($workOrder->wait_minutes) }}</td>
            <td><span class="muted">Tiempo de ejecución</span><br>{{ \App\Models\WorkOrder::formatDurationMinutes($workOrder->repair_minutes) }}</td>
        </tr>
    </table>

    @if ($workOrder->execution_type === \App\Enums\WorkOrderExecutionType::Externo)
        <h2>Factura / requerimiento de compra</h2>
        <table class="grid">
            <tr>
                <td><span class="muted">N.° factura / requerimiento de compra</span><br>{{ $workOrder->invoice_number ?? '—' }}</td>
                <td><span class="muted">Monto pagado</span><br>{{ $workOrder->amount_paid !== null ? '$'.number_format((float) $workOrder->amount_paid, 2) : '—' }}</td>
            </tr>
        </table>
    @endif

    @if ($workOrder->checklistResults->isNotEmpty())
        <h2>Checklist</h2>
        <table class="list">
            <thead><tr><th>Ítem</th><th>Resultado</th><th>Notas</th></tr></thead>
            <tbody>
                @foreach ($workOrder->checklistResults as $result)
                    <tr>
                        <td>{{ $result->checklistItem->label ?? '—' }}</td>
                        <td>{{ $result->passed === null ? '—' : ($result->passed ? 'OK' : 'Falla') }}</td>
                        <td>{{ $result->notes ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Descripción de las reparaciones o mantenimientos realizados</h2>
    <p>{{ $workOrder->resolution_notes ?? 'Sin registrar.' }}</p>

    <h2>Relación de insumos o partes que se cambian</h2>
    @if ($workOrder->sparePartUsages->isNotEmpty())
        <table class="list">
            <thead><tr><th>Repuesto</th><th>Código</th><th>Cantidad</th></tr></thead>
            <tbody>
                @foreach ($workOrder->sparePartUsages as $usage)
                    <tr>
                        <td>{{ $usage->sparePart->name }}</td>
                        <td>{{ $usage->sparePart->code }}</td>
                        <td>{{ $usage->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="muted">No se registraron repuestos.</p>
    @endif

    <h2>Evidencia fotográfica</h2>
    @if ($workOrder->attachments->isNotEmpty())
        <div class="photos">
            @foreach ($workOrder->attachments as $attachment)
                <img src="{{ Storage::disk('public')->path($attachment->path) }}">
            @endforeach
        </div>
    @else
        <p class="muted">Sin fotos adjuntas.</p>
    @endif
</body>
</html>
