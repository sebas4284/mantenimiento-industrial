<?php

namespace Tests\Feature;

use App\Enums\PreOperationalResult;
use App\Enums\UserRole;
use App\Livewire\Assets\Show as AssetsShow;
use App\Livewire\PreOperationalChecklists\Create;
use App\Livewire\PreOperationalChecklists\Index;
use App\Models\Area;
use App\Models\Asset;
use App\Models\Plant;
use App\Models\PreOperationalChecklist;
use App\Models\PreOperationalItem;
use App\Models\User;
use Database\Seeders\PreOperationalItemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class PreOperationalChecklistTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_pre_operational_checklist_saves_all_answers(): void
    {
        $this->seed(PreOperationalItemSeeder::class);

        $operator = User::factory()->role(UserRole::Operador)->create();
        $asset = Asset::factory()->create();
        $itemIds = PreOperationalItem::pluck('id');

        $this->actingAs($operator);

        $component = Livewire::test(Create::class)
            ->set('asset_id', $asset->id)
            ->set('inspected_at', now()->format('Y-m-d\TH:i'))
            ->set('result', PreOperationalResult::Apto->value)
            ->set('required_action', 'ninguna');

        foreach ($itemIds as $itemId) {
            $component->set("answers.{$itemId}", 'buena');
        }

        $component->call('save')->assertHasNoErrors();

        $this->assertSame(1, PreOperationalChecklist::withoutGlobalScopes()->count());
        $checklist = PreOperationalChecklist::withoutGlobalScopes()->first();
        $this->assertSame($asset->id, $checklist->asset_id);
        $this->assertSame($operator->id, $checklist->performed_by);
        $this->assertSame($itemIds->count(), $checklist->answers()->count());
    }

    public function test_corporativo_cannot_access_the_create_form(): void
    {
        $corporativo = User::factory()->role(UserRole::Corporativo)->create();

        $this->actingAs($corporativo)
            ->get(route('pre-operational-checklists.create'))
            ->assertForbidden();
    }

    public function test_the_result_is_kept_exactly_as_selected_not_recalculated(): void
    {
        $this->seed(PreOperationalItemSeeder::class);

        $admin = User::factory()->role(UserRole::Admin)->create();
        $asset = Asset::factory()->create();
        $itemIds = PreOperationalItem::pluck('id');

        $this->actingAs($admin);

        $component = Livewire::test(Create::class)
            ->set('asset_id', $asset->id)
            ->set('inspected_at', now()->format('Y-m-d\TH:i'))
            ->set('result', PreOperationalResult::Apto->value)
            ->set('required_action', 'ninguna');

        foreach ($itemIds as $index => $itemId) {
            // The very first answered item is "mala" — a manual APTO selection must still persist as-is.
            $component->set("answers.{$itemId}", $index === 0 ? 'mala' : 'buena');
        }

        $component->call('save')->assertHasNoErrors();

        $this->assertSame(PreOperationalResult::Apto, PreOperationalChecklist::first()->result);
    }

    public function test_excel_export_downloads_from_the_index(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        PreOperationalChecklist::factory()->create();

        $this->actingAs($admin);

        Livewire::test(Index::class)
            ->call('exportExcel')
            ->assertFileDownloaded('listas-preoperacionales.xlsx');
    }

    public function test_year_and_month_filter_only_shows_matching_checklists(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();

        $matching = PreOperationalChecklist::factory()->create([
            'inspected_at' => Carbon::parse('2026-06-10 08:00:00'),
        ]);

        $other = PreOperationalChecklist::factory()->create([
            'inspected_at' => Carbon::parse('2026-01-05 08:00:00'),
        ]);

        $this->actingAs($admin);

        $checklists = Livewire::test(Index::class)
            ->call('selectYear', 2026)
            ->call('selectMonth', 2026, 6)
            ->viewData('checklists');

        $ids = collect($checklists->items())->pluck('id');
        $this->assertTrue($ids->contains($matching->id));
        $this->assertFalse($ids->contains($other->id));
    }

    public function test_asset_page_shows_and_exports_its_pre_operational_checklists(): void
    {
        $plant = Plant::factory()->create();
        $admin = User::factory()->role(UserRole::Admin)->create();
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create();
        $checklist = PreOperationalChecklist::factory()->create(['asset_id' => $asset->id]);

        $this->actingAs($admin);

        Livewire::test(AssetsShow::class, ['asset' => $asset])
            ->assertSee('Listas preoperacionales')
            ->assertSee($checklist->performedBy->name)
            ->call('exportPreOperationalChecklists')
            ->assertFileDownloaded("preoperacionales-{$asset->code}.xlsx");
    }
}
