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
        <div class="min-h-screen flex items-center justify-center p-6">
            <div class="w-full max-w-4xl grid lg:grid-cols-2 rounded-lg overflow-hidden" style="box-shadow: var(--shadow-lg);">
                <div class="hidden lg:flex flex-col justify-between p-10 bg-gradient-to-br from-accent-900 via-accent-800 to-accent-600">
                    <a href="/" wire:navigate class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-md bg-white/10 border border-white/20 flex items-center justify-center text-white font-medium">M</div>
                        <span class="font-medium text-ink">{{ config('app.name') }}</span>
                    </a>

                    <div>
                        <i class="ph ph-wrench text-3xl text-accent-100"></i>
                        <h1 class="mt-4 text-3xl text-ink leading-tight">Control total de tu planta, desde un solo lugar.</h1>
                        <p class="mt-3 text-sm text-accent-100/80 max-w-sm">Órdenes de trabajo, activos, planes preventivos y proveedores, en un solo panel.</p>
                    </div>

                    <div class="flex items-center gap-8">
                        <div>
                            <div class="text-2xl font-medium text-ink">94.6%</div>
                            <div class="text-xs text-accent-100/70">Disponibilidad</div>
                        </div>
                        <div>
                            <div class="text-2xl font-medium text-ink">6.2h</div>
                            <div class="text-xs text-accent-100/70">MTTR</div>
                        </div>
                    </div>
                </div>

                <div class="bg-surface p-8 sm:p-10 flex flex-col justify-center">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
