<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <h1 class="text-xl text-ink m-0">Iniciar sesión</h1>
    <p class="mt-1 text-sm text-neutral-400">Ingresa tus credenciales para continuar</p>

    <!-- Session Status -->
    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form wire:submit="login" class="mt-6 flex flex-col gap-4">
        <!-- Email Address -->
        <div class="field">
            <label for="email">Correo electrónico</label>
            <input wire:model="form.email" id="email" type="email" name="email" required autofocus
                autocomplete="username" class="input" placeholder="nombre@empresa.com">
            <x-input-error :messages="$errors->get('form.email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div class="field" x-data="{ show: false }">
            <label for="password">Contraseña</label>
            <div class="relative">
                <input :type="show ? 'text' : 'password'" wire:model="form.password" id="password" name="password"
                    required autocomplete="current-password" class="input pr-10" placeholder="••••••••">
                <button type="button" @click="show = !show"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-neutral-500 hover:text-neutral-300">
                    <i class="ph" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('form.password')" class="mt-1" />
        </div>

        <!-- Remember Me + Forgot Password -->
        <div class="flex items-center justify-between">
            <label for="remember" class="flex items-center gap-2 text-sm text-neutral-400">
                <input wire:model="form.remember" id="remember" type="checkbox" name="remember"
                    class="rounded border-neutral-700 bg-surface text-accent-500 focus:ring-accent-500">
                Recordarme
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" wire:navigate class="text-sm text-accent-300 hover:text-accent-200">
                    ¿Olvidaste tu contraseña?
                </a>
            @endif
        </div>

        <button type="submit" class="btn btn-primary w-full justify-center">Ingresar</button>
    </form>
</div>
