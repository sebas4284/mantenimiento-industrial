<?php

namespace App\Models;

use App\Enums\WorkOrderExecutionType;
use App\Enums\WorkOrderPriority;
use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderType;
use App\Models\Concerns\ScopedToPlant;
use Database\Factories\WorkOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'asset_id',
    'maintenance_plan_id',
    'reported_by',
    'assigned_to',
    'type',
    'execution_type',
    'provider_id',
    'priority',
    'status',
    'failure_description',
    'resolution_notes',
    'opened_at',
    'started_at',
    'completed_at',
])]
class WorkOrder extends Model
{
    /** @use HasFactory<WorkOrderFactory> */
    use HasFactory, ScopedToPlant;

    protected function casts(): array
    {
        return [
            'type' => WorkOrderType::class,
            'execution_type' => WorkOrderExecutionType::class,
            'priority' => WorkOrderPriority::class,
            'status' => WorkOrderStatus::class,
            'opened_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function maintenancePlan(): BelongsTo
    {
        return $this->belongsTo(MaintenancePlan::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function checklistResults(): HasMany
    {
        return $this->hasMany(WorkOrderChecklistResult::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function sparePartUsages(): HasMany
    {
        return $this->hasMany(SparePartUsage::class);
    }

    protected static function booted(): void
    {
        static::creating(function (WorkOrder $workOrder) {
            if (! $workOrder->order_number) {
                $plant = $workOrder->asset->area->plant;
                $workOrder->order_number = $plant->allocateNextWorkOrderNumber();
            }
        });
    }

    /**
     * Time waiting to be picked up, in minutes — opened_at to started_at.
     */
    public function getWaitMinutesAttribute(): ?int
    {
        if (! $this->started_at) {
            return null;
        }

        return (int) $this->opened_at->diffInMinutes($this->started_at);
    }

    /**
     * Repair/execution duration in minutes, used for MTTR — started_at to completed_at.
     */
    public function getRepairMinutesAttribute(): ?int
    {
        if (! $this->started_at || ! $this->completed_at) {
            return null;
        }

        return (int) $this->started_at->diffInMinutes($this->completed_at);
    }

    /**
     * Total lifecycle duration in minutes — opened_at to completed_at.
     */
    public function getTotalMinutesAttribute(): ?int
    {
        if (! $this->completed_at) {
            return null;
        }

        return (int) $this->opened_at->diffInMinutes($this->completed_at);
    }

    /**
     * Render a minute count as a short human string (e.g. "2d 4h", "45m").
     */
    public static function formatDurationMinutes(?int $minutes): string
    {
        if ($minutes === null) {
            return '—';
        }

        $days = intdiv($minutes, 1440);
        $hours = intdiv($minutes % 1440, 60);
        $mins = $minutes % 60;

        if ($days > 0) {
            return "{$days}d {$hours}h";
        }

        if ($hours > 0) {
            return "{$hours}h {$mins}m";
        }

        return "{$mins}m";
    }

    protected static function applyPlantScope(Builder $builder, ?int $plantId): void
    {
        $builder->whereHas('asset.area', fn (Builder $query) => $query->where('plant_id', $plantId));
    }
}
