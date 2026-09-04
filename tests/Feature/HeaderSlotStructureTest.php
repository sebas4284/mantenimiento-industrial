<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Area;
use App\Models\Asset;
use App\Models\Plant;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Throwaway structural check for Finding 1: wire:-bearing controls must render as
 * DESCENDANTS of the Livewire root element (the one carrying wire:id), otherwise
 * Livewire's closestComponent() walk finds no component and the binding is dead.
 *
 * symfony/dom-crawler is not installed in this project, so this uses PHP's built-in
 * DOMDocument/DOMXPath — same descendant assertion, no new dependency.
 */
class HeaderSlotStructureTest extends TestCase
{
    use RefreshDatabase;

    private function xpath(string $html): DOMXPath
    {
        $doc = new DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_clear_errors();

        return new DOMXPath($doc);
    }

    /**
     * XPath uses name()= comparisons because attributes like `wire:id` would otherwise
     * be read as a `wire` namespace prefix.
     */
    private function assertNestedInComponent(string $html, string $attribute, ?string $value, string $label): void
    {
        $predicate = $value === null
            ? sprintf('@*[name()="%s"]', $attribute)
            : sprintf('@*[name()="%s"]="%s"', $attribute, $value);

        $anywhere = $this->xpath($html)->query(sprintf('//*[%s]', $predicate));
        $nested = $this->xpath($html)->query(sprintf('//*[@*[name()="wire:id"]]//*[%s]', $predicate));

        $this->assertGreaterThan(0, $anywhere->length, "{$label}: element not present in the page at all.");
        $this->assertGreaterThan(
            0,
            $nested->length,
            "{$label}: element is present ({$anywhere->length} found) but NOT a descendant of any [wire:id] element — the Livewire binding is dead."
        );
    }

    private function admin(): User
    {
        return User::factory()->role(UserRole::Admin)->create();
    }

    public function test_dashboard_period_select_is_inside_the_livewire_component_root(): void
    {
        $response = $this->actingAs($this->admin())->get('/dashboard');
        $response->assertOk();

        $this->assertNestedInComponent($response->getContent(), 'wire:model.live', 'period', 'Dashboard period select');
    }

    public function test_work_orders_create_button_is_inside_the_livewire_component_root(): void
    {
        $response = $this->actingAs($this->admin())->get('/ordenes');
        $response->assertOk();

        $this->assertNestedInComponent($response->getContent(), 'wire:click', 'create', 'Ordenes "Crear reporte" button');
    }

    public function test_asset_show_history_button_is_inside_the_livewire_component_root(): void
    {
        $asset = Asset::factory()->for(Area::factory()->for(Plant::factory()))->create();

        $response = $this->actingAs($this->admin())->get("/activos/{$asset->id}");
        $response->assertOk();

        $this->assertNestedInComponent($response->getContent(), 'wire:click', 'openHistory', 'Asset detail "Ver historial" button');
    }

    public function test_admin_users_create_button_is_inside_the_livewire_component_root(): void
    {
        $response = $this->actingAs($this->admin())->get('/usuarios');
        $response->assertOk();

        $this->assertNestedInComponent($response->getContent(), 'wire:click', 'create', 'Usuarios "Nuevo usuario" button');
    }

    public function test_checklist_templates_create_button_is_inside_the_livewire_component_root(): void
    {
        $response = $this->actingAs($this->admin())->get('/checklists');
        $response->assertOk();

        $this->assertNestedInComponent($response->getContent(), 'wire:click', 'create', 'Checklists "Nuevo checklist" button');
    }

    public function test_team_index_search_is_inside_the_livewire_component_root(): void
    {
        $response = $this->actingAs($this->admin())->get('/equipo');
        $response->assertOk();

        $this->assertNestedInComponent($response->getContent(), 'wire:model.live.debounce.400ms', 'search', 'Equipo search input');
    }

    public function test_maintenance_plans_create_button_is_inside_the_livewire_component_root(): void
    {
        $response = $this->actingAs($this->admin())->get('/planes');
        $response->assertOk();

        $this->assertNestedInComponent($response->getContent(), 'wire:click', 'create', 'Planes "Nuevo plan" button');
    }

    public function test_spare_parts_search_and_create_are_inside_the_livewire_component_root(): void
    {
        $response = $this->actingAs($this->admin())->get('/inventario');
        $response->assertOk();

        $this->assertNestedInComponent($response->getContent(), 'wire:model.live.debounce.400ms', 'search', 'Inventario search input');
        $this->assertNestedInComponent($response->getContent(), 'wire:click', 'create', 'Inventario "Nuevo repuesto" button');
    }

    public function test_assets_index_create_button_is_inside_the_livewire_component_root(): void
    {
        $response = $this->actingAs($this->admin())->get('/activos');
        $response->assertOk();

        $this->assertNestedInComponent($response->getContent(), 'wire:click', 'create', 'Activos "Nuevo activo" button');
    }

    public function test_admin_plants_create_button_is_inside_the_livewire_component_root(): void
    {
        $response = $this->actingAs($this->admin())->get('/plantas');
        $response->assertOk();

        $this->assertNestedInComponent($response->getContent(), 'wire:click', 'createPlant', 'Plantas "+ Nueva planta" button');
    }

    public function test_pre_operational_checklists_index_asset_filter_is_inside_the_livewire_component_root(): void
    {
        $response = $this->actingAs($this->admin())->get('/preoperacionales');
        $response->assertOk();

        $this->assertNestedInComponent($response->getContent(), 'wire:model.live', 'assetFilter', 'Listas preoperacionales asset filter');
    }
}
