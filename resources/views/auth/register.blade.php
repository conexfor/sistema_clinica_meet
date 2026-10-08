@extends('components.layouts.public')

@php $current = 'register'; $title = 'Reservar cita · ' . config('clinic.name'); @endphp

@section('content')
<div class="relative min-h-screen bg-slate-50 py-16 px-4 sm:px-6">
    <div class="max-w-7xl mx-auto grid gap-0 lg:grid-cols-2 lg:items-stretch rounded-[2rem] overflow-hidden shadow-[0_30px_70px_rgba(15,23,42,0.12)]">
        <div class="bg-gradient-to-br from-sage-700 via-sage-600 to-sand-100 text-white lg:min-h-[640px]">
            <div class="p-10 sm:p-14 h-full flex flex-col justify-between">
                <div class="mb-8">
                    <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.35em] text-white/90">Bienvenido</span>
                </div>
                <h1 class="font-serif text-5xl font-semibold leading-tight">Crea tu cuenta y comienza hoy.</h1>
                <p class="mt-6 max-w-xl text-sm text-white/80">Regístrate para gestionar tus citas y tener el apoyo que necesitas en cada sesión.</p>

                <div class="mt-12 grid gap-4 text-sm text-white/80">
                    <div class="rounded-3xl border border-white/10 bg-white/10 p-5">
                        <p class="font-semibold">Más accesible</p>
                        <p class="mt-2">Regístrate rápidamente y ve tu primera sesión disponible.</p>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/10 p-5">
                        <p class="font-semibold">Todo en uno</p>
                        <p class="mt-2">Tu cuenta te permite agendar, ver historial y recibir notificaciones.</p>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/10 p-5">
                        <p class="font-semibold">Confianza</p>
                        <p class="mt-2">Tus datos están protegidos con nosotros.</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-white/10 px-10 py-8 sm:px-14">
                <div class="flex items-center gap-4 text-sm text-white/80">
                    <div class="flex h-12 w-12 items-center justify-center rounded-3xl bg-white/10">📌</div>
                    <div>
                        <p class="font-semibold">Ejemplo</p>
                        <p>Registra nombre, email y contraseña para entrar al portal.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative bg-white lg:min-h-[620px]">
            <div class="relative overflow-hidden bg-white p-10 sm:p-14 h-full flex flex-col justify-between">
                <div class="mb-10">
                    <span class="inline-flex rounded-full bg-sage-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-sage-700">Registro</span>
                    <h1 class="mt-6 font-serif text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl">Crea tu cuenta</h1>
                    <p class="mt-4 max-w-xl text-sm text-slate-600">Regístrate y empieza a agendar tus sesiones con total facilidad.</p>
                </div>

                <form action="{{ route('register') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label class="label">Nombre completo</label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" required placeholder="Ej: Nicolás Rojas" class="field">
                        @error('nombre') <p class="text-sand-700 text-xs font-semibold mt-2">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="label">Correo electrónico</label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="tu@email.com" class="field">
                            @error('email') <p class="text-sand-700 text-xs font-semibold mt-2">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label">Teléfono</label>
                            <input type="text" name="telefono" value="{{ old('telefono') }}" placeholder="Ej: 999 888 777" class="field">
                            @error('telefono') <p class="text-sand-700 text-xs font-semibold mt-2">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="label">Contraseña</label>
                                <div class="relative">
                                    <input id="password" type="password" name="password" required placeholder="••••••••" class="field pr-12">
                                    <button type="button" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600" data-password-toggle="#password" aria-label="Mostrar contraseña"> 
                                        <svg class="password-show-icon w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg class="password-hide-icon hidden w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.88 9.88a2.25 2.25 0 113.182 3.182"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="label">Confirmar contraseña</label>
                                <div class="relative">
                                    <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="••••••••" class="field pr-12">
                                    <button type="button" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600" data-password-toggle="#password_confirmation" aria-label="Mostrar contraseña"> 
                                        <svg class="password-show-icon w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg class="password-hide-icon hidden w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.88 9.88a2.25 2.25 0 113.182 3.182"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </div>
                            </div>
                    </div>
                    @error('password') <p class="text-sand-700 text-xs font-semibold">{{ $message }}</p> @enderror

                    <button type="submit" class="btn-primary-gradient w-full py-4 text-base">
                        Crear mi cuenta
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>

                <p class="mt-8 text-center text-sm text-slate-600">¿Ya tienes cuenta? <a href="{{ route('login') }}" class="font-semibold text-sage-700 hover:text-sage-800">Iniciar sesión</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
