<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\WorkOrderExecutionType;
use App\Enums\WorkOrderStatus;
use App\Livewire\Providers\Index as ProvidersIndex;
use App\Models\Area;
use App\Models\Asset;
use App\Models\Plant;
use App\Models\Provider;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProvidersIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_row_counts_only_open_work_orders_as_active(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $this->actingAs($admin);

        $provider = Provider::factory()->create();
        $plant = Plant::factory()->create();
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create();

        WorkOrder::factory()->create([
            'asset_id' => $asset->id, 'provider_id' => $provider->id,
            'execution_type' => WorkOrderExecutionType::Externo, 'status' => WorkOrderStatus::EnProgreso,
        ]);
        WorkOrder::factory()->create([
            'asset_id' => $asset->id, 'provider_id' => $provider->id,
            'execution_type' => WorkOrderExecutionType::Externo, 'status' => WorkOrderStatus::Completada,
        ]);

        Livewire::test(ProvidersIndex::class)->assertViewHas(
            'providers',
            fn ($providers) => $providers->firstWhere('id', $provider->id)->active_work_orders_count === 1
        );
    }
}
