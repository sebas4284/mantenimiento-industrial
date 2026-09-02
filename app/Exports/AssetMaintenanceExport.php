<?php

namespace App\Exports;

use App\Enums\WorkOrderType;
use App\Models\Asset;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AssetMaintenanceExport implements Export, WithMultipleSheets
{
    public function __construct(
        private Asset $asset,
        private ?Carbon $from,
        private ?Carbon $to,
    ) {}

    /**
     * @return array<int, FromCollection>
     */
    public function sheets(): array
    {
        return [
            new AssetWorkOrdersSheetExport($this->asset, WorkOrderType::Correctivo, $this->from, $this->to),
            new AssetWorkOrdersSheetExport($this->asset, WorkOrderType::Preventivo, $this->from, $this->to),
        ];
    }
}
