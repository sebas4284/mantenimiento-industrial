<?php

use App\Livewire\Admin\Plants\Index as PlantsIndex;
use App\Livewire\Admin\Users\Index as UsersIndex;
use App\Livewire\Assets\Index as AssetsIndex;
use App\Livewire\Assets\Show as AssetsShow;
use App\Livewire\ChecklistTemplates\Index as ChecklistTemplatesIndex;
use App\Livewire\Dashboard;
use App\Livewire\MaintenancePlans\Index as MaintenancePlansIndex;
use App\Livewire\PreOperationalChecklists\Create as PreOperationalChecklistsCreate;
use App\Livewire\PreOperationalChecklists\Index as PreOperationalChecklistsIndex;
use App\Livewire\PreOperationalChecklists\Show as PreOperationalChecklistsShow;
use App\Livewire\Providers\Index as ProvidersIndex;
use App\Livewire\Providers\Show as ProvidersShow;
use App\Livewire\SpareParts\Index as SparePartsIndex;
use App\Livewire\Team\Index as TeamIndex;
use App\Livewire\Team\Show as TeamShow;
use App\Livewire\WorkOrders\Index as WorkOrdersIndex;
use App\Livewire\WorkOrders\QuickReport;
use App\Livewire\WorkOrders\Show as WorkOrderShow;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth'])->group(function () {
    Route::get('activos', AssetsIndex::class)->name('assets.index');
    Route::get('activos/{asset}', AssetsShow::class)->name('assets.show');

    Route::get('ordenes', WorkOrdersIndex::class)->name('work-orders.index');
    Route::get('ordenes/{workOrder}', WorkOrderShow::class)->name('work-orders.show');
    Route::get('reportar/{asset:code}', QuickReport::class)->name('work-orders.quick-report');

    Route::get('planes', MaintenancePlansIndex::class)->name('maintenance-plans.index');
    Route::get('checklists', ChecklistTemplatesIndex::class)->name('checklist-templates.index');

    Route::get('preoperacionales', PreOperationalChecklistsIndex::class)->name('pre-operational-checklists.index');
    Route::get('preoperacionales/nueva', PreOperationalChecklistsCreate::class)->name('pre-operational-checklists.create');
    Route::get('preoperacionales/{preOperationalChecklist}', PreOperationalChecklistsShow::class)->name('pre-operational-checklists.show');
    Route::get('inventario', SparePartsIndex::class)->name('spare-parts.index');
    Route::get('proveedores', ProvidersIndex::class)->name('providers.index');
    Route::get('proveedores/{provider}', ProvidersShow::class)->name('providers.show');

    Route::get('equipo', TeamIndex::class)->name('team.index');
    Route::get('equipo/{member}', TeamShow::class)->name('team.show');

    Route::get('usuarios', UsersIndex::class)->name('admin.users.index');
    Route::get('plantas', PlantsIndex::class)->name('admin.plants.index');
});

require __DIR__.'/auth.php';
