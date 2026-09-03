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
