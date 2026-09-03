<?php

namespace App\Livewire\PreOperationalChecklists;

use App\Models\PreOperationalChecklist;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public PreOperationalChecklist $preOperationalChecklist;

    public function mount(PreOperationalChecklist $preOperationalChecklist): void
    {
        $this->authorize('view', $preOperationalChecklist);

        $this->preOperationalChecklist = $preOperationalChecklist->load([
            'asset.area.plant',
            'performedBy',
            'answers.item',
        ]);
    }

    public function render()
    {
        $answersBySection = $this->preOperationalChecklist->answers
            ->sortBy(fn ($answer) => $answer->item->order)
            ->groupBy(fn ($answer) => $answer->item->section);

        return view('livewire.pre-operational-checklists.show', [
            'answersBySection' => $answersBySection,
        ]);
    }
}
