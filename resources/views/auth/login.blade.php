@extends('components.layouts.public')

@php $current = 'login'; $title = 'Ingresar · Sia'; @endphp

@section('content')
<div class="relative min-h-[75vh] flex items-center justify-center py-16 px-4">
    <div class="w-full max-w-[460px] relative z-10">
        <!-- Marca -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-[1.75rem] bg-white dark:bg-sage-900 shadow-calm mb-6 -rotate-3 hover:rotate-0 transition-transform duration-500 ring-1 ring-cream-300 dark:ring-sage-700 p-1.5">
                <div class="w-full h-full rounded-[1.4rem] overflow-hidden">
                    <img src="{{ asset('images/logo_sia.jpeg') }}" alt="Sia" class="w-full h-full object-cover">
                </div>
            </div>
            <h2 class="font-serif text-4xl text-sage-900 dark:text-cream-100 mb-3">
                Bienvenido <span class="italic text-sand-600">de nuevo</span>
            </h2>
            <p class="text-ink-500 dark:text-sage-300">Nos alegra tenerte aquí otra vez.</p>
        </div>

        <!-- Tarjeta -->
        <div class="surface p-8 sm:p-10">
            @if(session('error'))
            <div class="mb-8 bg-sand-50 border border-sand-300 text-sand-800 px-5 py-4 rounded-2xl text-sm font-medium flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="label">Correo electrónico</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        placeholder="tu@email.com" class="field">
                </div>

                <div>
                    <label for="password" class="label">Contraseña</label>
                    <input id="password" type="password" name="password" required
                        placeholder="••••••••" class="field">
                </div>

                <label class="flex items-center cursor-pointer group">
                    <input type="checkbox" name="remember" class="w-5 h-5 rounded-md border-cream-400 text-sage-600 focus:ring-sage-400 focus:ring-offset-0">
                    <span class="ml-3 text-sm font-medium text-ink-600 dark:text-sage-300 group-hover:text-sage-600 transition-colors">Recordarme</span>
                </label>

                <button type="submit" class="btn-primary w-full py-4 text-base">
                    Iniciar sesión
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>
        </div>

        <p class="mt-8 text-center text-ink-500 dark:text-sage-300 font-medium">
            ¿No tienes cuenta? <a href="{{ route('register') }}" class="text-sage-600 hover:text-sage-700 font-bold underline underline-offset-4 decoration-sage-200 transition-colors">Crea una ahora</a>
        </p>
    </div>
</div>
@endsection
