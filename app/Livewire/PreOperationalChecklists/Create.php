<?php

namespace App\Livewire\PreOperationalChecklists;

use App\Enums\PreOperationalAnswer;
use App\Enums\PreOperationalRequiredAction;
use App\Enums\PreOperationalResult;
use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\PreOperationalChecklist;
use App\Models\PreOperationalChecklistAnswer;
use App\Models\PreOperationalItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Create extends Component
{
    public ?int $asset_id = null;

    public string $inspected_at = '';

    public string $result = '';

    public string $anomaly_notes = '';

    public string $required_action = 'ninguna';

    public string $additional_notes = '';

    /** @var array<int, string> */
    public array $answers = [];

    public function mount(): void
    {
        $this->authorize('create', PreOperationalChecklist::class);

        $this->inspected_at = now()->format('Y-m-d\TH:i');

        foreach (PreOperationalItem::orderBy('order')->pluck('id') as $itemId) {
            $this->answers[$itemId] = '';
        }
    }

    public function save(): void
    {
        $this->authorize('create', PreOperationalChecklist::class);

        $isAdmin = auth()->user()->role === UserRole::Admin;

        $rules = [
            'asset_id' => ['required', 'exists:assets,id'],
            'result' => ['required', new Enum(PreOperationalResult::class)],
            'anomaly_notes' => ['nullable', 'string', 'max:2000'],
            'required_action' => ['required', new Enum(PreOperationalRequiredAction::class)],
            'additional_notes' => ['nullable', 'string', 'max:2000'],
            'answers' => ['required', 'array'],
            'answers.*' => ['required', new Enum(PreOperationalAnswer::class)],
        ];

        // Only Admin may back- or post-date a submission; everyone else always gets the server's current time.
        if ($isAdmin) {
            $rules['inspected_at'] = ['required', 'date'];
        }

        $validated = $this->validate($rules);

        $inspectedAt = $isAdmin ? $validated['inspected_at'] : now();

        DB::transaction(function () use ($validated, $inspectedAt) {
            $checklist = PreOperationalChecklist::create([
                'asset_id' => $validated['asset_id'],
                'performed_by' => auth()->id(),
                'inspected_at' => $inspectedAt,
                'result' => $validated['result'],
                'anomaly_notes' => $validated['anomaly_notes'],
                'required_action' => $validated['required_action'],
                'additional_notes' => $validated['additional_notes'],
            ]);

            foreach ($validated['answers'] as $itemId => $answer) {
                PreOperationalChecklistAnswer::create([
                    'checklist_id' => $checklist->id,
                    'item_id' => $itemId,
                    'answer' => $answer,
                ]);
            }
        });

        $this->redirect(route('pre-operational-checklists.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.pre-operational-checklists.create', [
            'assets' => Asset::with('area')->orderBy('name')->get(),
            'itemsBySection' => PreOperationalItem::orderBy('order')->get()->groupBy('section'),
            'results' => PreOperationalResult::cases(),
            'requiredActions' => PreOperationalRequiredAction::cases(),
        ]);
    }
}
