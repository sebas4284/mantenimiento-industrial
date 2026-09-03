<?php

namespace App\Models;

use App\Enums\PreOperationalAnswer;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['checklist_id', 'item_id', 'answer'])]
class PreOperationalChecklistAnswer extends Model
{
    protected function casts(): array
    {
        return [
            'answer' => PreOperationalAnswer::class,
        ];
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(PreOperationalChecklist::class, 'checklist_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(PreOperationalItem::class, 'item_id');
    }
}
