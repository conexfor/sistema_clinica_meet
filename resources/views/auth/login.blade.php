@extends('components.layouts.public')

@php $current = 'login'; $title = 'Ingresar · ' . config('clinic.name'); @endphp

@section('content')
<div class="relative min-h-screen bg-slate-50 py-16 px-4 sm:px-6">
    <div class="max-w-7xl mx-auto grid grid-cols-1 items-start gap-0 lg:grid-cols-2 lg:items-stretch rounded-[2rem] overflow-hidden shadow-[0_30px_70px_rgba(15,23,42,0.12)]">
        <div class="bg-gradient-to-br from-sage-700 via-sage-600 to-sand-100 text-white lg:min-h-[640px]">
            <div class="p-10 sm:p-14 h-full flex flex-col justify-between">
                <div class="mb-8">
                    <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.35em] text-white/90">Bienvenido</span>
                </div>
                <h1 class="font-serif text-5xl font-semibold leading-tight">Inicia sesión y sigue avanzando.</h1>
                <p class="mt-6 max-w-xl text-sm text-white/80">Accede rápidamente a tu cuenta y gestiona tus citas con total claridad.</p>

                <div class="mt-12 grid gap-4 text-sm text-white/80">
                    <div class="rounded-3xl border border-white/10 bg-white/10 p-5">
                        <p class="font-semibold">Control total</p>
                        <p class="mt-2">Revisa tus próximas sesiones y mensajes desde tu panel.</p>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/10 p-5">
                        <p class="font-semibold">Inicio rápido</p>
                        <p class="mt-2">Accede sin complicaciones a tu historial y programación.</p>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/10 p-5">
                        <p class="font-semibold">Soporte humano</p>
                        <p class="mt-2">Nuestro equipo está listo para ayudarte cuando lo necesites.</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-white/10 px-10 py-8 sm:px-14">
                <div class="flex items-center gap-4 text-sm text-white/80">
                    <div class="flex h-12 w-12 items-center justify-center rounded-3xl bg-white/10">📌</div>
                    <div>
                        <p class="font-semibold">Ejemplo</p>
                        <p>Usa tu correo y contraseña para iniciar sesión.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative bg-white">
            <div class="relative overflow-hidden bg-white p-10 sm:p-14">
                <div class="mb-10">
                    <span class="inline-flex rounded-full bg-sage-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-sage-700">Acceso seguro</span>
                    <h1 class="mt-6 font-serif text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl">Iniciar sesión</h1>
                    <p class="mt-4 max-w-xl text-sm text-slate-600">Accede a tu cuenta para continuar con tus sesiones y gestión de citas.</p>
                </div>

                @if(session('error'))
                    <div class="mb-6 rounded-3xl border border-sand-300 bg-sand-50 px-5 py-4 text-sm font-medium text-sand-800">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="label">Correo electrónico</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="tu@email.com" class="field">
                    </div>

                    <div>
                        <label for="login_password" class="label">Contraseña</label>
                        <div class="relative">
                            <input id="login_password" type="password" name="password" required
                                placeholder="••••••••" class="field pr-12">
                            <button type="button" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600" data-password-toggle="#login_password" aria-label="Mostrar contraseña">
                                <svg class="password-show-icon w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg class="password-hide-icon hidden w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.88 9.88a2.25 2.25 0 113.182 3.182"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <label class="flex items-center cursor-pointer gap-3 text-sm text-slate-600">
                            <input type="checkbox" name="remember" class="h-5 w-5 rounded-md border-cream-400 text-sage-600 focus:ring-sage-400 focus:ring-offset-0">
                            Recordarme
                        </label>
                        <a href="#" class="text-sm font-semibold text-sage-700 hover:text-sage-800">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" class="btn-primary-gradient w-full py-4 text-base">
                        Iniciar sesión
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>

                <p class="mt-8 text-center text-sm text-slate-600">¿No tienes cuenta? <a href="{{ route('register') }}" class="font-semibold text-sage-700 hover:text-sage-800">Registrarse</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
