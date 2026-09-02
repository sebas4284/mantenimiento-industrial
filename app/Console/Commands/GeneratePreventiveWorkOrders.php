<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Enums\WorkOrderPriority;
use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderType;
use App\Models\MaintenancePlan;
use App\Models\SparePart;
use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\LowStockNotification;
use App\Notifications\PreventiveMaintenanceDueNotification;
use App\Notifications\WorkOrderOverdueNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

#[Signature('app:generate-preventive-work-orders')]
#[Description('Generate preventive work orders from due maintenance plans and notify overdue work and upcoming preventives.')]
class GeneratePreventiveWorkOrders extends Command
{
    private const UPCOMING_WINDOW_DAYS = 3;

    private const OVERDUE_AFTER_DAYS = 3;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->generateDuePreventives();
        $this->notifyUpcomingPreventives();
        $this->notifyOverdueWorkOrders();
        $this->notifyLowStock();
    }

    private function generateDuePreventives(): void
    {
        MaintenancePlan::query()
            ->withoutGlobalScopes()
            ->where('active', true)
            ->where('next_due_date', '<=', now()->toDateString())
            ->with('asset.area.plant')
            ->each(function (MaintenancePlan $plan) {
                $reporter = $this->systemReporterFor($plan->asset->area->plant_id);

                if (! $reporter) {
                    return;
                }

                WorkOrder::create([
                    'asset_id' => $plan->asset_id,
                    'maintenance_plan_id' => $plan->id,
                    'reported_by' => $reporter->id,
                    'type' => WorkOrderType::Preventivo,
                    'priority' => WorkOrderPriority::Media,
                    'status' => WorkOrderStatus::Abierta,
                    'opened_at' => now(),
                ]);

                $plan->update(['next_due_date' => $plan->next_due_date->addDays($plan->frequency_days)]);

                $this->info("Generada OT preventiva para {$plan->asset->code} ({$plan->name}).");
            });
    }

    private function notifyUpcomingPreventives(): void
    {
        MaintenancePlan::query()
            ->withoutGlobalScopes()
            ->where('active', true)
            ->whereBetween('next_due_date', [now()->toDateString(), now()->addDays(self::UPCOMING_WINDOW_DAYS)->toDateString()])
            ->with('asset.area.plant')
            ->each(function (MaintenancePlan $plan) {
                $this->supervisorsFor($plan->asset->area->plant_id)
                    ->each(fn (User $supervisor) => $supervisor->notify(new PreventiveMaintenanceDueNotification($plan)));
            });
    }

    private function notifyOverdueWorkOrders(): void
    {
        WorkOrder::query()
            ->withoutGlobalScopes()
            ->whereIn('status', [WorkOrderStatus::Abierta, WorkOrderStatus::EnProgreso, WorkOrderStatus::EnEspera])
            ->where('opened_at', '<=', now()->subDays(self::OVERDUE_AFTER_DAYS))
            ->with('asset.area.plant', 'assignedTo')
            ->each(function (WorkOrder $workOrder) {
                $recipients = $this->supervisorsFor($workOrder->asset->area->plant_id);

                if ($workOrder->assignedTo) {
                    $recipients->push($workOrder->assignedTo);
                }

                $recipients->unique('id')->each(
                    fn (User $recipient) => $recipient->notify(new WorkOrderOverdueNotification($workOrder))
                );
            });
    }

    private function notifyLowStock(): void
    {
        SparePart::query()
            ->withoutGlobalScopes()
            ->whereColumn('stock_quantity', '<=', 'minimum_stock')
            ->each(function (SparePart $sparePart) {
                $this->supervisorsFor($sparePart->plant_id)
                    ->each(fn (User $supervisor) => $supervisor->notify(new LowStockNotification($sparePart)));
            });
    }

    private function systemReporterFor(int $plantId): ?User
    {
        return $this->supervisorsFor($plantId)->first()
            ?? User::where('role', UserRole::Admin)->first();
    }

    private function supervisorsFor(int $plantId): Collection
    {
        return User::where('plant_id', $plantId)->where('role', UserRole::Supervisor)->get();
    }
}
