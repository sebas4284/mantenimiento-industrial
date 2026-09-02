<?php

namespace App\Models;

use Database\Factories\WorkOrderChecklistResultFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['work_order_id', 'checklist_item_id', 'passed', 'value', 'notes'])]
class WorkOrderChecklistResult extends Model
{
    /** @use HasFactory<WorkOrderChecklistResultFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'passed' => 'boolean',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(ChecklistItem::class);
    }
}
