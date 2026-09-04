# Nocturne Redesign — Remaining 14 Screens Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Extend the already-shipped Nocturne dark design system to the 14 Livewire screens that were left on the old light Breeze/Tailwind styling, so no page shows a white table/card against the new dark shell.

**Architecture:** Pure view-layer restyling — no PHP component logic changes on any of the 14 screens (all `wire:submit`/`wire:click`/`@can`/`@if` behavior is preserved verbatim, only CSS classes and a few Blade-component swaps change). One small enabling change (Task 0) adds a `tagVariant()` method to 4 enums and a `<x-tag>` Blade component, replacing the old `<x-badge>` everywhere in scope.

**Tech Stack:** Laravel 11 (PHP 8.3), Livewire 3 + Volt, Tailwind CSS 3, the Nocturne component classes already defined in `resources/css/app.css`.

**Spec:** `C:\Users\Proyectos\.claude\plans\listo-ahora-en-algunas-harmonic-deer.md` (the approved plan-mode design doc this plan implements)

## Global Constraints

- Nocturne is the only theme — remove every `dark:`/`bg-white`/`bg-gray-*`/`text-gray-*`/`ring-gray-*`/`bg-indigo-*` class on every file touched; do not add new ones.
- **Hard rule, already the cause of one real production bug**: nothing inside `<x-slot name="header">` may carry a `wire:` directive (`wire:model`, `wire:click`, `wire:navigate`, `wire:confirm`, etc.) — that slot renders outside the Livewire component's `wire:id` DOM subtree, so any `wire:` binding there is silently dead. Every filter, search box, checkbox, and button with a `wire:` attribute goes in the component's own root div instead, typically a toolbar `<div class="flex flex-wrap items-center justify-between gap-3 mb-4">` right below where the header slot ends.
- No PHP file in `app/Livewire/**` changes in Tasks 1-14 (view-only). Task 0 touches 4 enum files (adding one method each, no existing method removed).
- No new Composer/npm dependency.
- Modal backdrop structure: two sibling children of `<div class="fixed inset-0 z-50 overflow-y-auto dialog-backdrop grid place-items-center p-4" wire:transition>` — first `<div class="fixed inset-0" wire:click="closeModal"></div>`, second `<div class="dialog relative">...</div>`. Never nest the dialog inside the click-catcher.
- Every `<table class="table">` sits inside `<div class="card elev-sm p-4"><div class="overflow-x-auto">...</div></div>` — `.table` has zero background/border of its own.
- Keep `<x-input-error>` as-is everywhere — it is not deprecated. Do not keep `<x-badge>`, `<x-input-label>`, `<x-text-input>`, `<x-primary-button>`, `<x-secondary-button>` in any touched file — replace with `<label>`+`.field`, `.input`, `.btn`/`.btn-primary`/`.btn-secondary`, or `<x-tag>`.
- Icons: Phosphor `<i class="ph ph-<name>">` (already installed, self-hosted).
- Run `vendor/bin/pint --dirty --format agent` after every task that touches a `.php` file (Task 0 only).
- Preserve every literal Spanish copy string exactly (button labels, empty-state text, table headers) unless a task explicitly says to change it.

---

### Task 0: `tagVariant()` enum methods + `<x-tag>` component

**Files:**
- Modify: `app/Enums/WorkOrderStatus.php`
- Modify: `app/Enums/WorkOrderPriority.php`
- Modify: `app/Enums/AssetStatus.php`
- Modify: `app/Enums/PreOperationalResult.php`
- Create: `resources/views/components/tag.blade.php`

**Interfaces:**
- Produces: `WorkOrderStatus::tagVariant(): string`, `WorkOrderPriority::tagVariant(): string`, `AssetStatus::tagVariant(): string`, `PreOperationalResult::tagVariant(): string` — each returns one of `'accent'|'neutral'|'outline'`, consumed by Tasks 6, 7, 8, 10, 12, 14 as `tag-{{ $x->tagVariant() }}`. Also produces `<x-tag :variant="...">` (optional convenience component — the plan primarily uses the bare `tag-{{ ... }}` class directly, so this component is available but not mandatory to use).
- Consumes: nothing.

- [ ] **Step 1: Add `tagVariant()` to `WorkOrderStatus`**

In `app/Enums/WorkOrderStatus.php`, add this method after the existing `isOpen()` method (keep `label()`, `color()`, `isOpen()` untouched):

```php
    public function tagVariant(): string
    {
        return match ($this) {
            self::Completada => 'neutral',
            self::Cancelada => 'outline',
            default => 'accent',
        };
    }
```

- [ ] **Step 2: Add `tagVariant()` to `WorkOrderPriority`**

In `app/Enums/WorkOrderPriority.php`, add after `color()`:

```php
    public function tagVariant(): string
    {
        return match ($this) {
            self::Urgente, self::Alta => 'accent',
            self::Media => 'outline',
            self::Baja => 'neutral',
        };
    }
```

- [ ] **Step 3: Add `tagVariant()` to `AssetStatus`**

In `app/Enums/AssetStatus.php`, add after `color()`:

```php
    public function tagVariant(): string
    {
        return match ($this) {
            self::Operativo => 'accent',
            self::Mantenimiento => 'outline',
            self::FueraServicio => 'neutral',
        };
    }
```

- [ ] **Step 4: Add `tagVariant()` to `PreOperationalResult`**

In `app/Enums/PreOperationalResult.php`, add after `color()`:

```php
    public function tagVariant(): string
    {
        return match ($this) {
            self::Apto => 'neutral',
            self::NoApto => 'accent',
        };
    }
```

- [ ] **Step 5: Create the `<x-tag>` component**

Create `resources/views/components/tag.blade.php`:

```blade
@props(['variant' => 'neutral'])

<span {{ $attributes->merge(['class' => "tag tag-$variant"]) }}>{{ $slot }}</span>
```

- [ ] **Step 6: Verify with a quick Tinker check**

Run: `php artisan tinker --execute='echo App\Enums\WorkOrderStatus::Completada->tagVariant(), " ", App\Enums\WorkOrderStatus::Abierta->tagVariant(), " ", App\Enums\WorkOrderPriority::Urgente->tagVariant(), " ", App\Enums\AssetStatus::Operativo->tagVariant(), " ", App\Enums\PreOperationalResult::NoApto->tagVariant();'`
Expected output: `neutral accent accent accent accent`

- [ ] **Step 7: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Enums/WorkOrderStatus.php app/Enums/WorkOrderPriority.php app/Enums/AssetStatus.php app/Enums/PreOperationalResult.php resources/views/components/tag.blade.php
git commit -m "feat: add Nocturne tagVariant() enum methods and <x-tag> component"
```

---

### Task 1: Admin\Users\Index

**Files:**
- Modify: `resources/views/livewire/admin/users/index.blade.php`

**Interfaces:**
- Consumes: nothing from Task 0 (this screen's only badge, Rol, stays literal `tag-neutral` per the plan — `UserRole` gets no `tagVariant()` method).

- [ ] **Step 1: Replace the view**

```blade
<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-users text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Usuarios</h1>
    </div>
</x-slot>

<div>
    <div class="flex justify-end mb-4">
        <button wire:click="create" class="btn btn-primary">
            <i class="ph ph-plus"></i> Nuevo usuario
        </button>
    </div>

    <div class="card elev-sm p-4">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Planta</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td class="font-medium text-ink">{{ $user->name }}</td>
                            <td class="text-muted">{{ $user->email }}</td>
                            <td><span class="tag tag-neutral">{{ $user->role->label() }}</span></td>
                            <td class="text-muted">{{ $user->plant?->name ?? 'Todas' }}</td>
                            <td class="text-right whitespace-nowrap">
                                <button wire:click="edit({{ $user->id }})" class="btn-ghost text-xs">Editar</button>
                                <button wire:click="delete({{ $user->id }})" wire:confirm="¿Eliminar este usuario?" class="text-xs text-neutral-400 hover:text-ink">Eliminar</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</div>

@if ($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto dialog-backdrop grid place-items-center p-4" wire:transition>
        <div class="fixed inset-0" wire:click="closeModal"></div>

        <div class="dialog relative">
            <h2 class="dialog-title">{{ $editing ? 'Editar usuario' : 'Nuevo usuario' }}</h2>

            <form wire:submit="save" class="flex flex-col gap-4">
                <div class="field">
                    <label>Nombre</label>
                    <input wire:model="name" class="input">
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div class="field">
                    <label>Correo</label>
                    <input wire:model="email" type="email" class="input">
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <div class="field">
                    <label>{{ $editing ? 'Nueva contraseña (opcional)' : 'Contraseña' }}</label>
                    <input wire:model="password" type="password" class="input">
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="field">
                        <label>Rol</label>
                        <select wire:model.live="role" class="input">
                            @foreach ($roles as $r)
                                <option value="{{ $r->value }}">{{ $r->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if (! \App\Enums\UserRole::from($role)->seesAllPlants())
                        <div class="field">
                            <label>Planta</label>
                            <select wire:model="plant_id" class="input">
                                <option value="">Selecciona una planta</option>
                                @foreach ($plants as $plant)
                                    <option value="{{ $plant->id }}">{{ $plant->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('plant_id')" class="mt-1" />
                        </div>
                    @endif
                </div>

                <div class="dialog-actions">
                    <button type="button" wire:click="closeModal" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endif
```

- [ ] **Step 2: Add a structural regression test**

In `tests/Feature/HeaderSlotStructureTest.php`, add this method (inside the class, after the existing 3 tests):

```php
    public function test_admin_users_create_button_is_inside_the_livewire_component_root(): void
    {
        $response = $this->actingAs($this->admin())->get('/usuarios');
        $response->assertOk();

        $this->assertNestedInComponent($response->getContent(), 'wire:click', 'create', 'Usuarios "Nuevo usuario" button');
    }
```

- [ ] **Step 3: Run tests**

Run: `php artisan test --filter=HeaderSlotStructureTest`
Expected: PASS (4 tests now).

- [ ] **Step 4: Manual verification**

As an Admin user, open `/usuarios`. Confirm: dark card table, "Nuevo usuario" button opens the dialog and creates a user, Editar/Eliminar work, role select in the modal correctly toggles the Planta field.

- [ ] **Step 5: Commit**

```bash
git add resources/views/livewire/admin/users/index.blade.php tests/Feature/HeaderSlotStructureTest.php
git commit -m "feat: redesign Usuarios in the Nocturne style"
```

---

### Task 2: ChecklistTemplates\Index

**Files:**
- Modify: `resources/views/livewire/checklist-templates/index.blade.php`

- [ ] **Step 1: Replace the view**

```blade
<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-list-checks text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Checklists reutilizables</h1>
    </div>
</x-slot>

<div>
    <div class="flex justify-end mb-4">
        @can('create', \App\Models\ChecklistTemplate::class)
            <button wire:click="create" class="btn btn-primary">
                <i class="ph ph-plus"></i> Nuevo checklist
            </button>
        @endcan
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @forelse ($templates as $template)
            <div wire:key="template-{{ $template->id }}" class="card elev-sm p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-medium text-ink m-0">{{ $template->name }}</h3>
                        <p class="text-xs text-neutral-500 m-0">{{ $template->items_count }} puntos de verificación</p>
                    </div>
                    <div class="flex gap-3">
                        @can('update', $template)
                            <button wire:click="edit({{ $template->id }})" class="btn-ghost text-xs">Editar</button>
                        @endcan
                        @can('delete', $template)
                            <button wire:click="delete({{ $template->id }})" wire:confirm="¿Eliminar este checklist?" class="text-xs text-neutral-400 hover:text-ink">Eliminar</button>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <p class="col-span-full text-center text-muted py-12">No hay checklists registrados.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $templates->links() }}</div>
</div>

@if ($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto dialog-backdrop grid place-items-center p-4" wire:transition>
        <div class="fixed inset-0" wire:click="closeModal"></div>

        <div class="dialog relative">
            <h2 class="dialog-title">{{ $editing ? 'Editar checklist' : 'Nuevo checklist' }}</h2>

            <form wire:submit="save" class="flex flex-col gap-4">
                <div class="field">
                    <label>Nombre del checklist</label>
                    <input wire:model="name" class="input">
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div class="field">
                    <label>Puntos de verificación</label>
                    <div class="mt-1 flex flex-col gap-2">
                        @foreach ($items as $index => $item)
                            <div class="flex gap-2" wire:key="item-{{ $index }}">
                                <input wire:model="items.{{ $index }}.label" class="input" placeholder="Ej. Revisar nivel de aceite">
                                <button type="button" wire:click="removeItem({{ $index }})" class="text-neutral-400 hover:text-ink px-2">&times;</button>
                            </div>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('items')" class="mt-1" />

                    <button type="button" wire:click="addItem" class="btn-ghost text-xs mt-2">+ Agregar punto</button>
                </div>

                <div class="dialog-actions">
                    <button type="button" wire:click="closeModal" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endif
```

- [ ] **Step 2: Add a structural regression test**

In `tests/Feature/HeaderSlotStructureTest.php`:

```php
    public function test_checklist_templates_create_button_is_inside_the_livewire_component_root(): void
    {
        $response = $this->actingAs($this->admin())->get('/checklists');
        $response->assertOk();

        $this->assertNestedInComponent($response->getContent(), 'wire:click', 'create', 'Checklists "Nuevo checklist" button');
    }
```

- [ ] **Step 3: Run tests**

Run: `php artisan test --filter=HeaderSlotStructureTest`
Expected: PASS (5 tests).

- [ ] **Step 4: Manual verification**

Open `/checklists`. Confirm dark cards, "Nuevo checklist" opens the dialog, add/remove item rows work, Editar/Eliminar work.

- [ ] **Step 5: Commit**

```bash
git add resources/views/livewire/checklist-templates/index.blade.php tests/Feature/HeaderSlotStructureTest.php
git commit -m "feat: redesign Checklists in the Nocturne style"
```

---

### Task 3: Team\Index

**Files:**
- Modify: `resources/views/livewire/team/index.blade.php`

**Interfaces:**
- Must preserve the exact literal strings `assertSee`'d by `tests/Feature/TeamTest.php`: technician names, `'Ocupado'`, `'Disponible'`.

- [ ] **Step 1: Replace the view**

```blade
<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-users-three text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Equipo de trabajo</h1>
    </div>
</x-slot>

<div>
    <div class="flex items-center justify-between gap-4 mb-4">
        <p class="text-sm text-neutral-400 m-0">Técnicos y supervisores de mantenimiento</p>
        <input wire:model.live.debounce.400ms="search" type="text" placeholder="Buscar por nombre..." class="input max-w-sm">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($members as $member)
            @php
                $isBusy = ($member->active_assigned_count + $member->active_support_count) > 0;
            @endphp
            <div wire:key="member-{{ $member->id }}" class="card elev-sm p-5 gap-2">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="font-medium text-ink m-0">
                            <a href="{{ route('team.show', $member) }}" wire:navigate class="text-ink hover:text-accent-300">{{ $member->name }}</a>
                        </h3>
                        <span class="tag tag-neutral">{{ $member->role->label() }}</span>
                    </div>
                    <span class="tag {{ $isBusy ? 'tag-accent' : 'tag-neutral' }}">{{ $isBusy ? 'Ocupado' : 'Disponible' }}</span>
                </div>

                <div class="text-sm text-muted">
                    <p class="m-0">{{ $member->email }}</p>
                    @if ($member->plant)
                        <p class="m-0">{{ $member->plant->name }}</p>
                    @endif
                </div>
            </div>
        @empty
            <p class="col-span-full text-center text-muted py-12">No hay colaboradores registrados todavía.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $members->links() }}</div>
</div>
```

- [ ] **Step 2: Add a structural regression test**

In `tests/Feature/HeaderSlotStructureTest.php`:

```php
    public function test_team_index_search_is_inside_the_livewire_component_root(): void
    {
        $response = $this->actingAs($this->admin())->get('/equipo');
        $response->assertOk();

        $this->assertNestedInComponent($response->getContent(), 'wire:model.live.debounce.400ms', 'search', 'Equipo search input');
    }
```

- [ ] **Step 3: Run tests**

Run: `php artisan test --filter=HeaderSlotStructureTest` (expect PASS, 6 tests) and `php artisan test --filter=TeamTest` (expect PASS unchanged — this is the direct copy-preservation check).

- [ ] **Step 4: Manual verification**

Open `/equipo` as Admin. Confirm dark cards, search filters live, Ocupado/Disponible tags render, clicking a name navigates to the member's detail page.

- [ ] **Step 5: Commit**

```bash
git add resources/views/livewire/team/index.blade.php tests/Feature/HeaderSlotStructureTest.php
git commit -m "feat: redesign Equipo de trabajo (index) in the Nocturne style"
```

---

### Task 4: MaintenancePlans\Index

**Files:**
- Modify: `resources/views/livewire/maintenance-plans/index.blade.php`

- [ ] **Step 1: Replace the view**

```blade
<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-calendar-check text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Planes de mantenimiento preventivo</h1>
    </div>
</x-slot>

<div>
    <div class="flex justify-end mb-4">
        @can('create', \App\Models\MaintenancePlan::class)
            <button wire:click="create" class="btn btn-primary">
                <i class="ph ph-plus"></i> Nuevo plan
            </button>
        @endcan
    </div>

    <div class="card elev-sm p-4">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Activo</th>
                        <th>Frecuencia</th>
                        <th>Próxima fecha</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($plans as $plan)
                        <tr wire:key="plan-{{ $plan->id }}">
                            <td>
                                <p class="font-medium text-ink m-0">{{ $plan->name }}</p>
                                @if ($plan->checklistTemplate)
                                    <p class="text-xs text-neutral-500 m-0">Checklist: {{ $plan->checklistTemplate->name }}</p>
                                @endif
                            </td>
                            <td class="text-muted">
                                {{ $plan->asset->code }} — {{ $plan->asset->name }}
                                <p class="text-xs text-neutral-500 m-0">{{ $plan->asset->area->plant->name }}</p>
                            </td>
                            <td class="text-muted">cada {{ $plan->frequency_days }} días</td>
                            <td class="text-muted">
                                {{ $plan->next_due_date->format('d/m/Y') }}
                                @if ($plan->next_due_date->isPast())
                                    <span class="tag tag-accent">Vencido</span>
                                @endif
                            </td>
                            <td>
                                <span class="tag {{ $plan->active ? 'tag-accent' : 'tag-neutral' }}">{{ $plan->active ? 'Activo' : 'Inactivo' }}</span>
                            </td>
                            <td class="text-right whitespace-nowrap">
                                @can('update', $plan)
                                    <button wire:click="edit({{ $plan->id }})" class="btn-ghost text-xs">Editar</button>
                                @endcan
                                @can('delete', $plan)
                                    <button wire:click="delete({{ $plan->id }})" wire:confirm="¿Eliminar este plan?" class="text-xs text-neutral-400 hover:text-ink">Eliminar</button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-8">No hay planes registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $plans->links() }}</div>
</div>

@if ($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto dialog-backdrop grid place-items-center p-4" wire:transition>
        <div class="fixed inset-0" wire:click="closeModal"></div>

        <div class="dialog relative">
            <h2 class="dialog-title">{{ $editing ? 'Editar plan' : 'Nuevo plan' }}</h2>

            <form wire:submit="save" class="flex flex-col gap-4">
                <div class="field">
                    <label>Nombre del plan</label>
                    <input wire:model="name" class="input">
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div class="field">
                    <label>Activo</label>
                    <select wire:model="asset_id" class="input">
                        <option value="">Selecciona un activo</option>
                        @foreach ($assets as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->code }} — {{ $asset->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('asset_id')" class="mt-1" />
                </div>

                <div class="field">
                    <label>Checklist (opcional)</label>
                    <select wire:model="checklist_template_id" class="input">
                        <option value="">Sin checklist</option>
                        @foreach ($templates as $template)
                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="field">
                        <label>Frecuencia (días)</label>
                        <input wire:model="frequency_days" type="number" min="1" class="input">
                        <x-input-error :messages="$errors->get('frequency_days')" class="mt-1" />
                    </div>
                    <div class="field">
                        <label>Próxima fecha</label>
                        <input wire:model="next_due_date" type="date" class="input">
                        <x-input-error :messages="$errors->get('next_due_date')" class="mt-1" />
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-neutral-400">
                    <input type="checkbox" wire:model="active" class="rounded border-neutral-700 bg-surface text-accent-500 focus:ring-accent-500">
                    Plan activo
                </label>

                <div class="dialog-actions">
                    <button type="button" wire:click="closeModal" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endif
```

- [ ] **Step 2: Add a structural regression test**

```php
    public function test_maintenance_plans_create_button_is_inside_the_livewire_component_root(): void
    {
        $response = $this->actingAs($this->admin())->get('/planes');
        $response->assertOk();

        $this->assertNestedInComponent($response->getContent(), 'wire:click', 'create', 'Planes "Nuevo plan" button');
    }
```

- [ ] **Step 3: Run tests**

Run: `php artisan test --filter=HeaderSlotStructureTest`
Expected: PASS (7 tests).

- [ ] **Step 4: Manual verification**

Open `/planes`. Confirm dark table, Vencido/Activo/Inactivo tags render correctly, "Nuevo plan" opens the dialog and saves, Editar/Eliminar work.

- [ ] **Step 5: Commit**

```bash
git add resources/views/livewire/maintenance-plans/index.blade.php tests/Feature/HeaderSlotStructureTest.php
git commit -m "feat: redesign Planes de mantenimiento in the Nocturne style"
```

---

### Task 5: SpareParts\Index

**Files:**
- Modify: `resources/views/livewire/spare-parts/index.blade.php`

- [ ] **Step 1: Replace the view**

```blade
<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-package text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Inventario de repuestos</h1>
    </div>
</x-slot>

<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <div class="flex flex-wrap items-center gap-3">
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Buscar por código o nombre..." class="input w-72">

            <label class="flex items-center gap-2 text-sm text-neutral-400">
                <input type="checkbox" wire:model.live="lowStockOnly" class="rounded border-neutral-700 bg-surface text-accent-500 focus:ring-accent-500">
                Solo stock bajo
            </label>
        </div>

        @can('create', \App\Models\SparePart::class)
            <button wire:click="create" class="btn btn-primary">
                <i class="ph ph-plus"></i> Nuevo repuesto
            </button>
        @endcan
    </div>

    <div class="card elev-sm p-4">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Repuesto</th>
                        <th>Planta</th>
                        <th>Stock</th>
                        <th>Mínimo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($spareParts as $part)
                        <tr wire:key="part-{{ $part->id }}">
                            <td>
                                <p class="font-medium text-ink m-0">{{ $part->name }}</p>
                                <p class="text-xs font-mono text-neutral-500 m-0">{{ $part->code }}</p>
                            </td>
                            <td class="text-muted">{{ $part->plant->name }}</td>
                            <td class="text-muted">{{ $part->stock_quantity }}</td>
                            <td class="text-muted">
                                {{ $part->minimum_stock }}
                                @if ($part->isLowStock())
                                    <span class="tag tag-accent">Stock bajo</span>
                                @endif
                            </td>
                            <td class="text-right whitespace-nowrap">
                                @can('update', $part)
                                    <button wire:click="edit({{ $part->id }})" class="btn-ghost text-xs">Editar</button>
                                @endcan
                                @can('delete', $part)
                                    <button wire:click="delete({{ $part->id }})" wire:confirm="¿Eliminar este repuesto?" class="text-xs text-neutral-400 hover:text-ink">Eliminar</button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-8">No hay repuestos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $spareParts->links() }}</div>
</div>

@if ($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto dialog-backdrop grid place-items-center p-4" wire:transition>
        <div class="fixed inset-0" wire:click="closeModal"></div>

        <div class="dialog relative">
            <h2 class="dialog-title">{{ $editing ? 'Editar repuesto' : 'Nuevo repuesto' }}</h2>

            <form wire:submit="save" class="flex flex-col gap-4">
                <div class="field">
                    <label>Planta</label>
                    <select wire:model="plant_id" class="input">
                        <option value="">Selecciona una planta</option>
                        @foreach ($plants as $plant)
                            <option value="{{ $plant->id }}">{{ $plant->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('plant_id')" class="mt-1" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="field">
                        <label>Código</label>
                        <input wire:model="code" class="input">
                        <x-input-error :messages="$errors->get('code')" class="mt-1" />
                    </div>
                    <div class="field">
                        <label>Nombre</label>
                        <input wire:model="name" class="input">
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="field">
                        <label>Stock actual</label>
                        <input wire:model="stock_quantity" type="number" min="0" class="input">
                        <x-input-error :messages="$errors->get('stock_quantity')" class="mt-1" />
                    </div>
                    <div class="field">
                        <label>Stock mínimo</label>
                        <input wire:model="minimum_stock" type="number" min="0" class="input">
                        <x-input-error :messages="$errors->get('minimum_stock')" class="mt-1" />
                    </div>
                </div>

                <div class="dialog-actions">
                    <button type="button" wire:click="closeModal" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endif
```

- [ ] **Step 2: Add structural regression tests**

```php
    public function test_spare_parts_search_and_create_are_inside_the_livewire_component_root(): void
    {
        $response = $this->actingAs($this->admin())->get('/inventario');
        $response->assertOk();

        $this->assertNestedInComponent($response->getContent(), 'wire:model.live.debounce.400ms', 'search', 'Inventario search input');
        $this->assertNestedInComponent($response->getContent(), 'wire:click', 'create', 'Inventario "Nuevo repuesto" button');
    }
```

- [ ] **Step 3: Run tests**

Run: `php artisan test --filter=HeaderSlotStructureTest`
Expected: PASS (8 tests).

- [ ] **Step 4: Manual verification**

Open `/inventario`. Confirm dark table, search + "Solo stock bajo" checkbox both filter live, Stock bajo tag renders, create/edit/delete work.

- [ ] **Step 5: Commit**

```bash
git add resources/views/livewire/spare-parts/index.blade.php tests/Feature/HeaderSlotStructureTest.php
git commit -m "feat: redesign Inventario de repuestos in the Nocturne style"
```

---

### Task 6: Assets\Index

**Files:**
- Modify: `resources/views/livewire/assets/index.blade.php`

**Interfaces:**
- Consumes: `AssetStatus::tagVariant()` (Task 0).

- [ ] **Step 1: Replace the view**

```blade
<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-gear-six text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Activos</h1>
    </div>
</x-slot>

<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <div class="flex flex-wrap items-center gap-3">
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Buscar por código o nombre..." class="input w-72">

            <select wire:model.live="areaFilter" class="input w-auto">
                <option value="">Todas las áreas</option>
                @foreach ($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->plant->name }} — {{ $area->name }}</option>
                @endforeach
            </select>
        </div>

        @can('create', \App\Models\Asset::class)
            <button wire:click="create" class="btn btn-primary">
                <i class="ph ph-plus"></i> Nuevo activo
            </button>
        @endcan
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($assets as $asset)
            <div wire:key="asset-{{ $asset->id }}" class="card elev-sm p-5 gap-3">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-mono text-neutral-500 m-0">{{ $asset->code }}</p>
                        <h3 class="font-medium text-ink m-0">
                            <a href="{{ route('assets.show', $asset) }}" wire:navigate class="text-ink hover:text-accent-300">{{ $asset->name }}</a>
                        </h3>
                        <p class="text-xs text-neutral-500 m-0">{{ $asset->area->plant->name }} — {{ $asset->area->name }}</p>
                    </div>
                    @if ($asset->qr_code_path)
                        <img src="{{ Storage::disk('public')->url($asset->qr_code_path) }}" alt="QR {{ $asset->code }}" class="h-16 w-16 rounded bg-white p-1">
                    @endif
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="tag tag-{{ $asset->status->tagVariant() }}">{{ $asset->status->label() }}</span>
                    <span class="tag tag-neutral">Criticidad {{ $asset->criticality->value }}</span>
                </div>

                <div class="mt-auto flex items-center justify-between pt-2 border-t border-neutral-800">
                    @if ($asset->qr_code_path)
                        <a href="{{ Storage::disk('public')->url($asset->qr_code_path) }}" target="_blank" class="text-xs text-accent-300">Ver / imprimir QR</a>
                    @else
                        <span class="text-xs text-neutral-500">Sin QR</span>
                    @endif

                    <div class="flex gap-3">
                        @can('update', $asset)
                            <button wire:click="edit({{ $asset->id }})" class="btn-ghost text-xs">Editar</button>
                        @endcan
                        @can('delete', $asset)
                            <button wire:click="delete({{ $asset->id }})" wire:confirm="¿Eliminar este activo?" class="text-xs text-neutral-400 hover:text-ink">Eliminar</button>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <p class="col-span-full text-center text-muted py-12">No hay activos registrados todavía.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $assets->links() }}</div>
</div>

@if ($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto dialog-backdrop grid place-items-center p-4" wire:transition>
        <div class="fixed inset-0" wire:click="closeModal"></div>

        <div class="dialog relative" style="width:min(560px, 100%);">
            <h2 class="dialog-title">{{ $editing ? 'Editar activo' : 'Nuevo activo' }}</h2>

            <form wire:submit="save" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="field sm:col-span-2">
                    <label>Área</label>
                    <select wire:model="area_id" class="input">
                        <option value="">Selecciona un área</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id }}">{{ $area->plant->name }} — {{ $area->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('area_id')" class="mt-1" />
                </div>

                <div class="field">
                    <label>Código</label>
                    <input wire:model="code" class="input">
                    <x-input-error :messages="$errors->get('code')" class="mt-1" />
                </div>

                <div class="field">
                    <label>Nombre</label>
                    <input wire:model="name" class="input">
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div class="field">
                    <label>Fabricante</label>
                    <input wire:model="manufacturer" class="input">
                </div>

                <div class="field">
                    <label>Modelo</label>
                    <input wire:model="model" class="input">
                </div>

                <div class="field">
                    <label>Número de serie</label>
                    <input wire:model="serial_number" class="input">
                </div>

                <div class="field">
                    <label>Criticidad</label>
                    <select wire:model="criticality" class="input">
                        @foreach ($criticalities as $c)
                            <option value="{{ $c->value }}">{{ $c->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label>Estado</label>
                    <select wire:model="status" class="input">
                        @foreach ($statuses as $s)
                            <option value="{{ $s->value }}">{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field sm:col-span-2">
                    <label>Foto (opcional)</label>
                    <input type="file" wire:model="photo" class="input">
                    <x-input-error :messages="$errors->get('photo')" class="mt-1" />
                </div>

                <div class="dialog-actions sm:col-span-2">
                    <button type="button" wire:click="closeModal" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endif
```

- [ ] **Step 2: Add a structural regression test**

```php
    public function test_assets_index_create_button_is_inside_the_livewire_component_root(): void
    {
        $response = $this->actingAs($this->admin())->get('/activos');
        $response->assertOk();

        $this->assertNestedInComponent($response->getContent(), 'wire:click', 'create', 'Activos "Nuevo activo" button');
    }
```

- [ ] **Step 3: Run tests**

Run: `php artisan test --filter=HeaderSlotStructureTest`
Expected: PASS (9 tests).

- [ ] **Step 4: Manual verification**

Open `/activos`. Confirm dark cards, search + area filter work, status tag color reflects `tagVariant()` correctly for Operativo/Mantenimiento/FueraServicio, QR thumbnail keeps its white background, create/edit (with photo upload) work.

- [ ] **Step 5: Commit**

```bash
git add resources/views/livewire/assets/index.blade.php tests/Feature/HeaderSlotStructureTest.php
git commit -m "feat: redesign Activos (index) in the Nocturne style"
```

---

### Task 7: Providers\Show

**Files:**
- Modify: `resources/views/livewire/providers/show.blade.php`

**Interfaces:**
- Consumes: `WorkOrderStatus::tagVariant()` (Task 0). No `wire:` controls on this screen — dynamic header title is safe (plain Blade interpolation, same pattern already proven by `assets/show.blade.php`).

- [ ] **Step 1: Replace the view**

```blade
<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-truck text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">{{ $provider->name }}</h1>
    </div>
</x-slot>

<div class="space-y-4">
    <a href="{{ route('providers.index') }}" wire:navigate class="text-sm text-accent-300">&larr; Volver a proveedores</a>

    <div class="card elev-sm p-6">
        @if ($provider->specialty)
            <span class="tag tag-neutral">{{ $provider->specialty }}</span>
        @endif

        <dl class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <dt class="text-neutral-500">Contacto</dt>
                <dd class="text-ink">{{ $provider->contact_name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-neutral-500">Teléfono</dt>
                <dd class="text-ink">{{ $provider->phone ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-neutral-500">Correo</dt>
                <dd class="text-ink">{{ $provider->email ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-neutral-500">Dirección</dt>
                <dd class="text-ink">{{ $provider->address ?? '—' }}</dd>
            </div>
        </dl>
    </div>

    <div class="card elev-sm p-6">
        <h2 class="card-title m-0 mb-3">Historial de mantenimientos atendidos</h2>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° Orden</th><th>Activo</th><th>Planta</th><th>Fecha</th><th>Tipo</th><th>Estado</th><th>Duración total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($workOrders as $wo)
                        <tr wire:key="wo-{{ $wo->id }}" class="cursor-pointer" onclick="window.location='{{ route('work-orders.show', $wo) }}'">
                            <td class="font-mono text-xs text-accent-300">{{ $wo->order_number }}</td>
                            <td class="text-ink">{{ $wo->asset->code }} — {{ $wo->asset->name }}</td>
                            <td class="text-muted">{{ $wo->asset->area->plant->name }}</td>
                            <td class="text-muted">{{ $wo->opened_at->format('d/m/Y H:i') }}</td>
                            <td><span class="tag tag-neutral">{{ $wo->type->label() }}</span></td>
                            <td><span class="tag tag-{{ $wo->status->tagVariant() }}">{{ $wo->status->label() }}</span></td>
                            <td class="text-muted">{{ $wo->status->isOpen() ? 'En curso' : \App\Models\WorkOrder::formatDurationMinutes($wo->total_minutes) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-8">Este proveedor no tiene mantenimientos registrados todavía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
```

- [ ] **Step 2: Run existing tests**

Run: `php artisan test --filter=ProvidersIndexTest`
Expected: PASS unchanged (different component, sanity check only — this screen has no dedicated test of its own).

- [ ] **Step 3: Manual verification**

Open a real provider's detail page (`/proveedores/{id}`, reachable from the Proveedores list). Confirm dynamic header title renders correctly, specialty tag, contact `<dl>`, and the history table (with tags) all render in dark theme.

- [ ] **Step 4: Commit**

```bash
git add resources/views/livewire/providers/show.blade.php
git commit -m "feat: redesign Proveedores (detalle) in the Nocturne style"
```

---

### Task 8: Team\Show

**Files:**
- Modify: `resources/views/livewire/team/show.blade.php`

**Interfaces:**
- Consumes: `WorkOrderStatus::tagVariant()` (Task 0). Must preserve `assertSee('Principal')` / `assertSee('Apoyo')` from `tests/Feature/TeamTest.php` exactly.

- [ ] **Step 1: Replace the view**

```blade
<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-users-three text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">{{ $member->name }}</h1>
    </div>
</x-slot>

<div class="space-y-4">
    <a href="{{ route('team.index') }}" wire:navigate class="text-sm text-accent-300">&larr; Volver a equipo de trabajo</a>

    <div class="card elev-sm p-6">
        <div class="flex gap-2">
            <span class="tag tag-neutral">{{ $member->role->label() }}</span>
            <span class="tag {{ $isBusy ? 'tag-accent' : 'tag-neutral' }}">{{ $isBusy ? 'Ocupado' : 'Disponible' }}</span>
        </div>

        <dl class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <dt class="text-neutral-500">Correo</dt>
                <dd class="text-ink">{{ $member->email }}</dd>
            </div>
            <div>
                <dt class="text-neutral-500">Planta</dt>
                <dd class="text-ink">{{ $member->plant->name ?? '—' }}</dd>
            </div>
        </dl>
    </div>

    <div class="card elev-sm p-6">
        <h2 class="card-title m-0 mb-3">Historial de mantenimientos realizados</h2>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° Orden</th><th>Activo</th><th>Planta</th><th>Fecha</th><th>Rol</th><th>Estado</th><th>Duración total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($workOrders as $wo)
                        <tr wire:key="wo-{{ $wo->id }}" class="cursor-pointer" onclick="window.location='{{ route('work-orders.show', $wo) }}'">
                            <td class="font-mono text-xs text-accent-300">{{ $wo->order_number }}</td>
                            <td class="text-ink">{{ $wo->asset->code }} — {{ $wo->asset->name }}</td>
                            <td class="text-muted">{{ $wo->asset->area->plant->name }}</td>
                            <td class="text-muted">{{ $wo->opened_at->format('d/m/Y H:i') }}</td>
                            <td><span class="tag tag-neutral">{{ $wo->collaboratorRole }}</span></td>
                            <td><span class="tag tag-{{ $wo->status->tagVariant() }}">{{ $wo->status->label() }}</span></td>
                            <td class="text-muted">{{ $wo->status->isOpen() ? 'En curso' : \App\Models\WorkOrder::formatDurationMinutes($wo->total_minutes) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-8">Este colaborador no tiene mantenimientos registrados todavía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
```

- [ ] **Step 2: Run existing tests**

Run: `php artisan test --filter=TeamTest`
Expected: PASS unchanged (this is the direct copy-preservation check for `Principal`/`Apoyo`/order numbers).

- [ ] **Step 3: Manual verification**

Open a real team member's detail page (`/equipo/{id}`) as Admin. Confirm dynamic header title, role/availability tags, and history table (with Principal/Apoyo tags) render in dark theme.

- [ ] **Step 4: Commit**

```bash
git add resources/views/livewire/team/show.blade.php
git commit -m "feat: redesign Equipo de trabajo (detalle) in the Nocturne style"
```

---

### Task 9: Admin\Plants\Index

**Files:**
- Modify: `resources/views/livewire/admin/plants/index.blade.php`

**Interfaces:**
- The page-level header slot shows "Plantas"; the left column's own former `<h1>Plantas</h1>` is removed (redundant with the header) and its `wire:click="createPlant"` link stays in-body as a small toolbar row. The right column's `<h2>Áreas</h2>` stays as an in-body section label — it is not the page title, so it is unaffected by the header-slot constraint.

- [ ] **Step 1: Replace the view**

```blade
<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-factory text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Plantas</h1>
    </div>
</x-slot>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <div class="flex justify-end mb-3">
            <button wire:click="createPlant" class="btn-ghost text-sm">+ Nueva planta</button>
        </div>

        <div class="flex flex-col gap-2">
            @foreach ($plants as $plant)
                <div wire:key="plant-{{ $plant->id }}"
                    class="card elev-sm p-4 cursor-pointer {{ $selectedPlantId === $plant->id ? 'border border-accent-600' : '' }}"
                    wire:click="selectPlant({{ $plant->id }})">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-ink m-0">{{ $plant->name }} <span class="font-mono text-xs text-neutral-500">({{ $plant->code }})</span></p>
                            <p class="text-xs text-neutral-500 m-0">{{ $plant->location }} · {{ $plant->areas_count }} áreas</p>
                        </div>
                        <div class="flex gap-3">
                            <button wire:click.stop="editPlant({{ $plant->id }})" class="btn-ghost text-xs">Editar</button>
                            <button wire:click.stop="deletePlant({{ $plant->id }})" wire:confirm="¿Eliminar esta planta y todas sus áreas/activos?" class="text-xs text-neutral-400 hover:text-ink">Eliminar</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="card-title m-0">Áreas</h2>
            @if ($selectedPlantId)
                <button wire:click="createArea" class="btn-ghost text-sm">+ Nueva área</button>
            @endif
        </div>

        @if (! $selectedPlantId)
            <p class="text-sm text-neutral-400">Selecciona una planta para gestionar sus áreas.</p>
        @else
            <div class="flex flex-col gap-2">
                @forelse ($areas as $area)
                    <div wire:key="area-{{ $area->id }}" class="card elev-sm p-4 flex-row items-center justify-between">
                        <div>
                            <p class="font-medium text-ink m-0">{{ $area->name }}</p>
                            <p class="text-xs text-neutral-500 m-0">{{ $area->assets_count }} activos</p>
                        </div>
                        <div class="flex gap-3">
                            <button wire:click="editArea({{ $area->id }})" class="btn-ghost text-xs">Editar</button>
                            <button wire:click="deleteArea({{ $area->id }})" wire:confirm="¿Eliminar esta área y sus activos?" class="text-xs text-neutral-400 hover:text-ink">Eliminar</button>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-neutral-400">Esta planta no tiene áreas todavía.</p>
                @endforelse
            </div>
        @endif
    </div>
</div>

@if ($showPlantModal)
    <div class="fixed inset-0 z-50 overflow-y-auto dialog-backdrop grid place-items-center p-4" wire:transition>
        <div class="fixed inset-0" wire:click="$set('showPlantModal', false)"></div>
        <div class="dialog relative">
            <h2 class="dialog-title">{{ $editingPlant ? 'Editar planta' : 'Nueva planta' }}</h2>
            <form wire:submit="savePlant" class="flex flex-col gap-4">
                <div class="field">
                    <label>Nombre</label>
                    <input wire:model="plantName" class="input">
                    <x-input-error :messages="$errors->get('plantName')" class="mt-1" />
                </div>
                <div class="field">
                    <label>Ubicación</label>
                    <input wire:model="plantLocation" class="input">
                </div>
                <div class="dialog-actions">
                    <button type="button" wire:click="$set('showPlantModal', false)" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endif

@if ($showAreaModal)
    <div class="fixed inset-0 z-50 overflow-y-auto dialog-backdrop grid place-items-center p-4" wire:transition>
        <div class="fixed inset-0" wire:click="$set('showAreaModal', false)"></div>
        <div class="dialog relative">
            <h2 class="dialog-title">{{ $editingArea ? 'Editar área' : 'Nueva área' }}</h2>
            <form wire:submit="saveArea" class="flex flex-col gap-4">
                <div class="field">
                    <label>Nombre</label>
                    <input wire:model="areaName" class="input">
                    <x-input-error :messages="$errors->get('areaName')" class="mt-1" />
                </div>
                <div class="dialog-actions">
                    <button type="button" wire:click="$set('showAreaModal', false)" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endif
```

- [ ] **Step 2: Add a structural regression test**

```php
    public function test_admin_plants_create_button_is_inside_the_livewire_component_root(): void
    {
        $response = $this->actingAs($this->admin())->get('/plantas');
        $response->assertOk();

        $this->assertNestedInComponent($response->getContent(), 'wire:click', 'createPlant', 'Plantas "+ Nueva planta" button');
    }
```

- [ ] **Step 3: Run tests**

Run: `php artisan test --filter=HeaderSlotStructureTest`
Expected: PASS (10 tests).

- [ ] **Step 4: Manual verification**

Open `/plantas` as Admin. Confirm: page header shows "Plantas", left column plant cards select correctly (accent border on the selected one), right column shows that plant's areas, create/edit/delete work for both plants and areas.

- [ ] **Step 5: Commit**

```bash
git add resources/views/livewire/admin/plants/index.blade.php tests/Feature/HeaderSlotStructureTest.php
git commit -m "feat: redesign Plantas in the Nocturne style"
```

---

### Task 10: PreOperationalChecklists\Index

**Files:**
- Modify: `resources/views/livewire/pre-operational-checklists/index.blade.php`

**Interfaces:**
- Consumes: `PreOperationalResult::tagVariant()` (Task 0). Keeps the real `grid lg:grid-cols-4` structure (3-col content + 1-col sidebar) — only the cards' internal skin changes.

- [ ] **Step 1: Replace the view**

```blade
<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-shield-check text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Listas preoperacionales</h1>
    </div>
</x-slot>

<div>
    <div class="flex items-center justify-between gap-4 mb-4">
        <p class="text-sm text-neutral-400 m-0">Inspecciones de seguridad registradas antes de iniciar turno</p>

        @can('create', \App\Models\PreOperationalChecklist::class)
            <a href="{{ route('pre-operational-checklists.create') }}" wire:navigate class="btn btn-primary">
                <i class="ph ph-plus"></i> Nueva lista
            </a>
        @endcan
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
        <div class="lg:col-span-3 flex flex-col gap-4">
            <div class="card elev-sm p-4">
                <div class="flex flex-wrap items-end gap-3">
                    <select wire:model.live="assetFilter" class="input w-auto">
                        <option value="">Todos los activos</option>
                        @foreach ($assets as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->code }} — {{ $asset->name }}</option>
                        @endforeach
                    </select>

                    @if ($selectedYear)
                        <button wire:click="clearPeriodFilter" class="text-xs text-neutral-400 hover:text-ink">
                            Quitar filtro de {{ $selectedMonth ? \Carbon\Carbon::create()->month($selectedMonth)->translatedFormat('F') : '' }} {{ $selectedYear }} &times;
                        </button>
                    @endif

                    <div class="ml-auto flex flex-wrap items-end gap-3">
                        <div class="field">
                            <label>Desde</label>
                            <input wire:model="exportFrom" type="date" class="input">
                        </div>
                        <div class="field">
                            <label>Hasta</label>
                            <input wire:model="exportTo" type="date" class="input">
                        </div>
                        <button wire:click="exportExcel" class="btn btn-secondary">Descargar Excel</button>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('exportTo')" class="mt-2" />
            </div>

            <div class="card elev-sm p-4">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th><th>Activo</th><th>Resultado</th><th>Acción requerida</th><th>Responsable</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($checklists as $checklist)
                                <tr wire:key="pc-{{ $checklist->id }}" class="cursor-pointer" onclick="window.location='{{ route('pre-operational-checklists.show', $checklist) }}'">
                                    <td class="text-muted">{{ $checklist->inspected_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-ink">{{ $checklist->asset->code }} — {{ $checklist->asset->name }}</td>
                                    <td><span class="tag tag-{{ $checklist->result->tagVariant() }}">{{ $checklist->result->label() }}</span></td>
                                    <td class="text-muted">{{ $checklist->required_action->label() }}</td>
                                    <td class="text-muted">{{ $checklist->performedBy->name }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-8">No hay listas preoperacionales registradas todavía.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $checklists->links() }}</div>
            </div>
        </div>

        <div class="card elev-sm p-4">
            <h3 class="card-title m-0">Filtrar por periodo</h3>

            <div class="mt-3 flex flex-col gap-1">
                @forelse ($periods as $year => $months)
                    <div>
                        <button wire:click="selectYear({{ $year }})" class="w-full flex items-center justify-between text-sm py-1.5 px-2 rounded-md {{ (int) $selectedYear === (int) $year ? 'bg-accent-500/20 text-accent-300' : 'text-neutral-400 hover:text-ink' }}">
                            {{ $year }}
                            <i class="ph ph-caret-right text-xs transition-transform {{ (int) $selectedYear === (int) $year ? 'rotate-90' : '' }}"></i>
                        </button>

                        @if ((int) $selectedYear === (int) $year)
                            <div class="ml-3 mt-1 flex flex-col gap-0.5">
                                @foreach ($months as $month)
                                    <button wire:click="selectMonth({{ $year }}, {{ $month }})" class="w-full text-left text-xs py-1 px-2 rounded-md {{ (int) $selectedMonth === (int) $month ? 'bg-accent-500/20 text-accent-300' : 'text-neutral-500 hover:text-ink' }}">
                                        {{ \Carbon\Carbon::create()->month((int) $month)->translatedFormat('F') }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-neutral-500">Sin registros todavía.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
```

- [ ] **Step 2: Add a structural regression test**

```php
    public function test_pre_operational_checklists_index_asset_filter_is_inside_the_livewire_component_root(): void
    {
        $response = $this->actingAs($this->admin())->get('/preoperacionales');
        $response->assertOk();

        $this->assertNestedInComponent($response->getContent(), 'wire:model.live', 'assetFilter', 'Listas preoperacionales asset filter');
    }
```

- [ ] **Step 3: Run tests**

Run: `php artisan test --filter=HeaderSlotStructureTest` (expect PASS, 11 tests) and `php artisan test --filter=PreOperationalChecklistTest` (expect PASS unchanged).

- [ ] **Step 4: Manual verification**

Open `/preoperacionales`. Confirm: page header, asset filter + export form + table + period sidebar all render in dark theme, period sidebar's selected year/month highlight matches the nav rail's own active-state color, "Nueva lista" navigates correctly.

- [ ] **Step 5: Commit**

```bash
git add resources/views/livewire/pre-operational-checklists/index.blade.php tests/Feature/HeaderSlotStructureTest.php
git commit -m "feat: redesign Listas preoperacionales (index) in the Nocturne style"
```

---

### Task 11: PreOperationalChecklists\Create

**Files:**
- Modify: `resources/views/livewire/pre-operational-checklists/create.blade.php`

**Interfaces:**
- No `wire:`-bound control needs to move (this is a single form, no header-slot-vs-body split issue — the whole page is the form). Deliberate color choice: "Buena" stays neutral (expected/default answer), "Mala" uses `text-accent-300` (the one hue Nocturne has for "needs attention"), matching the same choice already used for the busy-technician warning elsewhere in the app.

- [ ] **Step 1: Replace the view**

```blade
<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-shield-check text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Nueva lista preoperacional</h1>
    </div>
</x-slot>

<div class="max-w-4xl space-y-4">
    <a href="{{ route('pre-operational-checklists.index') }}" wire:navigate class="text-sm text-accent-300">&larr; Volver a listas preoperacionales</a>

    <form wire:submit="save" class="flex flex-col gap-4">
        <div class="card elev-sm p-6">
            <p class="text-sm text-neutral-400">Verificar antes de iniciar la operación que la máquina se encuentra en condiciones seguras y adecuadas de funcionamiento.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="field">
                    <label>Máquina / activo</label>
                    <select wire:model="asset_id" class="input">
                        <option value="">Selecciona un activo</option>
                        @foreach ($assets as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->code }} — {{ $asset->name }} ({{ $asset->area->name }})</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('asset_id')" class="mt-1" />
                </div>

                <div class="field">
                    <label>Fecha y hora</label>
                    <input wire:model="inspected_at" type="datetime-local" class="input">
                    <x-input-error :messages="$errors->get('inspected_at')" class="mt-1" />
                </div>
            </div>
        </div>

        @foreach ($itemsBySection as $section => $items)
            <div class="card elev-sm p-6">
                <h2 class="text-sm uppercase text-ink m-0">{{ $section }}</h2>

                <div class="mt-1 divide-y divide-neutral-800">
                    @foreach ($items as $item)
                        <div class="flex items-center justify-between gap-4 py-2.5" wire:key="item-{{ $item->id }}">
                            <p class="text-sm text-neutral-300 flex-1 m-0">{{ $item->label }}</p>
                            <div class="flex gap-3 shrink-0">
                                <label class="flex items-center gap-1 text-xs text-neutral-300">
                                    <input type="radio" wire:model="answers.{{ $item->id }}" value="buena"> Buena
                                </label>
                                <label class="flex items-center gap-1 text-xs text-accent-300">
                                    <input type="radio" wire:model="answers.{{ $item->id }}" value="mala"> Mala
                                </label>
                                <label class="flex items-center gap-1 text-xs text-neutral-500">
                                    <input type="radio" wire:model="answers.{{ $item->id }}" value="na"> N/A
                                </label>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('answers.'.$item->id)" class="mb-1" />
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="card elev-sm p-6 gap-4">
            <h2 class="card-title m-0">Resultado final</h2>

            <div class="field">
                <label>Resultado de la inspección</label>
                <select wire:model="result" class="input">
                    <option value="">Selecciona un resultado</option>
                    @foreach ($results as $r)
                        <option value="{{ $r->value }}">{{ $r->label() }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('result')" class="mt-1" />
                <p class="mt-1 text-xs text-accent-300">Si alguna condición crítica de seguridad quedó en MALA, la máquina debe considerarse NO APTA PARA OPERAR hasta que sea evaluada y corregida.</p>
            </div>

            <div class="field">
                <label>Observaciones — descripción de anomalías encontradas</label>
                <textarea wire:model="anomaly_notes" rows="3" class="input"></textarea>
                <x-input-error :messages="$errors->get('anomaly_notes')" class="mt-1" />
            </div>

            <div class="field">
                <label>Acción requerida</label>
                <select wire:model="required_action" class="input">
                    @foreach ($requiredActions as $action)
                        <option value="{{ $action->value }}">{{ $action->label() }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('required_action')" class="mt-1" />
            </div>

            <div class="field">
                <label>Observaciones adicionales</label>
                <textarea wire:model="additional_notes" rows="2" class="input"></textarea>
                <x-input-error :messages="$errors->get('additional_notes')" class="mt-1" />
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('pre-operational-checklists.index') }}" wire:navigate class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar lista preoperacional</button>
        </div>
    </form>
</div>
```

- [ ] **Step 2: Run existing tests**

Run: `php artisan test --filter=PreOperationalChecklistTest`
Expected: PASS unchanged.

- [ ] **Step 3: Manual verification**

Open `/preoperacionales/nueva`. Confirm the form renders in dark theme, all radio groups work, submitting creates a checklist and redirects to the index.

- [ ] **Step 4: Commit**

```bash
git add resources/views/livewire/pre-operational-checklists/create.blade.php
git commit -m "feat: redesign Nueva lista preoperacional in the Nocturne style"
```

---

### Task 12: PreOperationalChecklists\Show

**Files:**
- Modify: `resources/views/livewire/pre-operational-checklists/show.blade.php`

**Interfaces:**
- Consumes: `PreOperationalResult::tagVariant()` (Task 0). `PreOperationalAnswer` (cases `Buena`/`Mala`/`Na`) gets a local inline `@php` match — it appears in only this one file, so no enum method per Task 0's scope decision.

- [ ] **Step 1: Replace the view**

```blade
<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-shield-check text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">{{ $preOperationalChecklist->asset->name }}</h1>
    </div>
</x-slot>

<div class="max-w-4xl space-y-4">
    <a href="{{ route('pre-operational-checklists.index') }}" wire:navigate class="text-sm text-accent-300">&larr; Volver a listas preoperacionales</a>

    @php
        $answerTagClass = fn ($answer) => match ($answer) {
            \App\Enums\PreOperationalAnswer::Buena => 'tag-neutral',
            \App\Enums\PreOperationalAnswer::Mala => 'tag-accent',
            \App\Enums\PreOperationalAnswer::Na => 'tag-outline',
        };
    @endphp

    <div class="card elev-sm p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <p class="text-xs font-mono text-neutral-500 m-0">{{ $preOperationalChecklist->asset->code }} · {{ $preOperationalChecklist->asset->area->plant->name }} — {{ $preOperationalChecklist->asset->area->name }}</p>
            <span class="tag tag-{{ $preOperationalChecklist->result->tagVariant() }}">{{ $preOperationalChecklist->result->label() }}</span>
        </div>

        <dl class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <dt class="text-neutral-500">Fecha y hora</dt>
                <dd class="text-ink">{{ $preOperationalChecklist->inspected_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-neutral-500">Responsable</dt>
                <dd class="text-ink">{{ $preOperationalChecklist->performedBy->name }}</dd>
            </div>
            <div>
                <dt class="text-neutral-500">Acción requerida</dt>
                <dd class="text-ink">{{ $preOperationalChecklist->required_action->label() }}</dd>
            </div>
        </dl>

        @if ($preOperationalChecklist->anomaly_notes)
            <div class="mt-4 border-t border-neutral-800 pt-4">
                <dt class="text-neutral-500 text-sm">Observaciones — anomalías encontradas</dt>
                <dd class="mt-1 text-sm text-ink">{{ $preOperationalChecklist->anomaly_notes }}</dd>
            </div>
        @endif

        @if ($preOperationalChecklist->additional_notes)
            <div class="mt-3">
                <dt class="text-neutral-500 text-sm">Observaciones adicionales</dt>
                <dd class="mt-1 text-sm text-ink">{{ $preOperationalChecklist->additional_notes }}</dd>
            </div>
        @endif
    </div>

    @foreach ($answersBySection as $section => $answers)
        <div class="card elev-sm p-6">
            <h2 class="text-sm uppercase text-ink m-0">{{ $section }}</h2>

            <div class="mt-1 divide-y divide-neutral-800">
                @foreach ($answers as $answer)
                    <div class="flex items-center justify-between gap-4 py-2.5">
                        <p class="text-sm text-neutral-300 flex-1 m-0">{{ $answer->item->label }}</p>
                        <span class="tag {{ $answerTagClass($answer->answer) }}">{{ $answer->answer->label() }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
```

- [ ] **Step 2: Run existing tests**

Run: `php artisan test --filter=PreOperationalChecklistTest`
Expected: PASS unchanged.

- [ ] **Step 3: Manual verification**

Open a real checklist's detail page. Confirm result tag, per-section answer tags (Buena/Mala/N-A), and notes all render in dark theme.

- [ ] **Step 4: Commit**

```bash
git add resources/views/livewire/pre-operational-checklists/show.blade.php
git commit -m "feat: redesign Lista preoperacional (detalle) in the Nocturne style"
```

---

### Task 13: WorkOrders\QuickReport

**Files:**
- Modify: `resources/views/livewire/work-orders/quick-report.blade.php`

- [ ] **Step 1: Replace the view**

```blade
<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-warning-circle text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Reportar falla</h1>
    </div>
</x-slot>

<div class="max-w-md">
    <div class="card elev-sm p-6">
        <p class="text-xs font-mono text-neutral-500 m-0">{{ $asset->code }}</p>
        <h2 class="text-lg text-ink m-0">{{ $asset->name }}</h2>
        <p class="text-sm text-neutral-400 m-0">{{ $asset->area->plant->name }} — {{ $asset->area->name }}</p>

        @if ($submitted)
            <div class="mt-6 rounded-md border border-accent-600 p-4 text-sm text-ink">
                Falla reportada correctamente. El equipo de mantenimiento ha sido notificado.
            </div>

            <button wire:click="$set('submitted', false)" class="btn-ghost text-sm mt-4">
                Reportar otra falla en este equipo
            </button>
        @else
            <form wire:submit="report" class="mt-6 flex flex-col gap-4">
                <div class="field">
                    <label>Tipo</label>
                    <select wire:model="type" class="input">
                        @foreach ($types as $t)
                            <option value="{{ $t->value }}">{{ $t->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label>Prioridad</label>
                    <select wire:model="priority" class="input">
                        @foreach ($priorities as $p)
                            <option value="{{ $p->value }}">{{ $p->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label>Ejecución</label>
                    <select wire:model.live="execution_type" class="input">
                        @foreach ($executionTypes as $e)
                            <option value="{{ $e->value }}">{{ $e->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('execution_type')" class="mt-1" />
                </div>

                @if ($execution_type === 'externo')
                    <div class="field">
                        <label>Proveedor</label>
                        <select wire:model="provider_id" class="input">
                            <option value="">Selecciona un proveedor</option>
                            @foreach ($providers as $provider)
                                <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('provider_id')" class="mt-1" />
                    </div>
                @endif

                <div class="field">
                    <label>¿Qué está pasando?</label>
                    <textarea wire:model="failure_description" rows="4" autofocus class="input" placeholder="Describe la falla observada..."></textarea>
                    <x-input-error :messages="$errors->get('failure_description')" class="mt-1" />
                </div>

                <button type="submit" class="btn btn-primary w-full justify-center">Crear reporte</button>
            </form>
        @endif
    </div>
</div>
```

- [ ] **Step 2: Run existing tests**

Run: `php artisan test --filter=WorkOrderReportTest`
Expected: PASS except the one pre-existing, unrelated dompdf failure documented in the Global Constraints of the prior redesign plan (`docs/superpowers/plans/2026-09-03-nocturne-dashboard-redesign.md`) — if `WorkOrderReportTest` is entirely about this QuickReport flow (not the PDF one), it should be fully green; if it also covers `downloadReport()`, only that one sub-test is expected to fail, for the same pre-existing environment reason (dompdf/SSL), unrelated to this change.

- [ ] **Step 3: Manual verification**

Scan/open a real `/reportar/{code}` link as a logged-in user. Confirm the form renders in dark theme, submitting creates a work order and shows the success state, "Reportar otra falla" resets the form.

- [ ] **Step 4: Commit**

```bash
git add resources/views/livewire/work-orders/quick-report.blade.php
git commit -m "feat: redesign Reportar falla (QR) in the Nocturne style"
```

---

### Task 14: WorkOrders\Show

**Files:**
- Modify: `resources/views/livewire/work-orders/show.blade.php`

**Interfaces:**
- Consumes: `WorkOrderStatus::tagVariant()`, `WorkOrderPriority::tagVariant()` (Task 0). `WorkOrderType` stays literal `tag-neutral`; `WorkOrderExecutionType` gets a local inline ternary (`Externo` → `tag-outline`, else `tag-neutral`) since it appears only in this file. This is the highest-risk task in the plan — real business logic (invoice, PDF export, technician assignment with busy-technician warnings, checklist, spare-parts usage, photo gallery). Every `wire:submit`/`wire:click`/`@can`/`@if` in the file is preserved exactly; only classes and component swaps change.

- [ ] **Step 1: Replace the view**

```blade
<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-clipboard-text text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Orden {{ $workOrder->order_number }}</h1>
    </div>
</x-slot>

<div class="space-y-4">
    @php
        $priorityTagClass = fn ($priority) => match ($priority) {
            \App\Enums\WorkOrderPriority::Urgente, \App\Enums\WorkOrderPriority::Alta => 'tag-accent',
            \App\Enums\WorkOrderPriority::Media => 'tag-outline',
            \App\Enums\WorkOrderPriority::Baja => 'tag-neutral',
        };
        $executionTagClass = fn ($type) => $type === \App\Enums\WorkOrderExecutionType::Externo ? 'tag-outline' : 'tag-neutral';
    @endphp

    <a href="{{ route('work-orders.index') }}" wire:navigate class="text-sm text-accent-300">&larr; Volver a órdenes</a>

    <div class="card elev-sm p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm font-mono text-accent-300 m-0">{{ $workOrder->order_number }}</p>
                <p class="text-xs font-mono text-neutral-500 m-0">{{ $workOrder->asset->code }} · {{ $workOrder->asset->area->plant->name }} — {{ $workOrder->asset->area->name }}</p>
                <h2 class="text-xl text-ink m-0">{{ $workOrder->asset->name }}</h2>
                <p class="mt-1 text-sm text-neutral-300">{{ $workOrder->failure_description ?? 'Mantenimiento preventivo programado' }}</p>
                <p class="mt-2 text-xs text-neutral-500">Reportada por <span class="text-neutral-300">{{ $workOrder->reportedBy->name }}</span></p>
            </div>

            <div class="flex flex-col items-end gap-3">
                <div class="flex flex-wrap justify-end gap-2">
                    <span class="tag tag-{{ $workOrder->status->tagVariant() }}">{{ $workOrder->status->label() }}</span>
                    <span class="tag {{ $priorityTagClass($workOrder->priority) }}">{{ $workOrder->priority->label() }}</span>
                    <span class="tag tag-neutral">{{ $workOrder->type->label() }}</span>
                    <span class="tag {{ $executionTagClass($workOrder->execution_type) }}">{{ $workOrder->execution_type->label() }}</span>
                </div>

                <div class="flex gap-2">
                    @can('update', $workOrder)
                        <button wire:click="openEditModal" class="btn btn-secondary">Editar</button>
                    @endcan
                    <button wire:click="downloadReport" class="btn btn-secondary">Descargar informe</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card elev-sm p-6">
        <h2 class="card-title m-0">Asignación</h2>

        @if ($workOrder->provider)
            <p class="mt-3 text-sm text-neutral-300">
                Proveedor: <a href="{{ route('providers.show', $workOrder->provider) }}" wire:navigate class="font-medium text-accent-300">{{ $workOrder->provider->name }}</a>
            </p>
        @endif

        @if ($workOrder->supportCollaborator)
            <p class="mt-1 text-sm text-neutral-300">
                Colaborador de apoyo: <span class="font-medium text-ink">{{ $workOrder->supportCollaborator->name }}</span>
            </p>
        @endif

        @if ($workOrder->assignedTo && $workOrder->execution_type === \App\Enums\WorkOrderExecutionType::Interno)
            <p class="mt-1 text-sm text-neutral-300">
                Colaborador asignado: <span class="font-medium text-ink">{{ $workOrder->assignedTo->name }}</span>
            </p>
        @endif

        @can('update', $workOrder)
            <div class="mt-4 border-t border-neutral-800 pt-4">
                <form wire:submit="assign" class="flex flex-wrap items-end gap-3">
                    @if ($workOrder->execution_type === \App\Enums\WorkOrderExecutionType::Externo)
                        <div class="field flex-1 min-w-[200px]">
                            <label>Proveedor</label>
                            <select wire:model="provider_id" class="input">
                                <option value="">Sin asignar</option>
                                @foreach ($providers as $prov)
                                    <option value="{{ $prov->id }}">{{ $prov->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('provider_id')" class="mt-1" />
                        </div>
                        <div class="field flex-1 min-w-[200px]">
                            <label>Colaborador asignado de apoyo</label>
                            <select wire:model.live="support_collaborator_id" class="input">
                                <option value="">Sin asignar</option>
                                @foreach ($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('support_collaborator_id')" class="mt-1" />
                            @php $selectedSupport = $technicians->firstWhere('id', (int) $support_collaborator_id); @endphp
                            @if ($selectedSupport && ($selectedSupport->active_assigned_count + $selectedSupport->active_support_count) > 0)
                                <p class="mt-1 text-xs text-accent-300">⚠ {{ $selectedSupport->name }} ya tiene una orden en curso.</p>
                            @endif
                        </div>
                    @else
                        <div class="field flex-1 min-w-[200px]">
                            <label>Colaborador asignado</label>
                            <select wire:model.live="assigned_to" class="input">
                                <option value="">Sin asignar</option>
                                @foreach ($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('assigned_to')" class="mt-1" />
                            @php $selectedTech = $technicians->firstWhere('id', (int) $assigned_to); @endphp
                            @if ($selectedTech && ($selectedTech->active_assigned_count + $selectedTech->active_support_count) > 0)
                                <p class="mt-1 text-xs text-accent-300">⚠ {{ $selectedTech->name }} ya tiene una orden en curso.</p>
                            @endif
                        </div>
                    @endif
                    <button type="submit" class="btn btn-secondary">Asignar</button>
                </form>
            </div>
        @endcan
    </div>

    <div class="card elev-sm p-6">
        <h2 class="card-title m-0">Tiempos</h2>

        <dl class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <dt class="text-neutral-500">Abierta</dt>
                <dd class="text-ink">{{ $workOrder->opened_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-neutral-500">Iniciada</dt>
                <dd class="text-ink">{{ $workOrder->started_at?->format('d/m/Y H:i') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-neutral-500">Completada</dt>
                <dd class="text-ink">{{ $workOrder->completed_at?->format('d/m/Y H:i') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-neutral-500">Duración total</dt>
                <dd class="text-ink">{{ $workOrder->status->isOpen() ? 'En curso' : \App\Models\WorkOrder::formatDurationMinutes($workOrder->total_minutes) }}</dd>
            </div>
        </dl>

        <dl class="mt-4 grid grid-cols-2 gap-4 text-sm border-t border-neutral-800 pt-4">
            <div>
                <dt class="text-neutral-500">Tiempo de espera</dt>
                <dd class="text-ink">{{ \App\Models\WorkOrder::formatDurationMinutes($workOrder->wait_minutes) }}</dd>
            </div>
            <div>
                <dt class="text-neutral-500">Tiempo de ejecución</dt>
                <dd class="text-ink">{{ \App\Models\WorkOrder::formatDurationMinutes($workOrder->repair_minutes) }}</dd>
            </div>
        </dl>
    </div>

    @if ($workOrder->execution_type === \App\Enums\WorkOrderExecutionType::Externo)
        <div class="card elev-sm p-6">
            <h2 class="card-title m-0">Factura / requerimiento de compra</h2>

            @can('update', $workOrder)
                <form wire:submit="saveInvoiceInfo" class="mt-4 flex flex-wrap items-end gap-3">
                    <div class="field flex-1 min-w-[200px]">
                        <label>N.° factura / requerimiento de compra</label>
                        <input wire:model="invoice_number" class="input">
                        <x-input-error :messages="$errors->get('invoice_number')" class="mt-1" />
                    </div>
                    <div class="field w-40">
                        <label>Monto pagado</label>
                        <input wire:model="amount_paid" type="number" step="0.01" min="0" class="input">
                        <x-input-error :messages="$errors->get('amount_paid')" class="mt-1" />
                    </div>
                    <button type="submit" class="btn btn-secondary">Guardar</button>
                </form>
            @else
                <dl class="mt-4 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-neutral-500">N.° factura / requerimiento de compra</dt>
                        <dd class="text-ink">{{ $workOrder->invoice_number ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-500">Monto pagado</dt>
                        <dd class="text-ink">{{ $workOrder->amount_paid !== null ? '$'.number_format((float) $workOrder->amount_paid, 2) : '—' }}</dd>
                    </div>
                </dl>
            @endcan
        </div>
    @endif

    @if ($workOrder->maintenancePlan?->checklistTemplate)
        <div class="card elev-sm p-6">
            <h2 class="card-title m-0">Checklist — {{ $workOrder->maintenancePlan->checklistTemplate->name }}</h2>

            <form wire:submit="saveChecklist" class="mt-4 flex flex-col gap-4">
                @foreach ($workOrder->maintenancePlan->checklistTemplate->items as $item)
                    <div class="flex items-start gap-4 border-b border-neutral-800 pb-3" wire:key="item-{{ $item->id }}">
                        <div class="flex-1">
                            <p class="text-sm text-neutral-200 m-0">{{ $item->label }}</p>
                            <input type="text" wire:model="checklist.{{ $item->id }}.notes" placeholder="Notas (opcional)" class="input mt-1 text-xs">
                        </div>
                        <div class="flex gap-2 shrink-0 pt-1">
                            <label class="flex items-center gap-1 text-xs text-neutral-300">
                                <input type="radio" wire:model="checklist.{{ $item->id }}.passed" value="1"> OK
                            </label>
                            <label class="flex items-center gap-1 text-xs text-accent-300">
                                <input type="radio" wire:model="checklist.{{ $item->id }}.passed" value="0"> Falla
                            </label>
                        </div>
                    </div>
                @endforeach

                <button type="submit" class="btn btn-primary">Guardar checklist</button>
            </form>
        </div>
    @endif

    @can('update', $workOrder)
        <div class="card elev-sm p-6">
            <h2 class="card-title m-0">Descripción de las reparaciones o mantenimientos realizados</h2>

            <form wire:submit="saveResolution" class="mt-3 flex flex-col gap-3">
                <textarea wire:model="resolution_notes" rows="3" placeholder="Describe las reparaciones o mantenimientos realizados..." class="input"></textarea>

                <div class="flex gap-3">
                    <button type="submit" class="btn btn-secondary">Guardar notas</button>
                    @if ($workOrder->status->isOpen())
                        <button type="button" wire:click="complete" wire:confirm="¿Marcar esta orden como completada?" class="btn btn-primary">Completar orden</button>
                    @endif
                </div>
            </form>
        </div>
    @endcan

    @can('registerUsage', \App\Models\SparePart::class)
        <div class="card elev-sm p-6">
            <h2 class="card-title m-0">Relación de insumos o partes que se cambian</h2>

            <div class="mt-3 flex flex-col gap-2">
                @forelse ($workOrder->sparePartUsages as $usage)
                    <div class="flex items-center justify-between text-sm border-b border-neutral-800 pb-2" wire:key="usage-{{ $usage->id }}">
                        <span class="text-neutral-200">{{ $usage->sparePart->name }} <span class="text-neutral-500 font-mono text-xs">{{ $usage->sparePart->code }}</span></span>
                        <span class="text-neutral-300">x{{ $usage->quantity }}</span>
                    </div>
                @empty
                    <p class="text-sm text-neutral-500">No se han registrado repuestos.</p>
                @endforelse
            </div>

            <form wire:submit="addSparePartUsage" class="mt-4 flex flex-wrap items-end gap-3">
                <div class="field flex-1 min-w-[200px]">
                    <label>Repuesto</label>
                    <select wire:model="spare_part_id" class="input">
                        <option value="">Selecciona un repuesto</option>
                        @foreach ($spareParts as $part)
                            <option value="{{ $part->id }}">{{ $part->name }} ({{ $part->stock_quantity }} disponibles)</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('spare_part_id')" class="mt-1" />
                </div>
                <div class="field w-24">
                    <label>Cantidad</label>
                    <input wire:model="spare_part_quantity" type="number" min="1" class="input">
                    <x-input-error :messages="$errors->get('spare_part_quantity')" class="mt-1" />
                </div>
                <button type="submit" class="btn btn-secondary">Registrar</button>
            </form>
        </div>
    @endcan

    <div class="card elev-sm p-6">
        <h2 class="card-title m-0">Evidencia fotográfica</h2>

        <div class="mt-3 flex flex-wrap gap-3">
            @foreach ($workOrder->attachments as $attachment)
                <a href="{{ Storage::disk('public')->url($attachment->path) }}" target="_blank">
                    <img src="{{ Storage::disk('public')->url($attachment->path) }}" class="h-20 w-20 rounded-md object-cover ring-1 ring-neutral-800">
                </a>
            @endforeach
        </div>

        @can('update', $workOrder)
            <form wire:submit="uploadPhoto" class="mt-4 flex items-center gap-3">
                <input type="file" wire:model="newPhoto" class="text-sm text-neutral-400">
                <button type="submit" class="btn btn-secondary">Subir foto</button>
            </form>
            <x-input-error :messages="$errors->get('newPhoto')" class="mt-1" />
        @endcan
    </div>

    @if ($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto dialog-backdrop grid place-items-center p-4" wire:transition>
            <div class="fixed inset-0" wire:click="closeEditModal"></div>

            <div class="dialog relative">
                <h2 class="dialog-title">Editar reporte {{ $workOrder->order_number }}</h2>

                <form wire:submit="saveEdit" class="flex flex-col gap-4">
                    <div class="field">
                        <label>Tipo</label>
                        <select wire:model="edit_type" class="input">
                            @foreach ($types as $t)
                                <option value="{{ $t->value }}">{{ $t->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('edit_type')" class="mt-1" />
                    </div>

                    <div class="field">
                        <label>Prioridad</label>
                        <select wire:model="edit_priority" class="input">
                            @foreach ($priorities as $p)
                                <option value="{{ $p->value }}">{{ $p->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('edit_priority')" class="mt-1" />
                    </div>

                    <div class="field">
                        <label>Tipo de ejecución</label>
                        <select wire:model="edit_execution_type" class="input">
                            @foreach ($executionTypes as $e)
                                <option value="{{ $e->value }}">{{ $e->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('edit_execution_type')" class="mt-1" />
                    </div>

                    <div class="field">
                        <label>Descripción de la falla</label>
                        <textarea wire:model="edit_failure_description" rows="3" class="input"></textarea>
                        <x-input-error :messages="$errors->get('edit_failure_description')" class="mt-1" />
                    </div>

                    <div class="dialog-actions">
                        <button type="button" wire:click="closeEditModal" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
```

- [ ] **Step 2: Run existing tests**

Run: `php artisan test --filter=WorkOrderAssignmentTest` (expect PASS unchanged — this is the direct copy-preservation check for the busy-technician warning text and assignment flows) and `php artisan test --filter=WorkOrdersBoardTest` (expect PASS unchanged, defensive check).

- [ ] **Step 3: Manual verification**

Open a real work order detail page as Admin, for both an `interno` and an `externo` order, and for one with a linked `maintenancePlan.checklistTemplate` and one without. Confirm every card renders in dark theme; Editar, Descargar informe, Asignar (with the busy-technician warning showing correctly), Guardar checklist, Guardar notas, Completar orden, Guardar (factura), Registrar (repuesto), and Subir foto all still work exactly as before.

- [ ] **Step 4: Commit**

```bash
git add resources/views/livewire/work-orders/show.blade.php
git commit -m "feat: redesign Detalle de orden de trabajo in the Nocturne style"
```

---

### Task 15: Full regression pass and sign-off

**Files:** none (verification only).

- [ ] **Step 1: Run the full automated test suite**

Run: `php artisan test`
Expected: every test passes except the one pre-existing, unrelated, out-of-scope failure documented in the prior redesign plan (`WorkOrderReportTest::test_downloading_the_report_returns_a_pdf`, a local dompdf/SSL environment issue). Investigate and fix any OTHER failure before proceeding.

- [ ] **Step 2: Run Pint across the whole diff**

Run: `vendor/bin/pint --dirty --format agent`
Expected: clean (Task 0 already ran this).

- [ ] **Step 3: Production build check**

Run: `npm run build`
Expected: builds cleanly.

- [ ] **Step 4: Full manual walkthrough**

Log in as Admin and click through all 14 screens end to end (plus the 4 originally-redesigned screens, to confirm nothing regressed there): confirm no white table or card is visible on the dark shell anywhere in the app, every header title/icon renders (including the 4 dynamic ones: `PreOperationalChecklists\Show`, `Providers\Show`, `Team\Show`, `WorkOrders\Show`), every filter/search/checkbox/create button actually filters or opens its modal (not just renders), every modal's backdrop-click-to-close works and clicking inside the dialog does not close it, and every table scrolls horizontally on a narrow viewport instead of overflowing the page.

- [ ] **Step 5: Optional cleanup — retrofit the 2 original screens to use `tagVariant()`**

Only if Steps 1-4 are clean. In `resources/views/livewire/work-orders/index.blade.php` and `resources/views/livewire/assets/show.blade.php`, replace the local `$priorityTagClass`/`$statusTagClass` closures' bodies with calls to the new `->tagVariant()` methods where the enum has one (`WorkOrderStatus`, `WorkOrderPriority`, `AssetStatus`), keeping the closures themselves (they're still needed to prefix `tag-`) or switching to `<x-tag :variant="...">`. Run `php artisan test --filter=WorkOrdersBoardTest`, `--filter=AssetShowTest`, and `--filter=PreOperationalChecklistTest` (the last one touches `assets/show.blade.php` too) after this change. This step is a nice-to-have, not a merge blocker — skip it if short on time.

- [ ] **Step 6: Final commit (if any cleanup was needed)**

```bash
git add -A
git commit -m "chore: retrofit original screens to use tagVariant() (optional cleanup)"
```

If Step 5 was skipped, there is nothing to commit here — the plan is complete as of Task 14's commit.
