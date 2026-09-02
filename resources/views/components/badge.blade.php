@props(['color' => 'zinc'])

@php
$classes = match ($color) {
    'green' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
    'amber' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
    'red' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
    'sky' => 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300',
    default => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700/40 dark:text-zinc-300',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium $classes"]) }}>
    {{ $slot }}
</span>
