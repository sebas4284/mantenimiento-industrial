# Nocturne Dashboard Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Adopt the Nocturne dark design system across the app's global navigation shell, and fully redesign the Dashboard, Órdenes de trabajo, Proveedores, and Detalle de activo screens to its component language, wired to real data.

**Architecture:** Extend Tailwind's theme with Nocturne's color/radius/shadow tokens and swap to Inter; port Nocturne's plain-CSS component classes (`.card`, `.tag`, `.table`, `.btn`, `.field`, `.dialog`) into `app.css`; rebuild the global shell (left icon rail + top bar) that every page already inherits via `layouts/app.blade.php`; then redesign the four target screens' Blade views on top of that shell, adding the small amount of new backend logic (deltas, a backlog ring, a "top equipos" ranking, an "atención" pick, a provider's active-order count, an asset's MTBF/MTTR/next-preventive-date) needed to fill the new UI with real data.

**Tech Stack:** Laravel 11 (PHP 8.3), Livewire 3 + Volt, Tailwind CSS 3 (PostCSS pipeline, not the v4 Vite plugin), ApexCharts 7, Alpine.js, PHPUnit 12.

**Spec:** `docs/superpowers/specs/2026-09-02-nocturne-dashboard-redesign-design.md`

## Global Constraints

- Nocturne is the only theme — no light/dark toggle. Remove `dark:` variants on every file touched in this plan; do not add new ones.
- The 4 target screens get full content redesigns; every other page keeps its current light inner content and only inherits the new shell (rail + top bar). This is intentional, not a bug.
- No drag-and-drop on the kanban; status changes stay button-driven, unchanged.
- No new charting dependency: keep ApexCharts (already installed) for the Pareto bar and trend line charts; hand-build the backlog ring with CSS `conic-gradient`.
- Icons: `@phosphor-icons/web`, imported via its local CSS file path so Vite bundles the font — no CDN `<link>`/`<script>` at runtime.
- No SQL that isn't SQLite-portable — the test suite runs on `sqlite`/`:memory:` (see `phpunit.xml`). Do not use `orderByRaw` with MySQL-only functions (e.g. `FIELD()`).
- Run `vendor/bin/pint --dirty --format agent` after PHP changes in every task that touches a `.php` file.

**Reconciliation note (added after a concurrent merge landed mid-planning):** commit `f293282` ("Agregar edicion/factura/PDF de ordenes, tablero reorganizado y listas preoperacionales") merged into `main` while this plan was being written, changing files Tasks 1, 4, 7 and 8 depend on. Tasks 1, 4, 7 and 8 below already reflect the post-merge reality (3-status kanban + separate history table in Órdenes, a "Listas preoperacionales" nav item and asset-detail section, `barryvdh/laravel-dompdf` as a new composer dependency). Tasks 2, 3, 5, 6 are untouched by that merge and are unaffected. One pre-existing, unrelated failure exists in the baseline: `tests/Feature/WorkOrderReportTest::test_downloading_the_report_returns_a_pdf` fails with `Class "Barryvdh\DomPDF\Facade\Pdf" not found` because `composer install` cannot complete in this environment (local SSL/certificate interception, unrelated to this plan). Do not attempt to fix that dependency as part of any task in this plan — it is out of scope; each implementer should confirm their own task's tests pass and ignore that specific pre-existing failure when running the broader suite.

---

### Task 1: Global Nocturne shell (tokens, Inter, Phosphor icons, rail nav, top bar)

**Files:**
- Create: `resources/css/nocturne-tokens.css`
- Modify: `resources/css/app.css`
- Modify: `tailwind.config.js`
- Modify: `package.json`
- Modify: `resources/js/app.js`
- Modify: `resources/views/layouts/app.blade.php`
- Modify: `resources/views/livewire/layout/navigation.blade.php`

**Interfaces:**
- Produces: Tailwind utilities `bg-bg`, `text-ink`, `bg-surface`, `text-accent`/`accent-{100..900}`, `text-neutral`/`neutral-{100..900}`, `bg-section`/`section-glow`/`section-ghost`, `rounded-sm|md|lg`, `shadow-sm|md|lg`, all consumed by every later task. Plain CSS classes `.card`, `.elev-sm|md|lg`, `.card-title`, `.card-body`, `.tag`, `.tag-accent`, `.tag-accent-2`, `.tag-neutral`, `.tag-outline`, `.table`, `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-ghost`, `.field`, `.input`, `.dialog`, `.dialog-title`, `.dialog-actions`, `.text-muted`, consumed by every later task. A named Blade slot `header` on `layouts/app.blade.php`'s top bar (a two-child flex-justify-between container — first child is the page icon+title, an optional second child holds contextual controls), consumed by Tasks 3, 4, 6, 8.
- Consumes: nothing (foundational task).

- [ ] **Step 1: Add the Nocturne token file**

Create `resources/css/nocturne-tokens.css`:

```css
/* Nocturne design tokens — plain CSS custom properties consumed by the
   component classes in app.css. */
:root {
  --color-bg: #161826;
  --color-surface: #232532;
  --color-text: #e9e9ed;
  --color-accent: #9184d9;
  --color-accent-2: #a7a1db;
  --color-divider: color-mix(in srgb, #e9e9ed 16%, transparent);

  --color-neutral-100: #f3f5fe;
  --color-neutral-200: #e4e7f5;
  --color-neutral-300: #cfd3e5;
  --color-neutral-400: #b2b6ca;
  --color-neutral-500: #9397ab;
  --color-neutral-600: #75798c;
  --color-neutral-700: #595d6c;
  --color-neutral-800: #3f424d;
  --color-neutral-900: #292b31;

  --color-accent-100: #f5f4ff;
  --color-accent-200: #e7e5fe;
  --color-accent-300: #d2cefd;
  --color-accent-400: #b5abfc;
  --color-accent-500: #968ae0;
  --color-accent-600: #796cbf;
  --color-accent-700: #5d5294;
  --color-accent-800: #423a6a;
  --color-accent-900: #2b2741;

  --color-section: #262a60;
  --color-section-glow: #353b80;
  --color-section-ghost: #4c5397;

  --font-heading: "Inter", system-ui, sans-serif;
  --font-heading-weight: 500;
  --font-body: "Inter", system-ui, sans-serif;

  --space-1: 2.8px;
  --space-2: 5.6px;
  --space-3: 8.4px;
  --space-4: 11.2px;
  --space-6: 16.8px;
  --space-8: 22.4px;

  --radius-sm: 4px;
  --radius-md: 8px;
  --radius-lg: 14px;

  --shadow-sm: 0 0 0 1px #3f424d;
  --shadow-md: 0 0 0 1px #595d6c, 0 6px 18px rgba(0,0,0,0.55);
  --shadow-lg: 0 0 0 1px #9397ab, 0 16px 40px rgba(0,0,0,0.65);
}
```

- [ ] **Step 2: Rewrite `resources/css/app.css`** to import the tokens, the Phosphor webfont (self-hosted via the npm package's own file, not a CDN link — see Step 4), and the Nocturne component classes:

```css
@import './nocturne-tokens.css';
@import '@phosphor-icons/web/src/regular/style.css';

@tailwind base;
@tailwind components;
@tailwind utilities;

body { margin: 0; font-size: 15px; line-height: 1.55; font-weight: 400; }
h1, h2, h3, h4, h5, h6 {
  font-family: var(--font-heading); font-weight: var(--font-heading-weight);
  line-height: 1.12; letter-spacing: -0.015em; margin: 0 0 var(--space-2);
}
.text-muted { color: color-mix(in srgb, var(--color-text) 55%, transparent); }

.btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 6px;
  cursor: pointer; text-decoration: none;
  font-family: var(--font-heading); font-weight: var(--font-heading-weight);
  font-size: 14px; line-height: 1.2; color: var(--color-text);
  background: transparent; border: 1px solid transparent;
  padding: var(--space-2) calc(var(--space-3) * 1.2);
  border-radius: var(--radius-md);
}
.btn svg, .btn i { display: block; }
.btn:disabled { opacity: 0.45; cursor: not-allowed; }
.btn-primary { color: var(--color-accent); border-color: var(--color-accent); }
.btn-primary:hover { background: color-mix(in srgb, var(--color-accent) 12%, transparent); }
.btn-primary:active { background: color-mix(in srgb, var(--color-accent) 22%, transparent); }
.btn-secondary { border-color: var(--color-divider); }
.btn-secondary:hover { background: color-mix(in srgb, var(--color-text) 7%, transparent); }
.btn-secondary:active { background: color-mix(in srgb, var(--color-text) 14%, transparent); }
.btn-ghost { color: var(--color-accent); padding-inline: var(--space-1); }
.btn-ghost:hover { background: color-mix(in srgb, var(--color-accent) 10%, transparent); }
.btn-ghost:active { background: color-mix(in srgb, var(--color-accent) 18%, transparent); }

.field > label {
  display: block; font-size: 12px; margin-bottom: 5px;
  color: color-mix(in srgb, var(--color-text) 70%, transparent);
}
.input {
  width: 100%; min-height: 36px; padding: 6px 10px; font: inherit;
  font-size: 14px; color: var(--color-text); caret-color: var(--color-accent);
  background: var(--color-surface);
  border: 1px solid var(--color-divider); border-radius: var(--radius-md);
}
.input:hover { border-color: color-mix(in srgb, var(--color-text) 45%, transparent); }
.input:focus-visible { border-color: var(--color-accent); outline-offset: 0; }
textarea.input { min-height: 90px; resize: vertical; }

.card {
  display: flex; flex-direction: column; gap: var(--space-2);
  border-radius: var(--radius-md); background: var(--color-surface);
}
.card-kicker { font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--color-accent); }
.card-title {
  font-family: var(--font-heading); font-weight: var(--font-heading-weight);
  font-size: 17px; line-height: 1.2;
}
.card-body { margin: 0; font-size: 13px; opacity: 0.8; flex: 1; }
.elev-sm { box-shadow: var(--shadow-sm); }
.elev-md { box-shadow: var(--shadow-md); }
.elev-lg { box-shadow: var(--shadow-lg); }

.tag {
  display: inline-flex; align-items: center; font-size: 11px;
  letter-spacing: 0.02em; padding: 3px 10px;
  border-radius: calc(var(--radius-md) * 0.75);
}
.tag-accent { background: var(--color-accent-800); color: var(--color-accent-100); }
.tag-accent-2 { background: var(--color-accent-2, var(--color-accent-800)); color: var(--color-accent-100); }
.tag-neutral { background: var(--color-neutral-800); color: var(--color-neutral-100); }
.tag-outline { border: 1px solid var(--color-accent); color: var(--color-accent); }

.table { width: 100%; border-collapse: collapse; font-size: 14px; }
.table th {
  text-align: left; font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase;
  color: color-mix(in srgb, var(--color-text) 60%, transparent);
  padding: var(--space-2); border-bottom: 1px solid var(--color-divider);
}
.table td { padding: var(--space-2); border-bottom: 1px solid var(--color-divider); }
.table tbody tr:hover { background: color-mix(in srgb, var(--color-text) 4%, transparent); }

.dialog-backdrop { background: color-mix(in srgb, var(--color-neutral-900) 60%, transparent); }
.dialog {
  width: min(440px, 100%); display: flex; flex-direction: column; gap: var(--space-3);
  padding: var(--space-4); border-radius: var(--radius-lg);
  background: var(--color-surface); box-shadow: var(--shadow-lg);
}
.dialog-title {
  font-family: var(--font-heading); font-weight: var(--font-heading-weight);
  font-size: 20px; margin: 0;
}
.dialog-actions { display: flex; justify-content: flex-end; gap: var(--space-2); margin-top: var(--space-2); }
```

(This is a trimmed port of the handoff's `nocturne-styles.css` — only the classes this app's 4 screens actually use. The `.table` row/header fading-gradient effect from the reference is simplified to a plain 1px divider border, which is visually close and far simpler to maintain in a Tailwind-utility-first codebase.)

- [ ] **Step 3: Extend the Tailwind theme and swap the font**

Replace `tailwind.config.js` with:

```js
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                bg: '#161826',
                surface: '#232532',
                ink: '#e9e9ed',
                accent: {
                    DEFAULT: '#9184d9',
                    100: '#f5f4ff', 200: '#e7e5fe', 300: '#d2cefd', 400: '#b5abfc',
                    500: '#968ae0', 600: '#796cbf', 700: '#5d5294', 800: '#423a6a', 900: '#2b2741',
                },
                neutral: {
                    100: '#f3f5fe', 200: '#e4e7f5', 300: '#cfd3e5', 400: '#b2b6ca',
                    500: '#9397ab', 600: '#75798c', 700: '#595d6c', 800: '#3f424d', 900: '#292b31',
                },
                section: { DEFAULT: '#262a60', glow: '#353b80', ghost: '#4c5397' },
            },
            borderRadius: { sm: '4px', md: '8px', lg: '14px' },
            boxShadow: {
                sm: '0 0 0 1px #3f424d',
                md: '0 0 0 1px #595d6c, 0 6px 18px rgba(0,0,0,0.55)',
                lg: '0 0 0 1px #9397ab, 0 16px 40px rgba(0,0,0,0.65)',
            },
        },
    },

    plugins: [forms],
};
```

(There is no other use of Tailwind's `neutral-*` utility anywhere in `resources/views` today — confirmed via a repo-wide search — so overriding that scale is safe.)

- [ ] **Step 4: Add the Phosphor icons package**

```bash
npm install @phosphor-icons/web
```

This adds `"@phosphor-icons/web"` to `dependencies` in `package.json`. The `@import '@phosphor-icons/web/src/regular/style.css';` added to `app.css` in Step 2 resolves to the package's own file inside `node_modules` (this is the same file jsDelivr serves for the CDN usage documented by the project — installing it locally and importing its real path means Vite processes the `@font-face src: url(...)` references and bundles the referenced woff/woff2 files into the build, with no CDN request at runtime). Use `<i class="ph ph-<name>">` per Phosphor's regular-weight class convention in every task below.

- [ ] **Step 5: Rewrite the global layout as a rail + top-bar shell**

Replace `resources/views/layouts/app.blade.php`:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-bg text-ink">
        <div class="min-h-screen flex">
            <livewire:layout.navigation />

            <div class="flex-1 min-w-0 flex flex-col">
                @if (isset($header))
                    <header class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-neutral-800">
                        {{ $header }}
                    </header>
                @endif

                <main class="flex-1 overflow-y-auto p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
```

- [ ] **Step 6: Rewrite the navigation as the 72px left icon rail**

Replace `resources/views/livewire/layout/navigation.blade.php`:

```blade
<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

@php
$navItems = [
    ['route' => 'dashboard', 'pattern' => 'dashboard', 'icon' => 'ph-squares-four', 'label' => 'Dashboard'],
    ['route' => 'assets.index', 'pattern' => 'assets.*', 'icon' => 'ph-gear-six', 'label' => 'Activos'],
    ['route' => 'work-orders.index', 'pattern' => 'work-orders.*', 'icon' => 'ph-clipboard-text', 'label' => 'Órdenes'],
    ['route' => 'maintenance-plans.index', 'pattern' => 'maintenance-plans.index', 'icon' => 'ph-calendar-check', 'label' => 'Planes'],
    ['route' => 'checklist-templates.index', 'pattern' => 'checklist-templates.index', 'icon' => 'ph-list-checks', 'label' => 'Checklists'],
    ['route' => 'pre-operational-checklists.index', 'pattern' => 'pre-operational-checklists.*', 'icon' => 'ph-shield-check', 'label' => 'Listas preoperacionales'],
    ['route' => 'spare-parts.index', 'pattern' => 'spare-parts.index', 'icon' => 'ph-package', 'label' => 'Inventario'],
    ['route' => 'providers.index', 'pattern' => 'providers.*', 'icon' => 'ph-truck', 'label' => 'Proveedores'],
];
$navItemClass = fn (string $pattern) => request()->routeIs($pattern)
    ? 'bg-accent-500/20 text-accent-300'
    : 'text-neutral-500 hover:text-neutral-300';
@endphp

<nav class="w-[72px] shrink-0 bg-neutral-900 border-r border-neutral-800 flex flex-col items-center py-4 gap-6">
    <a href="{{ route('dashboard') }}" wire:navigate
        class="w-9 h-9 rounded-md bg-accent-800/40 border border-accent-700 flex items-center justify-center text-accent-300 font-medium">
        M
    </a>

    <div class="flex flex-col gap-2 items-center overflow-y-auto">
        @foreach ($navItems as $item)
            <a href="{{ route($item['route']) }}" wire:navigate title="{{ $item['label'] }}"
                class="w-10 h-10 rounded-md flex items-center justify-center {{ $navItemClass($item['pattern']) }}">
                <i class="ph {{ $item['icon'] }} text-[19px]"></i>
            </a>
        @endforeach

        @if (in_array(auth()->user()->role, [\App\Enums\UserRole::Admin, \App\Enums\UserRole::Supervisor], true))
            <a href="{{ route('team.index') }}" wire:navigate title="Equipo de trabajo"
                class="w-10 h-10 rounded-md flex items-center justify-center {{ $navItemClass('team.*') }}">
                <i class="ph ph-users-three text-[19px]"></i>
            </a>
        @endif

        @if (auth()->user()->role === \App\Enums\UserRole::Admin)
            <a href="{{ route('admin.users.index') }}" wire:navigate title="Usuarios"
                class="w-10 h-10 rounded-md flex items-center justify-center {{ $navItemClass('admin.users.index') }}">
                <i class="ph ph-users text-[19px]"></i>
            </a>
            <a href="{{ route('admin.plants.index') }}" wire:navigate title="Plantas"
                class="w-10 h-10 rounded-md flex items-center justify-center {{ $navItemClass('admin.plants.index') }}">
                <i class="ph ph-factory text-[19px]"></i>
            </a>
        @endif
    </div>

    <div class="mt-auto">
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="w-8 h-8 rounded-full bg-neutral-700 flex items-center justify-center text-[11px] text-ink" title="{{ auth()->user()->name }}">
                    {{ collect(explode(' ', auth()->user()->name))->map(fn ($n) => mb_substr($n, 0, 1))->take(2)->implode('') }}
                </button>
            </x-slot>

            <x-slot name="content">
                <x-dropdown-link :href="route('profile')" wire:navigate>
                    {{ __('Profile') }}
                </x-dropdown-link>

                <button wire:click="logout" class="w-full text-start">
                    <x-dropdown-link>
                        {{ __('Log Out') }}
                    </x-dropdown-link>
                </button>
            </x-slot>
        </x-dropdown>
    </div>
</nav>
```

- [ ] **Step 7: Build assets and verify manually**

```bash
npm install
npm run build
composer run dev
```

Open `/dashboard`, `/ordenes`, `/proveedores`, `/activos/{any-code}`, and one non-redesigned page (`/planes`) in a browser. Confirm: the 72px dark rail renders with working nav links and a working logout, the top bar area renders (even if a page has no header content yet), and no Blade/Vite errors appear in the terminal or `browser-logs`.

- [ ] **Step 8: Commit**

```bash
git add resources/css/nocturne-tokens.css resources/css/app.css tailwind.config.js package.json package-lock.json resources/js/app.js resources/views/layouts/app.blade.php resources/views/livewire/layout/navigation.blade.php
git commit -m "feat: adopt Nocturne dark shell (rail nav, top bar, design tokens)"
```

---

### Task 2: Dashboard backend — deltas, backlog ring, top equipos, atención

**Files:**
- Modify: `app/Livewire/Dashboard.php`
- Test: `tests/Feature/DashboardTest.php` (create)

**Interfaces:**
- Consumes: nothing new from Task 1 (pure backend).
- Produces: view data consumed by Task 3 — `mtbfDelta`, `mttrDelta`, `availabilityDelta`, `preventiveComplianceDelta` (each `?float`, percent change vs. the immediately preceding period of equal length, `null` when not computable), `backlogRingPct` (`int`, 0-100), `topAssets` (`Collection<int, array{name: string, code: string, technician: string, fails: int}>`), `attentionWorkOrder` (`?WorkOrder`, eager-loaded with `asset`).

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/DashboardTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\WorkOrderPriority;
use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderType;
use App\Livewire\Dashboard;
use App\Models\Area;
use App\Models\Asset;
use App\Models\Plant;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $this->actingAs($admin);

        return $admin;
    }

    private function makeAsset(): Asset
    {
        $plant = Plant::factory()->create();

        return Asset::factory()->for(Area::factory()->for($plant))->create();
    }

    public function test_preventive_compliance_delta_compares_to_previous_period_of_equal_length(): void
    {
        $this->actingAsAdmin();
        $asset = $this->makeAsset();

        // Current period (last 90 days): 1 of 2 preventivos completed => 50%.
        WorkOrder::factory()->preventivo()->create([
            'asset_id' => $asset->id,
            'status' => WorkOrderStatus::Completada,
            'opened_at' => now()->subDays(10),
        ]);
        WorkOrder::factory()->preventivo()->create([
            'asset_id' => $asset->id,
            'status' => WorkOrderStatus::Abierta,
            'opened_at' => now()->subDays(5),
        ]);

        // Previous period (90-180 days ago): 2 of 2 preventivos completed => 100%.
        WorkOrder::factory()->preventivo()->create([
            'asset_id' => $asset->id,
            'status' => WorkOrderStatus::Completada,
            'opened_at' => now()->subDays(120),
        ]);
        WorkOrder::factory()->preventivo()->create([
            'asset_id' => $asset->id,
            'status' => WorkOrderStatus::Completada,
            'opened_at' => now()->subDays(130),
        ]);

        Livewire::test(Dashboard::class)
            ->assertViewHas('preventiveCompliance', 50.0)
            ->assertViewHas('preventiveComplianceDelta', -50.0);
    }

    public function test_backlog_ring_percentage_is_share_in_progress_or_waiting(): void
    {
        $this->actingAsAdmin();
        $asset = $this->makeAsset();

        WorkOrder::factory()->create(['asset_id' => $asset->id, 'status' => WorkOrderStatus::Abierta]);
        WorkOrder::factory()->create(['asset_id' => $asset->id, 'status' => WorkOrderStatus::EnProgreso]);
        WorkOrder::factory()->create(['asset_id' => $asset->id, 'status' => WorkOrderStatus::EnProgreso]);
        WorkOrder::factory()->create(['asset_id' => $asset->id, 'status' => WorkOrderStatus::EnEspera]);

        Livewire::test(Dashboard::class)
            ->assertViewHas('backlogTotal', 4)
            ->assertViewHas('backlogRingPct', 75);
    }

    public function test_top_assets_ranks_by_corrective_count_with_most_recent_technician(): void
    {
        $this->actingAsAdmin();
        $plant = Plant::factory()->create();
        $area = Area::factory()->for($plant)->create();
        $assetA = Asset::factory()->for($area)->create();
        $assetB = Asset::factory()->for($area)->create();
        $earlierTech = User::factory()->role(UserRole::Tecnico)->create(['plant_id' => $plant->id]);
        $latestTech = User::factory()->role(UserRole::Tecnico)->create(['plant_id' => $plant->id]);

        WorkOrder::factory()->create([
            'asset_id' => $assetA->id, 'type' => WorkOrderType::Correctivo,
            'assigned_to' => $earlierTech->id, 'opened_at' => now()->subDays(10),
        ]);
        WorkOrder::factory()->create([
            'asset_id' => $assetA->id, 'type' => WorkOrderType::Correctivo,
            'assigned_to' => $latestTech->id, 'opened_at' => now()->subDays(2),
        ]);
        WorkOrder::factory()->create([
            'asset_id' => $assetA->id, 'type' => WorkOrderType::Correctivo,
            'assigned_to' => null, 'opened_at' => now()->subDays(1),
        ]);
        WorkOrder::factory()->create([
            'asset_id' => $assetB->id, 'type' => WorkOrderType::Correctivo,
            'opened_at' => now()->subDays(3),
        ]);

        Livewire::test(Dashboard::class)->assertViewHas('topAssets', function ($topAssets) use ($assetA, $latestTech) {
            $first = $topAssets->first();

            return $first['code'] === $assetA->code
                && $first['fails'] === 3
                && $first['technician'] === $latestTech->name;
        });
    }

    public function test_attention_card_picks_the_oldest_open_order_at_the_highest_present_priority(): void
    {
        $this->actingAsAdmin();
        $asset = $this->makeAsset();

        WorkOrder::factory()->create([
            'asset_id' => $asset->id, 'priority' => WorkOrderPriority::Baja,
            'status' => WorkOrderStatus::Abierta, 'opened_at' => now()->subDays(1),
        ]);
        WorkOrder::factory()->create([
            'asset_id' => $asset->id, 'priority' => WorkOrderPriority::Media,
            'status' => WorkOrderStatus::Abierta, 'opened_at' => now()->subDays(10),
        ]);
        WorkOrder::factory()->create([
            'asset_id' => $asset->id, 'priority' => WorkOrderPriority::Alta,
            'status' => WorkOrderStatus::EnEspera, 'opened_at' => now()->subDays(5),
        ]);
        $olderAlta = WorkOrder::factory()->create([
            'asset_id' => $asset->id, 'priority' => WorkOrderPriority::Alta,
            'status' => WorkOrderStatus::Abierta, 'opened_at' => now()->subDays(8),
        ]);

        Livewire::test(Dashboard::class)
            ->assertViewHas('attentionWorkOrder', fn ($wo) => $wo->id === $olderAlta->id);
    }
}
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `php artisan test --filter=DashboardTest`
Expected: FAIL — `preventiveComplianceDelta`/`backlogRingPct`/`topAssets`/`attentionWorkOrder` are undefined view keys.

- [ ] **Step 3: Implement the new logic**

Replace `app/Livewire/Dashboard.php`:

```php
<?php

namespace App\Livewire;

use App\Enums\WorkOrderPriority;
use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderType;
use App\Models\Asset;
use App\Models\Plant;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public int $period = 90;

    public ?int $plantFilter = null;

    /** @var array{labels: array<int, string>, values: array<int, int>} */
    public array $paretoData = ['labels' => [], 'values' => []];

    /** @var array{labels: array<int, string>, correctivo: array<int, int>, preventivo: array<int, int>} */
    public array $trendData = ['labels' => [], 'correctivo' => [], 'preventivo' => []];

    public function render()
    {
        $start = Carbon::now()->subDays($this->period)->startOfDay();
        $end = Carbon::now()->endOfDay();
        $previousEnd = $start->copy()->subSecond();
        $previousStart = $previousEnd->copy()->subDays($this->period)->startOfDay();

        $current = $this->periodMetrics($start, $end);
        $previous = $this->periodMetrics($previousStart, $previousEnd);

        $backlog = $this->backlogByPriority();
        $backlogTotal = $backlog->sum();
        $backlogInProgressOrWaiting = $this->scopedWorkOrders()
            ->whereIn('status', [WorkOrderStatus::EnProgreso, WorkOrderStatus::EnEspera])
            ->count();

        $this->paretoData = $this->paretoOfFailures($start, $end);
        $this->trendData = $this->monthlyTrend();

        return view('livewire.dashboard', [
            'isMultiPlant' => Auth::user()->role->seesAllPlants(),
            'plants' => Auth::user()->role->seesAllPlants() ? Plant::orderBy('name')->get() : collect(),
            'mtbfHours' => $current['mtbf'],
            'mtbfDelta' => $this->percentDelta($current['mtbf'], $previous['mtbf']),
            'mttrHours' => $current['mttr'],
            'mttrDelta' => $this->percentDelta($current['mttr'], $previous['mttr']),
            'availability' => $current['availability'],
            'availabilityDelta' => $this->percentDelta($current['availability'], $previous['availability']),
            'preventiveCompliance' => $current['preventiveCompliance'],
            'preventiveComplianceDelta' => $this->percentDelta($current['preventiveCompliance'], $previous['preventiveCompliance']),
            'backlogTotal' => $backlogTotal,
            'backlogByPriority' => $backlog,
            'backlogRingPct' => $backlogTotal > 0 ? (int) round($backlogInProgressOrWaiting / $backlogTotal * 100) : 0,
            'topAssets' => $this->topAssetsWithFailures($start, $end),
            'attentionWorkOrder' => $this->attentionWorkOrder(),
        ]);
    }

    private function scopedWorkOrders(): Builder
    {
        return WorkOrder::query()->when(
            $this->plantFilter,
            fn (Builder $q) => $q->whereHas('asset.area', fn (Builder $q2) => $q2->where('plant_id', $this->plantFilter))
        );
    }

    private function scopedAssets(): Builder
    {
        return Asset::query()->when(
            $this->plantFilter,
            fn (Builder $q) => $q->whereHas('area', fn (Builder $q2) => $q2->where('plant_id', $this->plantFilter))
        );
    }

    /**
     * @return array{mtbf: ?float, mttr: ?float, availability: ?float, preventiveCompliance: ?float}
     */
    private function periodMetrics(Carbon $start, Carbon $end): array
    {
        $totalAssets = $this->scopedAssets()->count();
        $failuresCount = $this->scopedWorkOrders()
            ->where('type', WorkOrderType::Correctivo)
            ->whereBetween('opened_at', [$start, $end])
            ->count();

        $periodDays = max($start->diffInDays($end), 1);

        $mtbf = $failuresCount > 0
            ? round(($periodDays * 24 * max($totalAssets, 1)) / $failuresCount, 1)
            : null;

        $mttr = $this->averageRepairHours($start, $end);

        $availability = $mtbf && $mttr
            ? round($mtbf / ($mtbf + $mttr) * 100, 1)
            : null;

        return [
            'mtbf' => $mtbf,
            'mttr' => $mttr,
            'availability' => $availability,
            'preventiveCompliance' => $this->preventiveCompliance($start, $end),
        ];
    }

    private function percentDelta(?float $current, ?float $previous): ?float
    {
        if ($current === null || $previous === null || $previous == 0.0) {
            return null;
        }

        return round(($current - $previous) / $previous * 100, 1);
    }

    private function averageRepairHours(Carbon $start, Carbon $end): ?float
    {
        $minutes = $this->scopedWorkOrders()
            ->where('type', WorkOrderType::Correctivo)
            ->where('status', WorkOrderStatus::Completada)
            ->whereBetween('completed_at', [$start, $end])
            ->get()
            ->avg(fn (WorkOrder $wo) => $wo->started_at->diffInMinutes($wo->completed_at));

        return $minutes ? round($minutes / 60, 1) : null;
    }

    private function preventiveCompliance(Carbon $start, Carbon $end): ?float
    {
        $scheduled = $this->scopedWorkOrders()
            ->where('type', WorkOrderType::Preventivo)
            ->whereBetween('opened_at', [$start, $end])
            ->count();

        if ($scheduled === 0) {
            return null;
        }

        $completed = $this->scopedWorkOrders()
            ->where('type', WorkOrderType::Preventivo)
            ->where('status', WorkOrderStatus::Completada)
            ->whereBetween('opened_at', [$start, $end])
            ->count();

        return round($completed / $scheduled * 100, 1);
    }

    private function backlogByPriority(): Collection
    {
        return $this->scopedWorkOrders()
            ->whereIn('status', [WorkOrderStatus::Abierta, WorkOrderStatus::EnProgreso, WorkOrderStatus::EnEspera])
            ->get()
            ->countBy(fn (WorkOrder $wo) => $wo->priority->value);
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    private function paretoOfFailures(Carbon $start, Carbon $end): array
    {
        $rows = $this->scopedWorkOrders()
            ->where('type', WorkOrderType::Correctivo)
            ->whereBetween('opened_at', [$start, $end])
            ->select('asset_id', DB::raw('count(*) as total'))
            ->groupBy('asset_id')
            ->orderByDesc('total')
            ->limit(8)
            ->with('asset')
            ->get();

        return [
            'labels' => $rows->map(fn ($row) => $row->asset->code)->all(),
            'values' => $rows->pluck('total')->all(),
        ];
    }

    /**
     * @return Collection<int, array{name: string, code: string, technician: string, fails: int}>
     */
    private function topAssetsWithFailures(Carbon $start, Carbon $end): Collection
    {
        $rows = $this->scopedWorkOrders()
            ->where('type', WorkOrderType::Correctivo)
            ->whereBetween('opened_at', [$start, $end])
            ->select('asset_id', DB::raw('count(*) as total'))
            ->groupBy('asset_id')
            ->orderByDesc('total')
            ->limit(3)
            ->with(['asset.workOrders' => fn (Builder $q) => $q
                ->where('type', WorkOrderType::Correctivo)
                ->whereBetween('opened_at', [$start, $end])
                ->whereNotNull('assigned_to')
                ->latest('opened_at')
                ->with('assignedTo'),
            ])
            ->get();

        return $rows->map(fn ($row) => [
            'name' => $row->asset->name,
            'code' => $row->asset->code,
            'technician' => $row->asset->workOrders->first()?->assignedTo?->name ?? '—',
            'fails' => $row->total,
        ]);
    }

    /**
     * The oldest still-open order at the highest priority tier that currently has any —
     * checked as separate queries (Urgente, then Alta, ...) rather than a single
     * `ORDER BY FIELD(...)` so this stays portable to the SQLite test database.
     * WorkOrderPriority::cases() is declared Baja, Media, Alta, Urgente, so walking
     * the reversed array checks highest-priority first.
     */
    private function attentionWorkOrder(): ?WorkOrder
    {
        foreach (array_reverse(WorkOrderPriority::cases()) as $priority) {
            $workOrder = $this->scopedWorkOrders()
                ->whereIn('status', [WorkOrderStatus::Abierta, WorkOrderStatus::EnProgreso, WorkOrderStatus::EnEspera])
                ->where('priority', $priority)
                ->oldest('opened_at')
                ->with('asset')
                ->first();

            if ($workOrder) {
                return $workOrder;
            }
        }

        return null;
    }

    /**
     * @return array{labels: array<int, string>, correctivo: array<int, int>, preventivo: array<int, int>}
     */
    private function monthlyTrend(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->subMonths($i)->startOfMonth());

        $byMonthAndType = $this->scopedWorkOrders()
            ->where('opened_at', '>=', $months->first())
            ->get(['opened_at', 'type'])
            ->groupBy(fn (WorkOrder $wo) => $wo->opened_at->format('Y-m'));

        return [
            'labels' => $months->map(fn (Carbon $m) => $m->translatedFormat('M Y'))->all(),
            'correctivo' => $months->map(
                fn (Carbon $m) => $byMonthAndType->get($m->format('Y-m'), collect())
                    ->where('type', WorkOrderType::Correctivo)->count()
            )->all(),
            'preventivo' => $months->map(
                fn (Carbon $m) => $byMonthAndType->get($m->format('Y-m'), collect())
                    ->where('type', WorkOrderType::Preventivo)->count()
            )->all(),
        ];
    }
}
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `php artisan test --filter=DashboardTest`
Expected: PASS (4 tests).

- [ ] **Step 5: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Livewire/Dashboard.php tests/Feature/DashboardTest.php
git commit -m "feat: add dashboard deltas, backlog ring, top equipos and atención data"
```

---

### Task 3: Dashboard view — Nocturne redesign

**Files:**
- Modify: `resources/views/livewire/dashboard.blade.php`

**Interfaces:**
- Consumes: Task 1's `header` slot mechanism and CSS classes; Task 2's `mtbfDelta`/`mttrDelta`/`availabilityDelta`/`preventiveComplianceDelta`/`backlogRingPct`/`topAssets`/`attentionWorkOrder` view data, plus the pre-existing `mtbfHours`/`mttrHours`/`availability`/`preventiveCompliance`/`backlogTotal`/`backlogByPriority`/`isMultiPlant`/`plants`/`paretoData`/`trendData`.
- Produces: nothing consumed elsewhere.

- [ ] **Step 1: Replace the view**

Replace `resources/views/livewire/dashboard.blade.php`:

```blade
<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-squares-four text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Análisis de mantenimiento</h1>
    </div>

    <div class="flex items-center gap-3">
        @if ($isMultiPlant)
            <select wire:model.live="plantFilter" class="input w-auto">
                <option value="">Todas las plantas</option>
                @foreach ($plants as $plant)
                    <option value="{{ $plant->id }}">{{ $plant->name }}</option>
                @endforeach
            </select>
        @endif

        <select wire:model.live="period" class="input w-auto">
            <option value="30">Últimos 30 días</option>
            <option value="90">Últimos 90 días</option>
            <option value="180">Últimos 180 días</option>
            <option value="365">Último año</option>
        </select>
    </div>
</x-slot>

<div class="space-y-4">
    @php
        $priorityTagClass = fn ($priority) => match ($priority) {
            \App\Enums\WorkOrderPriority::Urgente, \App\Enums\WorkOrderPriority::Alta => 'tag-accent',
            \App\Enums\WorkOrderPriority::Media => 'tag-outline',
            \App\Enums\WorkOrderPriority::Baja => 'tag-neutral',
        };
        $kpis = [
            ['icon' => 'ph-gauge', 'label' => 'MTBF', 'value' => $mtbfHours, 'unit' => 'h', 'sub' => 'Tiempo medio entre fallas', 'delta' => $mtbfDelta, 'invert' => false],
            ['icon' => 'ph-wrench', 'label' => 'MTTR', 'value' => $mttrHours, 'unit' => 'h', 'sub' => 'Tiempo medio de reparación', 'delta' => $mttrDelta, 'invert' => true],
            ['icon' => 'ph-shield-check', 'label' => 'Disponibilidad', 'value' => $availability, 'unit' => '%', 'sub' => 'Basada en MTBF / MTTR', 'delta' => $availabilityDelta, 'invert' => false],
            ['icon' => 'ph-calendar-check', 'label' => 'Cumplimiento preventivo', 'value' => $preventiveCompliance, 'unit' => '%', 'sub' => 'Preventivas completadas / programadas', 'delta' => $preventiveComplianceDelta, 'invert' => false],
        ];
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($kpis as $kpi)
            <div class="card elev-sm p-4">
                <div class="w-8 h-8 rounded bg-accent-500/20 flex items-center justify-center text-accent-300">
                    <i class="ph {{ $kpi['icon'] }} text-base"></i>
                </div>
                <div class="mt-2 font-medium text-2xl text-ink">
                    {{ $kpi['value'] ?? '—' }}<span class="text-sm font-normal text-neutral-400"> {{ $kpi['unit'] }}</span>
                </div>
                <div class="text-xs text-neutral-400">{{ $kpi['label'] }}</div>
                <div class="text-xs text-neutral-400">{{ $kpi['sub'] }}</div>
                @if ($kpi['delta'] !== null)
                    @php $isGood = $kpi['invert'] ? $kpi['delta'] <= 0 : $kpi['delta'] >= 0; @endphp
                    <div class="flex items-center gap-1 text-xs mt-1 {{ $isGood ? 'text-accent-300' : 'text-neutral-400' }}">
                        <i class="ph {{ $kpi['delta'] >= 0 ? 'ph-arrow-up-right' : 'ph-arrow-down-right' }}"></i>
                        {{ $kpi['delta'] > 0 ? '+' : '' }}{{ $kpi['delta'] }}% <span class="text-neutral-500">vs periodo anterior</span>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-stretch">
        <div class="card elev-sm p-4 bg-section">
            <p class="text-xs text-neutral-300">Backlog de órdenes abiertas</p>
            <p class="mt-1 font-medium text-2xl text-ink">{{ $backlogTotal }}</p>

            <div class="mt-4 flex items-center gap-4">
                <div class="w-[76px] h-[76px] rounded-full flex items-center justify-center shrink-0"
                    style="background: conic-gradient(var(--color-accent-400) 0% {{ $backlogRingPct }}%, var(--color-neutral-700) {{ $backlogRingPct }}% 100%);">
                    <div class="w-14 h-14 rounded-full bg-section flex items-center justify-center text-sm font-medium text-ink">
                        {{ $backlogRingPct }}%
                    </div>
                </div>
                <p class="text-xs text-neutral-400">en progreso o en espera del total abierto</p>
            </div>

            <div class="mt-3 pt-3 border-t border-neutral-800 space-y-1.5">
                @forelse ($backlogByPriority as $priority => $count)
                    <div class="flex items-center justify-between text-sm">
                        <span class="tag {{ $priorityTagClass(\App\Enums\WorkOrderPriority::from($priority)) }}">{{ \App\Enums\WorkOrderPriority::from($priority)->label() }}</span>
                        <span class="text-neutral-300">{{ $count }}</span>
                    </div>
                @empty
                    <p class="text-xs text-neutral-400">Sin órdenes pendientes.</p>
                @endforelse
            </div>
        </div>

        <div class="card elev-sm p-4 lg:col-span-2">
            <p class="card-title mb-2">Correctivo vs. preventivo — últimos 6 meses</p>
            <div wire:ignore x-data="trendChart(@entangle('trendData'))">
                <div x-ref="chart"></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-stretch">
        <div class="card elev-sm p-4 lg:col-span-2">
            <p class="card-title mb-2">Pareto de fallas — equipos con más correctivos</p>
            <div wire:ignore x-data="paretoChart(@entangle('paretoData'))">
                <div x-ref="chart"></div>
            </div>
        </div>

        <div class="card elev-sm p-4">
            <div class="flex items-center gap-2 mb-3">
                <i class="ph ph-warning text-accent-300"></i>
                <p class="card-title m-0">Atención</p>
            </div>
            @if ($attentionWorkOrder)
                <div class="border border-neutral-800 rounded-md p-3 flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs text-ink">{{ $attentionWorkOrder->order_number }} · {{ $attentionWorkOrder->asset->code }}</span>
                        <span class="tag {{ $priorityTagClass($attentionWorkOrder->priority) }}">{{ $attentionWorkOrder->priority->label() }}</span>
                    </div>
                    <p class="m-0 text-xs text-neutral-400">{{ $attentionWorkOrder->failure_description ?? $attentionWorkOrder->type->label() }} — abierta {{ $attentionWorkOrder->opened_at->diffForHumans() }}</p>
                    <a href="{{ route('work-orders.show', $attentionWorkOrder) }}" wire:navigate class="text-xs text-accent-300">Reasignar técnico →</a>
                </div>
            @else
                <p class="text-xs text-neutral-400">Sin alertas activas.</p>
            @endif
        </div>
    </div>

    <div class="card elev-sm p-4">
        <p class="card-title mb-3">Top equipos con fallas</p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @forelse ($topAssets as $asset)
                <div class="border border-neutral-800 rounded-md p-3 flex flex-col gap-2">
                    <div class="flex items-center gap-2">
                        <div class="w-[30px] h-[30px] rounded bg-neutral-800 flex items-center justify-center text-accent-300">
                            <i class="ph ph-gear-six"></i>
                        </div>
                        <div>
                            <div class="text-sm text-ink">{{ $asset['name'] }}</div>
                            <div class="text-[11px] font-mono text-neutral-500">{{ $asset['code'] }}</div>
                        </div>
                    </div>
                    <div class="flex justify-between text-xs text-neutral-400">
                        <span>{{ $asset['technician'] }}</span>
                        <span>{{ $asset['fails'] }} fallas</span>
                    </div>
                </div>
            @empty
                <p class="text-xs text-neutral-400 col-span-full">Sin correctivos en el periodo seleccionado.</p>
            @endforelse
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('paretoChart', (data) => ({
        chart: null,
        data,
        init() {
            this.chart = new ApexCharts(this.$refs.chart, this.options());
            this.chart.render();
            this.$watch('data', () => {
                this.chart.updateOptions(this.options());
            });
        },
        options() {
            return {
                chart: { type: 'bar', height: 280, toolbar: { show: false }, background: 'transparent' },
                series: [{ name: 'Fallas', data: this.data.values }],
                xaxis: { categories: this.data.labels, labels: { style: { colors: '#9397ab' } } },
                yaxis: { labels: { style: { colors: '#9397ab' } } },
                colors: ['#968ae0'],
                plotOptions: { bar: { borderRadius: 6 } },
                grid: { borderColor: '#3f424d' },
                theme: { mode: 'dark' },
            };
        },
    }));

    Alpine.data('trendChart', (data) => ({
        chart: null,
        data,
        init() {
            this.chart = new ApexCharts(this.$refs.chart, this.options());
            this.chart.render();
            this.$watch('data', () => {
                this.chart.updateOptions(this.options());
            });
        },
        options() {
            return {
                chart: { type: 'line', height: 280, toolbar: { show: false }, background: 'transparent' },
                series: [
                    { name: 'Correctivo', data: this.data.correctivo },
                    { name: 'Preventivo', data: this.data.preventivo },
                ],
                xaxis: { categories: this.data.labels, labels: { style: { colors: '#9397ab' } } },
                yaxis: { labels: { style: { colors: '#9397ab' } } },
                colors: ['#b5abfc', '#75798c'],
                stroke: { curve: 'smooth', width: [2.5, 2], dashArray: [0, 5] },
                fill: { type: ['gradient', 'solid'], gradient: { opacityFrom: 0.35, opacityTo: 0 } },
                grid: { borderColor: '#3f424d' },
                theme: { mode: 'dark' },
            };
        },
    }));
</script>
@endscript
```

- [ ] **Step 2: Run the existing Dashboard tests**

Run: `php artisan test --filter=DashboardTest`
Expected: PASS (the view changes must not break `assertViewHas`, which reads view data before rendering — but confirm nothing in the new Blade throws, e.g. an undefined-variable error, by also loading the page manually in the next step).

- [ ] **Step 3: Manual verification**

With `composer run dev`/`npm run dev` running, open `/dashboard` as an Admin user. Confirm: 4 KPI cards with delta rows, backlog ring renders as a partial circle, both charts render dark-themed, "Top equipos" and "Atención" sections render (with real or empty-state content depending on seeded data).

- [ ] **Step 4: Commit**

```bash
git add resources/views/livewire/dashboard.blade.php
git commit -m "feat: redesign Dashboard view in the Nocturne style"
```

---

### Task 4: Órdenes de trabajo view — kanban + history redesign

**Files:**
- Modify: `resources/views/livewire/work-orders/index.blade.php`

**Interfaces:**
- Consumes: Task 1's `header` slot and CSS classes. No PHP changes — `App\Livewire\WorkOrders\Index` is untouched; all `wire:click`/`wire:model` targets (`create`, `take`, `transition`, `closeModal`, `save`, the `search`/`typeFilter`/`dateFrom`/`dateTo`/`asset_id`/`type`/`priority`/`execution_type`/`provider_id`/`failure_description` bindings) and `@can` gates are identical to today. This component's board is 3 columns (only `WorkOrderStatus::isOpen()` cases — Abierta/EnProgreso/EnEspera; Completada/Cancelada live in the separate paginated `historial`), per the current `render()` — do not reintroduce a 5-column board.

- [ ] **Step 1: Replace the view**

Replace `resources/views/livewire/work-orders/index.blade.php`:

```blade
<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-clipboard-text text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Órdenes de trabajo</h1>
    </div>

    @can('create', \App\Models\WorkOrder::class)
        <div class="flex items-center gap-3">
            <button wire:click="create" class="btn btn-primary">
                <i class="ph ph-plus"></i> Crear reporte
            </button>
        </div>
    @endcan
</x-slot>

<div class="space-y-4">
    @php
        $priorityTagClass = fn ($priority) => match ($priority) {
            \App\Enums\WorkOrderPriority::Urgente, \App\Enums\WorkOrderPriority::Alta => 'tag-accent',
            \App\Enums\WorkOrderPriority::Media => 'tag-outline',
            \App\Enums\WorkOrderPriority::Baja => 'tag-neutral',
        };
    @endphp

    <div class="flex flex-wrap items-center gap-3">
        <input wire:model.live.debounce.400ms="search" type="text" placeholder="Buscar por N.° de orden, activo o descripción..." class="input w-72">

        <select wire:model.live="typeFilter" class="input w-auto">
            <option value="">Todos los tipos</option>
            <option value="correctivo">Correctivo</option>
            <option value="preventivo">Preventivo</option>
        </select>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-start">
        @foreach ($columns as $column)
            <div class="bg-neutral-900 border border-neutral-800 rounded-md p-3 flex flex-col gap-3">
                <h3 class="text-sm text-neutral-300 flex items-center justify-between m-0">
                    {{ $column->label() }}
                    <span class="text-xs text-neutral-500">{{ $workOrdersByStatus->get($column->value, collect())->count() }}</span>
                </h3>

                <div class="flex flex-col gap-3">
                    @forelse ($workOrdersByStatus->get($column->value, collect()) as $wo)
                        <div wire:key="wo-{{ $wo->id }}" class="card elev-sm p-3 gap-1.5">
                            <a href="{{ route('work-orders.show', $wo) }}" wire:navigate class="block">
                                <p class="m-0 font-mono text-xs text-accent-300">{{ $wo->order_number }}</p>
                                <p class="m-0 font-mono text-xs text-neutral-500">{{ $wo->asset->code }}</p>
                                <p class="m-0 text-sm text-ink">{{ $wo->asset->name }}</p>
                                <p class="mt-1 text-xs text-neutral-400 line-clamp-2">{{ $wo->failure_description ?? $wo->type->label() }}</p>
                            </a>

                            <div class="flex flex-wrap gap-1.5">
                                <span class="tag {{ $priorityTagClass($wo->priority) }}">{{ $wo->priority->label() }}</span>
                                <span class="tag tag-neutral">{{ $wo->type->label() }}</span>
                            </div>

                            <p class="mt-1 text-[11px] text-neutral-500">Abierta {{ $wo->opened_at->diffForHumans() }}</p>

                            <div class="mt-1 flex flex-wrap gap-3 border-t border-neutral-800 pt-2">
                                @can('update', $wo)
                                    @if ($column === \App\Enums\WorkOrderStatus::Abierta)
                                        <button wire:click="take({{ $wo->id }})" class="btn-ghost text-xs">Iniciar</button>
                                    @endif
                                    @if ($column === \App\Enums\WorkOrderStatus::EnProgreso)
                                        <button wire:click="transition({{ $wo->id }}, 'en_espera')" class="text-xs text-neutral-300 hover:text-ink">Pausar</button>
                                        <button wire:click="transition({{ $wo->id }}, 'completada')" class="btn-ghost text-xs">Completar</button>
                                    @endif
                                    @if ($column === \App\Enums\WorkOrderStatus::EnEspera)
                                        <button wire:click="transition({{ $wo->id }}, 'en_progreso')" class="btn-ghost text-xs">Reanudar</button>
                                    @endif
                                    @if ($column->isOpen())
                                        <button wire:click="transition({{ $wo->id }}, 'cancelada')" wire:confirm="¿Cancelar esta orden?" class="text-xs text-neutral-400 hover:text-ink">Cancelar</button>
                                    @endif
                                @endcan
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-neutral-500 text-center py-6">Sin órdenes</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <div class="card elev-sm p-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="card-title m-0">Historial (completadas y canceladas)</h2>

            <div class="flex flex-wrap items-end gap-3">
                <div class="field">
                    <label>Desde</label>
                    <input wire:model.live="dateFrom" type="date" class="input">
                </div>
                <div class="field">
                    <label>Hasta</label>
                    <input wire:model.live="dateTo" type="date" class="input">
                </div>
            </div>
        </div>

        <table class="table mt-4">
            <thead>
                <tr>
                    <th>N° Orden</th><th>Activo</th><th>Descripción</th><th>Prioridad</th><th>Tipo</th><th>Estado</th><th>Abierta</th><th>Completada</th><th>Duración total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($historial as $wo)
                    <tr wire:key="historial-{{ $wo->id }}" class="cursor-pointer" onclick="window.location='{{ route('work-orders.show', $wo) }}'">
                        <td class="font-mono text-xs text-accent-300">{{ $wo->order_number }}</td>
                        <td class="text-ink">{{ $wo->asset->code }} — {{ $wo->asset->name }}</td>
                        <td class="text-muted max-w-xs truncate">{{ $wo->failure_description ?? $wo->type->label() }}</td>
                        <td><span class="tag {{ $priorityTagClass($wo->priority) }}">{{ $wo->priority->label() }}</span></td>
                        <td><span class="tag tag-neutral">{{ $wo->type->label() }}</span></td>
                        <td><span class="tag {{ $wo->status === \App\Enums\WorkOrderStatus::Completada ? 'tag-neutral' : 'tag-outline' }}">{{ $wo->status->label() }}</span></td>
                        <td class="text-muted">{{ $wo->opened_at->format('d/m/Y H:i') }}</td>
                        <td class="text-muted">{{ $wo->completed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="text-muted">{{ \App\Models\WorkOrder::formatDurationMinutes($wo->total_minutes) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-8">No hay órdenes completadas o canceladas en este rango.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">{{ $historial->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto dialog-backdrop grid place-items-center p-4" wire:transition>
            <div class="fixed inset-0" wire:click="closeModal"></div>

            <div class="dialog relative">
                <h2 class="dialog-title">Crear reporte</h2>

                <form wire:submit="save" class="flex flex-col gap-4">
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
                        <label>Tipo</label>
                        <select wire:model="type" class="input">
                            @foreach ($types as $t)
                                <option value="{{ $t->value }}">{{ $t->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-1" />
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
                        <label>Descripción de la falla</label>
                        <textarea wire:model="failure_description" rows="4" class="input"></textarea>
                        <x-input-error :messages="$errors->get('failure_description')" class="mt-1" />
                    </div>

                    <div class="dialog-actions">
                        <button type="button" wire:click="closeModal" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear reporte</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
```

- [ ] **Step 2: Run the existing work-order test suites**

Run: `php artisan test --filter=WorkOrderAssignmentTest`
Run: `php artisan test --filter=WorkOrdersBoardTest`
Expected: both PASS unchanged (they exercise `App\Livewire\WorkOrders\Show` and `App\Livewire\WorkOrders\Index`'s query/filter behavior respectively — no PHP changed in this task, so this confirms the view rewrite didn't alter any Livewire-visible behavior these tests assert on, e.g. via `assertSee`).

- [ ] **Step 3: Manual verification**

Open `/ordenes`. Confirm the 3 columns (Abierta/En progreso/En espera) render, the search box and type filter narrow the board, "Crear reporte" opens the dialog and creates an order, Iniciar/Pausar/Completar/Reanudar/Cancelar buttons still work and respect permissions for a Técnico user vs. an Admin, and the "Historial" table below the board lists completed/cancelled orders with working date-range filters and pagination.

- [ ] **Step 4: Commit**

```bash
git add resources/views/livewire/work-orders/index.blade.php
git commit -m "feat: redesign Órdenes kanban in the Nocturne style"
```

---

### Task 5: Providers backend — active work orders count

**Files:**
- Modify: `app/Livewire/Providers/Index.php`
- Test: `tests/Feature/ProvidersIndexTest.php` (create)

**Interfaces:**
- Produces: each `Provider` in the `providers` view-data paginator gains an `active_work_orders_count` integer attribute (via `withCount`), consumed by Task 6.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/ProvidersIndexTest.php`:

```php
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
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --filter=ProvidersIndexTest`
Expected: FAIL — `active_work_orders_count` is undefined on the model.

- [ ] **Step 3: Implement**

In `app/Livewire/Providers/Index.php`, add the import and update `render()`:

```php
use App\Enums\WorkOrderStatus;
```

```php
    public function render()
    {
        $providers = Provider::query()
            ->withCount(['workOrders as active_work_orders_count' => fn ($q) => $q->whereIn('status', [
                WorkOrderStatus::Abierta,
                WorkOrderStatus::EnProgreso,
                WorkOrderStatus::EnEspera,
            ])])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('specialty', 'like', "%{$this->search}%")))
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.providers.index', [
            'providers' => $providers,
        ]);
    }
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `php artisan test --filter=ProvidersIndexTest`
Expected: PASS.

- [ ] **Step 5: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Livewire/Providers/Index.php tests/Feature/ProvidersIndexTest.php
git commit -m "feat: count each provider's active work orders on the Providers list"
```

---

### Task 6: Proveedores view — table redesign

**Files:**
- Modify: `resources/views/livewire/providers/index.blade.php`

**Interfaces:**
- Consumes: Task 1's `header` slot and CSS classes; Task 5's `active_work_orders_count`.
- Produces: nothing consumed elsewhere.

- [ ] **Step 1: Replace the view**

Replace `resources/views/livewire/providers/index.blade.php`:

```blade
<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-truck text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">Proveedores</h1>
    </div>
</x-slot>

<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <input wire:model.live.debounce.400ms="search" type="text" placeholder="Buscar por nombre o especialidad..." class="input max-w-sm">

        @can('create', \App\Models\Provider::class)
            <button wire:click="create" class="btn btn-primary">
                <i class="ph ph-plus"></i> Nuevo proveedor
            </button>
        @endcan
    </div>

    <div class="card elev-sm p-4">
        <table class="table">
            <thead>
                <tr>
                    <th>Proveedor</th>
                    <th>Especialidad</th>
                    <th>Contacto</th>
                    <th>Órdenes activas</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($providers as $provider)
                    <tr wire:key="provider-{{ $provider->id }}">
                        <td class="text-ink">{{ $provider->name }}</td>
                        <td class="text-muted">{{ $provider->specialty ?? '—' }}</td>
                        <td class="text-muted">{{ $provider->contact_name ?? $provider->email ?? '—' }}</td>
                        <td>{{ $provider->active_work_orders_count }}</td>
                        <td class="text-right whitespace-nowrap">
                            @can('update', $provider)
                                <button wire:click="edit({{ $provider->id }})" class="btn-ghost text-xs">Editar</button>
                            @endcan
                            @can('delete', $provider)
                                <button wire:click="delete({{ $provider->id }})" wire:confirm="¿Eliminar este proveedor?" class="text-xs text-neutral-400 hover:text-ink">Eliminar</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-8">No hay proveedores registrados todavía.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $providers->links() }}</div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto dialog-backdrop grid place-items-center p-4" wire:transition>
            <div class="fixed inset-0" wire:click="closeModal"></div>

            <div class="dialog relative">
                <h2 class="dialog-title">{{ $editing ? 'Editar proveedor' : 'Nuevo proveedor' }}</h2>

                <form wire:submit="save" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="field sm:col-span-2">
                        <label>Nombre de la empresa</label>
                        <input wire:model="name" class="input">
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div class="field">
                        <label>Persona de contacto</label>
                        <input wire:model="contact_name" class="input">
                    </div>

                    <div class="field">
                        <label>Especialidad</label>
                        <input wire:model="specialty" class="input">
                    </div>

                    <div class="field">
                        <label>Teléfono</label>
                        <input wire:model="phone" class="input">
                    </div>

                    <div class="field">
                        <label>Correo</label>
                        <input wire:model="email" type="email" class="input">
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div class="field sm:col-span-2">
                        <label>Dirección</label>
                        <input wire:model="address" class="input">
                    </div>

                    <div class="dialog-actions sm:col-span-2">
                        <button type="button" wire:click="closeModal" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
```

- [ ] **Step 2: Run the Providers test**

Run: `php artisan test --filter=ProvidersIndexTest`
Expected: PASS (unchanged by the view — confirms the view didn't break the component's render path).

- [ ] **Step 3: Manual verification**

Open `/proveedores`. Confirm the table renders with real "Órdenes activas" counts, search/pagination work, and the create/edit dialog opens, validates, and saves.

- [ ] **Step 4: Commit**

```bash
git add resources/views/livewire/providers/index.blade.php
git commit -m "feat: redesign Proveedores as a Nocturne table"
```

---

### Task 7: Detalle de activo backend — MTBF, MTTR, próximo preventivo

**Files:**
- Modify: `app/Livewire/Assets/Show.php`
- Test: `tests/Feature/AssetShowTest.php` (create)

**Interfaces:**
- Produces: view data `mtbfHours` (`?float`), `mttrHours` (`?float`), `nextPreventiveDate` (`?Carbon`), consumed by Task 8. This task ADDS to the current `app/Livewire/Assets/Show.php` (which already has pre-operational-checklist export support from a concurrently-merged feature — `preopExportFrom`/`preopExportTo`/`exportPreOperationalChecklists()`/the `preOperationalChecklists` view key) — it does not replace or remove any of that; Task 8 depends on `preOperationalChecklists` still being present untouched.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/AssetShowTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderType;
use App\Livewire\Assets\Show;
use App\Models\Area;
use App\Models\Asset;
use App\Models\MaintenancePlan;
use App\Models\Plant;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AssetShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_kpi_row_shows_mttr_and_the_nearest_active_preventive_due_date(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $this->actingAs($admin);

        $plant = Plant::factory()->create();
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create();

        WorkOrder::factory()->create([
            'asset_id' => $asset->id,
            'type' => WorkOrderType::Correctivo,
            'status' => WorkOrderStatus::Completada,
            'started_at' => now()->subDays(10),
            'completed_at' => now()->subDays(10)->addHours(4),
        ]);

        MaintenancePlan::factory()->create([
            'asset_id' => $asset->id, 'active' => true, 'next_due_date' => now()->addDays(9),
        ]);
        MaintenancePlan::factory()->create([
            'asset_id' => $asset->id, 'active' => true, 'next_due_date' => now()->addDays(30),
        ]);
        MaintenancePlan::factory()->create([
            'asset_id' => $asset->id, 'active' => false, 'next_due_date' => now()->addDay(),
        ]);

        Livewire::test(Show::class, ['asset' => $asset])
            ->assertViewHas('mttrHours', 4.0)
            ->assertViewHas('nextPreventiveDate', fn ($date) => $date->isSameDay(now()->addDays(9)));
    }

    public function test_kpi_row_has_no_mtbf_or_mttr_when_the_asset_has_no_correctivos(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $this->actingAs($admin);

        $plant = Plant::factory()->create();
        $asset = Asset::factory()->for(Area::factory()->for($plant))->create();

        Livewire::test(Show::class, ['asset' => $asset])
            ->assertViewHas('mtbfHours', null)
            ->assertViewHas('mttrHours', null)
            ->assertViewHas('nextPreventiveDate', null);
    }
}
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `php artisan test --filter=AssetShowTest`
Expected: FAIL — `mtbfHours`/`mttrHours`/`nextPreventiveDate` are undefined view keys.

- [ ] **Step 3: Implement**

The current `app/Livewire/Assets/Show.php` (as of the concurrent pre-operational-checklists merge) already has `preopExportFrom`/`preopExportTo`/`exportPreOperationalChecklists()` and a `preOperationalChecklists` view key — keep all of that. Add the `WorkOrderStatus` and `Collection` imports, add three private methods, and add three keys to the `render()` view array. The full resulting file:

```php
<?php

namespace App\Livewire\Assets;

use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderType;
use App\Exports\AssetMaintenanceExport;
use App\Exports\PreOperationalChecklistExport;
use App\Models\Asset;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.app')]
class Show extends Component
{
    public Asset $asset;

    public bool $showHistory = false;

    public string $exportFrom = '';

    public string $exportTo = '';

    public string $preopExportFrom = '';

    public string $preopExportTo = '';

    public function mount(Asset $asset): void
    {
        $this->authorize('view', $asset);

        $this->asset = $asset->load('area.plant');
    }

    public function openHistory(): void
    {
        $this->showHistory = true;
    }

    public function closeHistory(): void
    {
        $this->showHistory = false;
    }

    public function exportHistory()
    {
        $validated = $this->validate([
            'exportFrom' => ['nullable', 'date'],
            'exportTo' => ['nullable', 'date', 'after_or_equal:exportFrom'],
        ]);

        $from = $validated['exportFrom'] ? Carbon::parse($validated['exportFrom'])->startOfDay() : null;
        $to = $validated['exportTo'] ? Carbon::parse($validated['exportTo'])->endOfDay() : null;

        return Excel::download(
            new AssetMaintenanceExport($this->asset, $from, $to),
            "mantenimiento-{$this->asset->code}.xlsx",
        );
    }

    public function exportPreOperationalChecklists()
    {
        $validated = $this->validate([
            'preopExportFrom' => ['nullable', 'date'],
            'preopExportTo' => ['nullable', 'date', 'after_or_equal:preopExportFrom'],
        ]);

        $from = $validated['preopExportFrom'] ? Carbon::parse($validated['preopExportFrom'])->startOfDay() : null;
        $to = $validated['preopExportTo'] ? Carbon::parse($validated['preopExportTo'])->endOfDay() : null;

        return Excel::download(
            new PreOperationalChecklistExport($this->asset, $from, $to),
            "preoperacionales-{$this->asset->code}.xlsx",
        );
    }

    public function render()
    {
        $workOrders = $this->asset->workOrders()
            ->with(['assignedTo', 'reportedBy', 'maintenancePlan'])
            ->orderByDesc('opened_at')
            ->get();

        $correctivos = $workOrders->where('type', WorkOrderType::Correctivo);

        return view('livewire.assets.show', [
            'workOrders' => $workOrders,
            'correctivos' => $correctivos,
            'preventivos' => $workOrders->where('type', WorkOrderType::Preventivo),
            'preOperationalChecklists' => $this->asset->preOperationalChecklists()
                ->with('performedBy')
                ->orderByDesc('inspected_at')
                ->limit(10)
                ->get(),
            'mtbfHours' => $this->mtbfHours($correctivos),
            'mttrHours' => $this->mttrHours($correctivos),
            'nextPreventiveDate' => $this->nextPreventiveDate(),
        ]);
    }

    /**
     * @param  Collection<int, \App\Models\WorkOrder>  $correctivos
     */
    private function mtbfHours(Collection $correctivos): ?float
    {
        $failures = $correctivos->count();

        if ($failures === 0) {
            return null;
        }

        $hoursObserved = $this->asset->created_at->diffInHours(now());

        return $hoursObserved > 0 ? round($hoursObserved / $failures, 1) : null;
    }

    /**
     * @param  Collection<int, \App\Models\WorkOrder>  $correctivos
     */
    private function mttrHours(Collection $correctivos): ?float
    {
        $minutes = $correctivos
            ->where('status', WorkOrderStatus::Completada)
            ->avg(fn ($wo) => $wo->repair_minutes);

        return $minutes ? round($minutes / 60, 1) : null;
    }

    private function nextPreventiveDate(): ?Carbon
    {
        return $this->asset->maintenancePlans()
            ->where('active', true)
            ->whereNotNull('next_due_date')
            ->orderBy('next_due_date')
            ->first()
            ?->next_due_date;
    }
}
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `php artisan test --filter=AssetShowTest`
Expected: PASS (2 tests).

- [ ] **Step 5: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Livewire/Assets/Show.php tests/Feature/AssetShowTest.php
git commit -m "feat: add per-asset MTBF, MTTR and next-preventive-date data"
```

---

### Task 8: Detalle de activo view — header card, KPI row, table redesign

**Files:**
- Modify: `resources/views/livewire/assets/show.blade.php`

**Interfaces:**
- Consumes: Task 1's `header` slot and CSS classes; Task 7's `mtbfHours`/`mttrHours`/`nextPreventiveDate`; the pre-existing `asset`/`workOrders`/`correctivos`/`preventivos`/`showHistory`/`exportFrom`/`exportTo`/`preOperationalChecklists`/`preopExportFrom`/`preopExportTo`. The "Listas preoperacionales" card (from the concurrently-merged feature) is restyled here, not removed — it is real, shipped functionality, not part of the original design reference.
- Produces: nothing consumed elsewhere.

- [ ] **Step 1: Replace the view**

Replace `resources/views/livewire/assets/show.blade.php`:

```blade
<x-slot name="header">
    <div class="flex items-center gap-3">
        <i class="ph ph-gear-six text-accent-300 text-xl"></i>
        <h1 class="m-0 font-medium text-lg text-ink">{{ $asset->name }}</h1>
    </div>

    <div class="flex items-center gap-3">
        <button wire:click="openHistory" class="btn btn-secondary">
            <i class="ph ph-clock-counter-clockwise"></i> Ver historial
        </button>
    </div>
</x-slot>

<div class="space-y-4">
    @php
        $statusTagClass = fn ($status) => match ($status) {
            \App\Enums\WorkOrderStatus::Completada => 'tag-neutral',
            \App\Enums\WorkOrderStatus::Cancelada => 'tag-outline',
            default => 'tag-accent',
        };
        $priorityTagClass = fn ($priority) => match ($priority) {
            \App\Enums\WorkOrderPriority::Urgente, \App\Enums\WorkOrderPriority::Alta => 'tag-accent',
            \App\Enums\WorkOrderPriority::Media => 'tag-outline',
            \App\Enums\WorkOrderPriority::Baja => 'tag-neutral',
        };
        $assetStatusTagClass = fn ($status) => match ($status) {
            \App\Enums\AssetStatus::Operativo => 'tag-accent',
            \App\Enums\AssetStatus::Mantenimiento => 'tag-outline',
            \App\Enums\AssetStatus::FueraServicio => 'tag-neutral',
        };
        $preopResultTagClass = fn ($result) => match ($result) {
            \App\Enums\PreOperationalResult::Apto => 'tag-neutral',
            \App\Enums\PreOperationalResult::NoApto => 'tag-accent',
        };
    @endphp

    <a href="{{ route('assets.index') }}" wire:navigate class="text-sm text-accent-300">&larr; Volver a activos</a>

    <div class="card elev-sm p-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
            <div class="sm:col-span-1">
                @if ($asset->photo_path)
                    <img src="{{ Storage::disk('public')->url($asset->photo_path) }}" alt="{{ $asset->name }}"
                        class="w-full aspect-square object-cover rounded-md border border-neutral-800">
                @else
                    <div class="w-full aspect-square rounded-md bg-neutral-900 border border-neutral-800 flex items-center justify-center text-neutral-600">
                        <i class="ph ph-image text-4xl"></i>
                    </div>
                @endif
            </div>

            <div class="sm:col-span-2">
                <h2 class="m-0 text-xl text-ink">{{ $asset->name }}</h2>

                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="tag {{ $assetStatusTagClass($asset->status) }}">{{ $asset->status->label() }}</span>
                    <span class="tag tag-neutral">Criticidad {{ $asset->criticality->value }}</span>
                </div>

                <dl class="mt-6 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-neutral-500">Código</dt>
                        <dd class="text-ink">{{ $asset->code }}</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-500">Planta</dt>
                        <dd class="text-ink">{{ $asset->area->plant->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-500">Área</dt>
                        <dd class="text-ink">{{ $asset->area->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-500">Fabricante</dt>
                        <dd class="text-ink">{{ $asset->manufacturer ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-500">Modelo</dt>
                        <dd class="text-ink">{{ $asset->model ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-500">Número de serie</dt>
                        <dd class="text-ink">{{ $asset->serial_number ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="sm:col-span-1">
                @if ($asset->qr_code_path)
                    <img src="{{ Storage::disk('public')->url($asset->qr_code_path) }}" alt="QR {{ $asset->code }}"
                        class="w-full aspect-square object-contain rounded-md bg-white p-2">
                    <a href="{{ Storage::disk('public')->url($asset->qr_code_path) }}" target="_blank" class="mt-2 block text-center text-xs text-accent-300">Ver / imprimir QR</a>
                @else
                    <div class="w-full aspect-square rounded-md bg-neutral-900 border border-neutral-800 flex items-center justify-center text-xs text-neutral-500">
                        Sin QR
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card elev-sm p-4">
            <p class="text-xs text-neutral-400 m-0">MTBF</p>
            <p class="mt-1 font-medium text-xl text-ink">{{ $mtbfHours !== null ? "{$mtbfHours} h" : '—' }}</p>
        </div>
        <div class="card elev-sm p-4">
            <p class="text-xs text-neutral-400 m-0">MTTR</p>
            <p class="mt-1 font-medium text-xl text-ink">{{ $mttrHours !== null ? "{$mttrHours} h" : '—' }}</p>
        </div>
        <div class="card elev-sm p-4">
            <p class="text-xs text-neutral-400 m-0">Próximo preventivo</p>
            <p class="mt-1 font-medium text-xl text-ink">{{ $nextPreventiveDate?->translatedFormat('d M') ?? '—' }}</p>
        </div>
        <div class="card elev-sm p-4">
            <p class="text-xs text-neutral-400 m-0">Criticidad</p>
            <p class="mt-1 font-medium text-xl text-ink">{{ $asset->criticality->label() }}</p>
        </div>
    </div>

    <div class="card elev-sm p-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="card-title m-0">Historial de mantenimiento</h2>
                <p class="text-xs text-neutral-400 mt-0.5">Descarga un Excel con los correctivos y preventivos de este activo.</p>
            </div>

            <form wire:submit="exportHistory" class="flex flex-wrap items-end gap-3">
                <div class="field">
                    <label>Desde</label>
                    <input wire:model="exportFrom" type="date" class="input">
                </div>
                <div class="field">
                    <label>Hasta</label>
                    <input wire:model="exportTo" type="date" class="input">
                </div>
                <button type="submit" class="btn btn-secondary">
                    <i class="ph ph-download-simple"></i> Descargar Excel
                </button>
            </form>
        </div>
        <x-input-error :messages="$errors->get('exportTo')" class="mt-2" />
    </div>

    <div class="card elev-sm p-6">
        <h2 class="card-title m-0 mb-3">Mantenimiento correctivo</h2>

        <table class="table">
            <thead>
                <tr>
                    <th>N° Orden</th><th>Fecha</th><th>Descripción</th><th>Prioridad</th><th>Estado</th><th>Técnico</th><th>Completada</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($correctivos as $wo)
                    <tr wire:key="correctivo-{{ $wo->id }}" class="cursor-pointer" onclick="window.location='{{ route('work-orders.show', $wo) }}'">
                        <td class="font-mono text-xs text-accent-300">{{ $wo->order_number }}</td>
                        <td class="text-muted">{{ $wo->opened_at->format('d/m/Y H:i') }}</td>
                        <td class="text-ink">{{ $wo->failure_description ?? '—' }}</td>
                        <td><span class="tag {{ $priorityTagClass($wo->priority) }}">{{ $wo->priority->label() }}</span></td>
                        <td><span class="tag {{ $statusTagClass($wo->status) }}">{{ $wo->status->label() }}</span></td>
                        <td class="text-muted">{{ $wo->assignedTo->name ?? '—' }}</td>
                        <td class="text-muted">{{ $wo->completed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-8">No hay mantenimientos correctivos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card elev-sm p-6">
        <h2 class="card-title m-0 mb-3">Mantenimiento preventivo</h2>

        <table class="table">
            <thead>
                <tr>
                    <th>N° Orden</th><th>Fecha</th><th>Plan</th><th>Estado</th><th>Técnico</th><th>Completada</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($preventivos as $wo)
                    <tr wire:key="preventivo-{{ $wo->id }}" class="cursor-pointer" onclick="window.location='{{ route('work-orders.show', $wo) }}'">
                        <td class="font-mono text-xs text-accent-300">{{ $wo->order_number }}</td>
                        <td class="text-muted">{{ $wo->opened_at->format('d/m/Y H:i') }}</td>
                        <td class="text-ink">{{ $wo->maintenancePlan?->name ?? 'Mantenimiento preventivo' }}</td>
                        <td><span class="tag {{ $statusTagClass($wo->status) }}">{{ $wo->status->label() }}</span></td>
                        <td class="text-muted">{{ $wo->assignedTo->name ?? '—' }}</td>
                        <td class="text-muted">{{ $wo->completed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-8">No hay mantenimientos preventivos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card elev-sm p-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="card-title m-0">Listas preoperacionales</h2>
                <p class="text-xs text-neutral-400 mt-0.5">Inspecciones de seguridad registradas antes de iniciar turno.</p>
            </div>

            <form wire:submit="exportPreOperationalChecklists" class="flex flex-wrap items-end gap-3">
                <div class="field">
                    <label>Desde</label>
                    <input wire:model="preopExportFrom" type="date" class="input">
                </div>
                <div class="field">
                    <label>Hasta</label>
                    <input wire:model="preopExportTo" type="date" class="input">
                </div>
                <button type="submit" class="btn btn-secondary">
                    <i class="ph ph-download-simple"></i> Descargar Excel
                </button>
            </form>
        </div>
        <x-input-error :messages="$errors->get('preopExportTo')" class="mt-2" />

        <table class="table mt-4">
            <thead>
                <tr>
                    <th>Fecha</th><th>Resultado</th><th>Acción requerida</th><th>Responsable</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($preOperationalChecklists as $checklist)
                    <tr wire:key="preop-{{ $checklist->id }}" class="cursor-pointer" onclick="window.location='{{ route('pre-operational-checklists.show', $checklist) }}'">
                        <td class="text-muted">{{ $checklist->inspected_at->format('d/m/Y H:i') }}</td>
                        <td><span class="tag {{ $preopResultTagClass($checklist->result) }}">{{ $checklist->result->label() }}</span></td>
                        <td class="text-muted">{{ $checklist->required_action->label() }}</td>
                        <td class="text-muted">{{ $checklist->performedBy->name }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-8">No hay listas preoperacionales registradas.</td></tr>
                @endforelse
            </tbody>
        </table>

        <a href="{{ route('pre-operational-checklists.index', ['asset' => $asset->id]) }}" wire:navigate class="mt-3 inline-block text-xs text-accent-300">Ver todas las listas preoperacionales de este activo &rarr;</a>
    </div>

    @if ($showHistory)
        <div class="fixed inset-0 z-50" wire:transition>
            <div class="fixed inset-0" style="background: color-mix(in srgb, var(--color-neutral-900) 60%, transparent);" wire:click="closeHistory"></div>

            <div class="fixed inset-y-0 right-0 w-full max-w-md bg-surface shadow-lg overflow-y-auto">
                <div class="flex items-center justify-between p-6 border-b border-neutral-800">
                    <h2 class="card-title m-0">Historial de mantenimiento</h2>
                    <button wire:click="closeHistory" class="text-neutral-400 hover:text-ink text-xl leading-none">&times;</button>
                </div>

                <div class="p-6 space-y-4">
                    @forelse ($workOrders as $wo)
                        <a href="{{ route('work-orders.show', $wo) }}" wire:navigate
                            class="block rounded-md border border-neutral-800 p-4 hover:border-accent-600">
                            <p class="font-mono text-xs text-accent-300 m-0">{{ $wo->order_number }}</p>
                            <div class="mt-1 flex flex-wrap gap-1.5">
                                <span class="tag {{ $statusTagClass($wo->status) }}">{{ $wo->status->label() }}</span>
                                <span class="tag {{ $priorityTagClass($wo->priority) }}">{{ $wo->priority->label() }}</span>
                                <span class="tag tag-neutral">{{ $wo->type->label() }}</span>
                            </div>
                            <p class="mt-2 text-sm text-ink">{{ $wo->failure_description ?? 'Mantenimiento preventivo programado' }}</p>
                            <p class="mt-1 text-xs text-neutral-500">
                                Abierta {{ $wo->opened_at->format('d/m/Y H:i') }}
                                @if ($wo->completed_at)
                                    · Completada {{ $wo->completed_at->format('d/m/Y H:i') }}
                                @endif
                            </p>
                        </a>
                    @empty
                        <p class="text-sm text-neutral-400">Este activo no tiene mantenimientos registrados todavía.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
```

- [ ] **Step 2: Run the Asset Show tests**

Run: `php artisan test --filter=AssetShowTest`
Run: `php artisan test --filter=PreOperationalChecklistTest`
Expected: both PASS. `PreOperationalChecklistTest` (pre-existing, from the concurrent merge) directly asserts on this same view — `Livewire::test(AssetsShow::class, ...)->assertSee('Listas preoperacionales')->assertSee($checklist->performedBy->name)` — so it is a real regression check that the restyled section still renders that heading text and the technician name.

- [ ] **Step 3: Manual verification**

Open a real `/activos/{code}` page. Confirm: header card shows photo/QR/details, the new 4-KPI row shows real MTBF/MTTR/próximo preventivo/criticidad (or `—` for an asset with no correctivos/planes), both maintenance history tables and the "Listas preoperacionales" table render with tags, both Excel exports (maintenance and pre-operational) still download, and the history drawer still opens.

- [ ] **Step 4: Commit**

```bash
git add resources/views/livewire/assets/show.blade.php
git commit -m "feat: redesign Detalle de activo in the Nocturne style"
```

---

### Task 9: Full regression pass and sign-off

**Files:** none (verification only).

**Interfaces:** none.

- [ ] **Step 1: Run the full automated test suite**

Run: `php artisan test`
Expected: every test PASSES except the one pre-existing, out-of-scope failure named in the Global Constraints' reconciliation note (`WorkOrderReportTest::test_downloading_the_report_returns_a_pdf`, blocked on a local `composer install`/SSL issue unrelated to this plan). That means: `Auth/*`, `ExampleTest`, `ProfileTest`, `TeamTest`, `WorkOrderAssignmentTest`, `WorkOrdersBoardTest`, `PreOperationalChecklistTest`, and the 3 new files from Tasks 2/5/7 (`DashboardTest`, `ProvidersIndexTest`, `AssetShowTest`) all green. Investigate and fix any OTHER failure before proceeding — do not skip or delete a failing test to get to green, and do not attempt to fix the dompdf dependency issue itself (out of scope).

- [ ] **Step 2: Run Pint across the whole diff**

Run: `vendor/bin/pint --dirty --format agent`
Expected: no further changes reported (each task already ran this after its own PHP edits) — this is a final catch-all.

- [ ] **Step 3: Production build check**

Run: `npm run build`
Expected: builds cleanly, no missing-module errors for `@phosphor-icons/web`, no Tailwind errors.

- [ ] **Step 4: Full manual walkthrough**

With `composer run dev` running, log in as an Admin and click through: Dashboard (both with and without a `plantFilter`, and at least two different `period` values), Órdenes (create a report, move a card through Tomar → Completar), Proveedores (search, create, edit, delete), Detalle de activo (a real asset with maintenance history, and one with none), plus one non-redesigned page (e.g. `/planes`) to confirm it still renders inside the new shell without errors — its light-themed inner cards on the dark background are expected and not a bug (see Global Constraints). Check `browser-logs` for JS errors on each page.

- [ ] **Step 5: Final commit (if any cleanup was needed)**

Only if Steps 1-4 required fixes:

```bash
git add -A
git commit -m "fix: address regressions found in final Nocturne redesign verification"
```

If no fixes were needed, there is nothing to commit — the plan is complete as of Task 8's commit.
