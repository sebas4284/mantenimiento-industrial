<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\WorkOrderExecutionType;
use App\Enums\WorkOrderPriority;
use App\Enums\WorkOrderStatus;
use App\Models\Area;
use App\Models\Asset;
use App\Models\ChecklistItem;
use App\Models\ChecklistTemplate;
use App\Models\MaintenancePlan;
use App\Models\Plant;
use App\Models\Provider;
use App\Models\SparePart;
use App\Models\SparePartUsage;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->role(UserRole::Admin)->create([
            'name' => 'Admin General',
            'email' => 'admin@mantenimiento.test',
        ]);

        User::factory()->role(UserRole::Corporativo)->create([
            'name' => 'Dirección Corporativa',
            'email' => 'corporativo@mantenimiento.test',
        ]);

        $this->call(PreOperationalItemSeeder::class);

        $checklistTemplates = ChecklistTemplate::factory()
            ->count(3)
            ->has(ChecklistItem::factory()->count(5), 'items')
            ->create();

        $providers = Provider::factory()->count(4)->create();

        foreach (['Planta Norte' => ['norte', 'PN'], 'Planta Sur' => ['sur', 'PS']] as $plantName => [$slug, $code]) {
            $plant = Plant::factory()->create(['name' => $plantName, 'code' => $code]);

            $this->seedPlant($plant, $slug, $checklistTemplates, $providers);
        }
    }

    private function seedPlant(Plant $plant, string $slug, Collection $checklistTemplates, Collection $providers): void
    {
        User::factory()->role(UserRole::Supervisor)->create([
            'name' => "Supervisor {$plant->name}",
            'email' => "supervisor.{$slug}@mantenimiento.test",
            'plant_id' => $plant->id,
        ]);

        $tecnicos = User::factory()
            ->role(UserRole::Tecnico)
            ->count(2)
            ->create(['plant_id' => $plant->id]);

        User::factory()->role(UserRole::Operador)->create([
            'name' => "Operador {$plant->name}",
            'email' => "operador.{$slug}@mantenimiento.test",
            'plant_id' => $plant->id,
        ]);

        $areas = Area::factory()
            ->count(3)
            ->create(['plant_id' => $plant->id]);

        $assets = $areas->flatMap(function (Area $area) {
            return Asset::factory()
                ->count(4)
                ->create(['area_id' => $area->id])
                ->each(function (Asset $asset) {
                    $asset->generateQrCode();
                    $asset->save();
                });
        });

        // A plan on roughly half the assets, tied to a shared checklist template.
        $assets->random((int) ceil($assets->count() / 2))->each(function (Asset $asset) use ($checklistTemplates) {
            MaintenancePlan::factory()->create([
                'asset_id' => $asset->id,
                'checklist_template_id' => $checklistTemplates->random()->id,
            ]);
        });

        $reporters = $tecnicos->concat(
            User::where('plant_id', $plant->id)->where('role', UserRole::Operador)->get()
        );

        $spareParts = SparePart::factory()->count(8)->create(['plant_id' => $plant->id]);
        // Force a couple of parts below their minimum, so the "stock bajo" alert has something to show.
        $spareParts->take(2)->each(fn (SparePart $part) => $part->update(['stock_quantity' => max(0, $part->minimum_stock - 2)]));

        $completedCorrectivas = collect();

        $assets->each(function (Asset $asset) use ($tecnicos, $reporters, $completedCorrectivas) {
            // A handful of closed correctivas spread over the last 6 months, for MTBF/MTTR.
            $completedCorrectivas->push(...WorkOrder::factory()
                ->count(random_int(2, 5))
                ->completed()
                ->create([
                    'asset_id' => $asset->id,
                    'reported_by' => $reporters->random()->id,
                    'assigned_to' => $tecnicos->random()->id,
                ]));

            // One currently open correctiva so the backlog isn't empty.
            if (random_int(0, 1) === 1) {
                WorkOrder::factory()->create([
                    'asset_id' => $asset->id,
                    'reported_by' => $reporters->random()->id,
                    'status' => WorkOrderStatus::Abierta,
                    'priority' => WorkOrderPriority::Alta,
                    'opened_at' => now()->subDays(random_int(1, 10)),
                ]);
            }
        });

        // A few of those completed correctivas consumed spare parts.
        $completedCorrectivas->random(min(5, $completedCorrectivas->count()))->each(function (WorkOrder $workOrder) use ($spareParts, $tecnicos) {
            SparePartUsage::factory()->create([
                'work_order_id' => $workOrder->id,
                'spare_part_id' => $spareParts->random()->id,
                'used_by' => $tecnicos->random()->id,
            ]);
        });

        // A few of those completed correctivas were handled by an external provider.
        $completedCorrectivas->random(min(6, $completedCorrectivas->count()))->each(function (WorkOrder $workOrder) use ($providers) {
            $workOrder->update([
                'execution_type' => WorkOrderExecutionType::Externo,
                'provider_id' => $providers->random()->id,
            ]);
        });

        // Preventive work orders generated from the plans, some completed on time.
        MaintenancePlan::whereHas('asset.area', fn ($q) => $q->where('plant_id', $plant->id))
            ->get()
            ->each(function (MaintenancePlan $plan) use ($tecnicos, $reporters) {
                WorkOrder::factory()
                    ->preventivo()
                    ->state(['opened_at' => now()->subDays($plan->frequency_days + 5)])
                    ->completed()
                    ->create([
                        'asset_id' => $plan->asset_id,
                        'maintenance_plan_id' => $plan->id,
                        'reported_by' => $reporters->random()->id,
                        'assigned_to' => $tecnicos->random()->id,
                    ]);
            });
    }
}
