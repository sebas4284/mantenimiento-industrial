<?php

namespace App\Models;

use App\Enums\AssetCriticality;
use App\Enums\AssetStatus;
use App\Models\Concerns\ScopedToPlant;
use Database\Factories\AssetFactory;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'area_id',
    'code',
    'name',
    'manufacturer',
    'model',
    'serial_number',
    'criticality',
    'status',
    'qr_code_path',
    'photo_path',
])]
class Asset extends Model
{
    /** @use HasFactory<AssetFactory> */
    use HasFactory, ScopedToPlant;

    protected function casts(): array
    {
        return [
            'criticality' => AssetCriticality::class,
            'status' => AssetStatus::class,
        ];
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function maintenancePlans(): HasMany
    {
        return $this->hasMany(MaintenancePlan::class);
    }

    public function preOperationalChecklists(): HasMany
    {
        return $this->hasMany(PreOperationalChecklist::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * The status actually shown to users: "Inactivo" is the only status set by
     * hand, everything else is derived from whether the asset currently has a
     * work order in progress.
     */
    public function computedStatus(bool $hasActiveWorkOrder): AssetStatus
    {
        if ($this->status === AssetStatus::Inactivo) {
            return AssetStatus::Inactivo;
        }

        return $hasActiveWorkOrder ? AssetStatus::Mantenimiento : AssetStatus::Operativo;
    }

    /**
     * Generate (or regenerate) the QR code that links to this asset's quick-report page,
     * and store its path on the model without persisting.
     */
    public function generateQrCode(): string
    {
        $qrCode = new QrCode(url("/reportar/{$this->code}"));
        $path = "qrcodes/{$this->code}.svg";

        Storage::disk('public')->put($path, (new SvgWriter)->write($qrCode)->getString());

        return $this->qr_code_path = $path;
    }

    protected static function applyPlantScope(Builder $builder, ?int $plantId): void
    {
        $builder->whereHas('area', fn (Builder $query) => $query->where('plant_id', $plantId));
    }
}
