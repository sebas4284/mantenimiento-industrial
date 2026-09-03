# Nocturne redesign: shell, Dashboard, Órdenes, Proveedores, Detalle de activo

## Overview

Adopt the "Nocturne" dark design system from the Vistora EV Fleet Dashboard
design handoff (`C:\Users\Proyectos\Downloads\Vistora EV Fleet Dashboard\design_handoff_vistora_dashboard\`)
across the app's global navigation shell, and fully redesign four screens to
its component language: Dashboard, Órdenes de trabajo (kanban), Proveedores,
and Detalle de activo. This replaces the current light Breeze/Jetstream-style
theme; there is no light/dark toggle — Nocturne becomes the only theme.

Source design files (reference only, not shipped as-is):
- `design_handoff_vistora_dashboard/README.md` — screen-by-screen spec.
- `design_handoff_vistora_dashboard/reference/Vistora Model.dc.html` — interactive prototype (mock data).
- `design_handoff_vistora_dashboard/reference/nocturne-styles.css` — full DS stylesheet (tokens + `.btn`/`.card`/`.tag`/`.table`/`.field`/`.dialog` classes).
- `design_handoff_vistora_dashboard/tokens/nocturne-tokens.css` and `tokens/tailwind.config.snippet.js` — token extracts.

## Goals

- Global left icon-rail + top-bar shell (Nocturne) replaces the current top
  nav (`resources/views/layouts/app.blade.php` + `resources/views/livewire/layout/navigation.blade.php`)
  on **every** page.
- Dashboard, Órdenes, Proveedores, and Detalle de activo get a full visual
  and (where noted) data redesign to Nocturne's card/table/tag/kanban
  patterns, wired to real Eloquent/Livewire data — no mock data ships.
- Icons move from inline Heroicon-style SVGs to Phosphor
  (`@phosphor-icons/web` npm package, bundled via Vite — no runtime CDN
  dependency).
- Charts: keep ApexCharts (already a dependency, already wired to real data)
  for the Pareto bar chart and the correctivo/preventivo trend line, retheme
  to Nocturne's palette. New chart-like elements with no existing ApexCharts
  precedent (backlog progress ring) are hand-built CSS/inline SVG, matching
  the reference prototype's approach — no new charting dependency.

## Non-goals / explicit scope cuts

- Pages other than the 4 named screens (Activos index, Planes, Checklists,
  Inventario, Usuarios, Plantas, Equipo, Perfil) are **not** visually
  redesigned in this pass. They inherit the new dark shell (rail + top bar)
  but keep their current inner content styling (light cards: `bg-white
  dark:bg-gray-800`, etc.). This is a known, accepted visual inconsistency
  for this pass — those screens get their own redesign pass later.
- No drag-and-drop on the kanban — status changes stay button-driven
  (`take`/`transition`/`cancelar`), unchanged from today.
- KPI mini-sparklines (the 4 Dashboard KPI cards) and the MTTR trend
  sparkline card are **dropped** from this pass — they need bucketing the
  selected period into historical sub-ranges per metric, which is
  materially more backend work than the rest of the Dashboard content for
  comparatively low value. KPI cards show current value + delta vs. the
  prior period of equal length, no sparkline graphic.
- Providers table has no server-side sort/column config — same fixed column
  set as the reference.
- Asset detail keeps two separate history tables (Correctivo / Preventivo)
  rather than the mockup's single merged table — this distinction is
  existing, real functionality and is preserved.

## Architecture

### Tokens & Tailwind

- Add `resources/css/nocturne-tokens.css` (verbatim copy of the handoff's
  `tokens/nocturne-tokens.css`), imported from `resources/css/app.css`.
- Merge `tokens/tailwind.config.snippet.js`'s `theme.extend` into
  `tailwind.config.js`: `colors.bg/surface/ink/accent/neutral/section`,
  `borderRadius.sm/md/lg`, `boxShadow.sm/md/lg`.
- Swap the `sans` font family from Figtree to Inter; update the Google/Bunny
  Fonts `<link>` in `layouts/app.blade.php` to Inter 400/500/600/700.
- `body` gets `bg-bg text-ink` (or the plain CSS token equivalent) — no more
  conditional `dark:` variants needed on the 4 redesigned screens (single
  theme). Existing `dark:` classes elsewhere are left alone (non-goal pages).
- Port the component classes we need from `nocturne-styles.css`
  (`.card`/`.elev-sm`/`.tag*`/`.table`/`.btn*`/`.field`/`.input`/`.dialog*`)
  into `resources/css/app.css` as plain CSS (same source), scoped so they
  don't leak into non-redesigned pages in a way that breaks their existing
  Tailwind utility classes (these are new class names, not overrides of
  Tailwind utilities, so no collision is expected — verify visually after
  wiring the shell).

### Icons

- Add `@phosphor-icons/web` to `package.json` (dependency addition —
  approved). Import it once from `resources/js/app.js` (or `app.css`, per
  whichever the package's docs recommend for the "regular" weight webfont)
  so Vite bundles it; no `<script src="unpkg...">` tag.
- Use `<i class="ph ph-<name>">` per the reference markup, `color:
  currentColor` via the surrounding element's text color (matches
  `nocturne-styles.css` usage).

### Global shell

- `resources/views/livewire/layout/navigation.blade.php` (Volt, keeps its
  `logout()` action) becomes the 72px left rail:
  - Top: small logo/mark tile.
  - Middle: stacked 40×40 icon buttons — Dashboard, Activos, Órdenes,
    Planes, Checklists, Inventario, Proveedores, Equipo, plus Usuarios and
    Plantas gated the same way they are today
    (`in_array(auth()->user()->role, [Admin, Supervisor])` /
    `=== Admin`). Active route gets the tinted-background/accent-300 icon
    treatment; route matching mirrors today's `request()->routeIs(...)`
    per item. Wrap the icon list in its own `overflow-y:auto` in case the
    item count doesn't fit short viewports.
  - Bottom: avatar circle (initials) that opens the existing `x-dropdown`
    (Perfil / Cerrar sesión), restyled — no separate settings gear (out of
    scope, nothing to link it to).
- `resources/views/layouts/app.blade.php` becomes a flex row: the rail,
  then a flex column with the top bar and `<main>`, background
  `--color-bg`.
- Top bar renders a page icon + `<h1>` and optional contextual controls
  (plant/period pickers on Dashboard, "Crear reporte" button on Órdenes).
  Mechanism: a named slot, e.g.:
  ```blade
  <x-slot:header>
      <x-slot:icon>ph-squares-four</x-slot:icon>
      Análisis de mantenimiento
      <x-slot:actions>...plant/period selects...</x-slot:actions>
  </x-slot:header>
  ```
  exact slot shape to be finalized during implementation (simplest Blade
  mechanism that lets each of the 4 pages supply icon/title/actions; other
  pages that don't define it fall back to a generic title-only bar derived
  from the route name or a default per-page title).

## Screen-by-screen mapping

### Dashboard (`app/Livewire/Dashboard.php`, `resources/views/livewire/dashboard.blade.php`)

- **KPI row** (4 `.card.elev-sm`): MTBF, MTTR, Disponibilidad, Cumplimiento
  preventivo — same values already computed. Add **delta vs. previous
  period of equal length** for each: re-run the same private calculation
  methods (`averageRepairHours`, etc.) against the immediately-preceding
  date range (`[start - period days, start]`) and compute `%` change. Show
  as `Δ arrow + %  vs periodo anterior` per the reference's delta row.
  No sparkline graphic (see non-goals).
- **Trend card** (correctivo vs. preventivo, existing `trendChart` Alpine/
  ApexCharts component): retheme only — solid accent-400 line for
  correctivo, dashed neutral-500 for preventivo, gradient area fill under
  correctivo, dark tooltip/grid. No data changes.
- **Backlog card**: existing `backlogTotal`/`backlogByPriority` unchanged.
  Add `backlogRingPct`: `round(count(status in [EnProgreso, EnEspera]) /
  backlogTotal * 100)`, rendered as a CSS `conic-gradient` ring (no library)
  per the reference markup.
- **Pareto card** (existing `paretoChart`): retheme only (accent-500 bars,
  rounded tops, dark grid) — same `paretoOfFailures()` data.
- **Top equipos con fallas** (new): top 3 assets by corrective-failure
  count in the selected period (reuse/extend `paretoOfFailures`'s grouping
  logic, limit 3, `with('asset')`), each row showing asset name/code and
  its most recently assigned technician (`assignedTo` on the most recent
  matching work order) and the failure count.
- **Atención** (new): the single oldest still-open work order with the
  highest priority in scope (e.g. `status` in open set, `priority =
  Alta`, oldest `opened_at`), showing order number, asset code, truncated
  failure description, and a "Reasignar técnico →" link to
  `work-orders.show`. If none match, the card shows an empty state
  ("Sin alertas activas") instead of being hidden.
- MTTR secondary trend card (with sparkline) from the reference: **dropped**
  per non-goals — MTBF/MTTR/Disponibilidad/Cumplimiento already covers this
  metric in the KPI row.

### Órdenes de trabajo (`app/Livewire/WorkOrders/Index.php`, `resources/views/livewire/work-orders/index.blade.php`)

- Same 5 `WorkOrderStatus` columns, same `wire:click` actions
  (`take`/`transition`/`cancelar`) and `@can` gates — no PHP changes.
- Cards restyled to `.card.elev-sm`: order number in accent-300 mono, asset
  code, asset name, truncated description, `.tag` for priority
  (Alta→`tag-accent`, Media→`tag-outline`, Baja→`tag-neutral`) and type
  (`tag-neutral`), relative-time footer.
- "Crear reporte" modal restyled with `.dialog`/`.field`/`.input` classes;
  same form fields and validation.

### Proveedores (`app/Livewire/Providers/Index.php`, `resources/views/livewire/providers/index.blade.php`)

- Layout changes from a card grid to a single `.card.elev-sm` wrapping a
  `.table`: columns Proveedor, Especialidad, Contacto, Órdenes activas,
  plus an Actions column (Editar/Eliminar) behind the existing `@can`
  gates — the reference table is read-only, but Editar/Eliminar are real
  existing features and are kept. The reference's "Estado" column is
  **dropped**: `Provider` has no status concept in this app (confirmed —
  `app/Models/Provider.php` has no status field/enum), so it would be
  fabricated data. Not adding a new status field/migration for this is a
  deliberate scope cut, not an oversight.
- "Órdenes activas" = `$provider->workOrders()` (existing `hasMany`
  relation) count scoped to open `WorkOrderStatus` values
  (Abierta/EnProgreso/EnEspera).
- Search input, "Nuevo proveedor" button, create/edit modal, and pagination
  unchanged in behavior, restyled to Nocturne inputs/dialog.

### Detalle de activo (`app/Livewire/Assets/Show.php`, `resources/views/livewire/assets/show.blade.php`)

- Header card keeps the existing photo, QR code, and detail list (code,
  estado, planta, área, fabricante, modelo, número de serie, criticidad),
  restyled to Nocturne surfaces/tags — not replaced by the mockup's
  icon-only avatar (that would drop real functionality).
- **New** 4-KPI row below the header, using real data:
  - MTBF / MTTR: computed all-time (or since the asset's oldest work
    order) from `$asset->workOrders` already loaded by `Show::render()` —
    same formulas as the Dashboard's, scoped to this one asset, no period
    selector needed here.
  - Próximo preventivo: `min(next_due_date)` across the asset's active
    `MaintenancePlan`s (field already exists on `MaintenancePlan`).
  - Criticidad: existing `$asset->criticality`.
- Correctivo and Preventivo tables stay separate (existing behavior),
  restyled to `.table` with Nocturne tags for status/priority.
- Export-to-Excel form and the history slide-over drawer keep their current
  behavior, restyled only.

## Testing / verification

- No new automated UI/CSS tests (not applicable to Blade/CSS changes).
- Run the existing Feature test suite for work orders (create, status
  transitions), providers (CRUD), and asset export after the Blade/CSS
  changes to confirm `wire:click`/`wire:model`/`@can` wiring wasn't broken
  by the markup rewrite.
- Manual verification in-browser (`npm run dev` / `composer run dev`) of
  Dashboard, Órdenes, Proveedores, and one real Detalle de activo page with
  actual DB data, plus a quick pass over one non-redesigned page (e.g.
  Planes) to confirm the new shell renders sanely even where inner content
  keeps its old light styling.

## Known accepted inconsistency

Every page outside the 4 redesigned screens will show light-themed content
cards inside the new dark shell until they get their own redesign pass.
This is intentional for this iteration, not a bug to silently work around.
