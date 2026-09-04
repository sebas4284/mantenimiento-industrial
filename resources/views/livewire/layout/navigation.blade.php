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
    ['route' => 'pre-operational-checklists.index', 'pattern' => 'pre-operational-checklists.*', 'icon' => 'ph-shield-check', 'label' => 'Listas preoperacionales'],
    ['route' => 'spare-parts.index', 'pattern' => 'spare-parts.index', 'icon' => 'ph-package', 'label' => 'Inventario'],
    ['route' => 'providers.index', 'pattern' => 'providers.*', 'icon' => 'ph-truck', 'label' => 'Proveedores'],
];
$navItemClass = fn (string $pattern) => request()->routeIs($pattern)
    ? 'bg-accent-500/20 text-accent-300'
    : 'text-neutral-500 hover:text-neutral-300';
@endphp

<nav
    x-data="{ expanded: JSON.parse(localStorage.getItem('nav-expanded') ?? 'false') }"
    x-effect="localStorage.setItem('nav-expanded', JSON.stringify(expanded))"
    :class="expanded ? 'w-56' : 'w-[72px]'"
    class="shrink-0 bg-neutral-900 border-r border-neutral-800 flex flex-col py-4 gap-4 transition-all duration-200 overflow-hidden"
>
    <div class="flex items-center gap-2 px-3 shrink-0" :class="expanded ? '' : 'justify-center'">
        <a href="{{ route('dashboard') }}" wire:navigate
            class="w-9 h-9 shrink-0 rounded-md bg-accent-800/40 border border-accent-700 flex items-center justify-center text-accent-300 font-medium">
            M
        </a>
        <span x-show="expanded" class="text-sm font-medium text-ink whitespace-nowrap">Mantenimiento</span>
    </div>

    <button
        @click="expanded = !expanded"
        title="{{ __('Expandir/contraer menú') }}"
        class="mx-3 h-9 rounded-md flex items-center gap-3 px-2.5 shrink-0 text-neutral-500 hover:text-neutral-300 hover:bg-neutral-800"
        :class="expanded ? '' : 'justify-center'"
    >
        <i class="ph text-[19px] shrink-0" :class="expanded ? 'ph-caret-line-left' : 'ph-list'"></i>
        <span x-show="expanded" class="text-sm whitespace-nowrap">Contraer menú</span>
    </button>

    <div class="flex flex-col gap-1 px-3 overflow-y-auto overflow-x-hidden">
        @foreach ($navItems as $item)
            <a href="{{ route($item['route']) }}" wire:navigate title="{{ $item['label'] }}"
                class="h-10 rounded-md flex items-center gap-3 px-2.5 shrink-0 {{ $navItemClass($item['pattern']) }}"
                :class="expanded ? '' : 'justify-center'">
                <i class="ph {{ $item['icon'] }} text-[19px] shrink-0"></i>
                <span x-show="expanded" class="text-sm whitespace-nowrap">{{ $item['label'] }}</span>
            </a>
        @endforeach

        @if (in_array(auth()->user()->role, [\App\Enums\UserRole::Admin, \App\Enums\UserRole::Supervisor], true))
            <a href="{{ route('team.index') }}" wire:navigate title="Equipo de trabajo"
                class="h-10 rounded-md flex items-center gap-3 px-2.5 shrink-0 {{ $navItemClass('team.*') }}"
                :class="expanded ? '' : 'justify-center'">
                <i class="ph ph-users-three text-[19px] shrink-0"></i>
                <span x-show="expanded" class="text-sm whitespace-nowrap">Equipo de trabajo</span>
            </a>
        @endif

        @if (auth()->user()->role === \App\Enums\UserRole::Admin)
            <a href="{{ route('admin.users.index') }}" wire:navigate title="Usuarios"
                class="h-10 rounded-md flex items-center gap-3 px-2.5 shrink-0 {{ $navItemClass('admin.users.index') }}"
                :class="expanded ? '' : 'justify-center'">
                <i class="ph ph-user-gear text-[19px] shrink-0"></i>
                <span x-show="expanded" class="text-sm whitespace-nowrap">Usuarios</span>
            </a>
            <a href="{{ route('admin.plants.index') }}" wire:navigate title="Plantas"
                class="h-10 rounded-md flex items-center gap-3 px-2.5 shrink-0 {{ $navItemClass('admin.plants.index') }}"
                :class="expanded ? '' : 'justify-center'">
                <i class="ph ph-factory text-[19px] shrink-0"></i>
                <span x-show="expanded" class="text-sm whitespace-nowrap">Plantas</span>
            </a>
        @endif
    </div>

    <div class="mt-auto px-3 shrink-0">
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="w-full flex items-center gap-3 rounded-md hover:bg-neutral-800 p-1" :class="expanded ? '' : 'justify-center'">
                    <span class="w-8 h-8 shrink-0 rounded-full bg-neutral-700 flex items-center justify-center text-[11px] text-ink" title="{{ auth()->user()->name }}">
                        {{ collect(explode(' ', auth()->user()->name))->map(fn ($n) => mb_substr($n, 0, 1))->take(2)->implode('') }}
                    </span>
                    <span x-show="expanded" class="text-sm text-ink whitespace-nowrap truncate">{{ auth()->user()->name }}</span>
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
