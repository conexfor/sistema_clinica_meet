@extends('components.layouts.public')

@php $current = 'register'; $title = 'Reservar cita · Sia'; @endphp

@section('content')
<div class="relative min-h-[75vh] flex items-center justify-center py-16 px-4 overflow-hidden">
    <!-- Orbes decorativos, muy sutiles -->
    <div class="orb -top-16 -right-20 w-80 h-80 bg-sand-200/30"></div>
    <div class="orb bottom-0 -left-20 w-72 h-72 bg-sage-300/30" style="animation-delay:-5s"></div>

    <div class="w-full max-w-[620px] relative z-10">
        <!-- Marca -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-[1.75rem] bg-white dark:bg-sage-900 shadow-calm mb-6 rotate-3 hover:rotate-0 transition-transform duration-500 ring-1 ring-cream-300 dark:ring-sage-700 p-1.5">
                <div class="w-full h-full rounded-[1.4rem] overflow-hidden">
                    <img src="{{ asset('images/logo_sia.jpeg') }}" alt="Sia" class="w-full h-full object-cover">
                </div>
            </div>
            <h2 class="font-serif text-4xl text-sage-900 dark:text-cream-100 mb-3">
                Únete a <span class="italic text-gradient">Sia</span>
            </h2>
            <p class="text-ink-500 dark:text-sage-300">Tu camino hacia el bienestar comienza aquí.</p>
        </div>

        <!-- Tarjeta -->
        <div class="surface relative overflow-hidden p-8 sm:p-10">
            <span class="absolute top-0 left-0 right-0 h-1" style="background: linear-gradient(90deg, var(--color-sand-400), var(--color-sage-400));"></span>
            <form action="{{ route('register') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label class="label">Nombre completo</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required placeholder="Ej: Nicolás Rojas" class="field">
                    @error('nombre') <p class="text-sand-700 text-xs font-semibold mt-2">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="label">Contraseña</label>
                        <input type="password" name="password" required placeholder="••••••••" class="field">
                    </div>
                    <div>
                        <label class="label">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" required placeholder="••••••••" class="field">
                    </div>
                </div>
                @error('password') <p class="text-sand-700 text-xs font-semibold">{{ $message }}</p> @enderror

                <button type="submit" class="btn-primary-gradient w-full py-4 text-base">
                    Crear mi cuenta
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>
        </div>

        <p class="mt-8 text-center text-ink-500 dark:text-sage-300 font-medium">
            ¿Ya eres parte de Sia? <a href="{{ route('login') }}" class="text-sage-600 hover:text-sage-700 font-bold underline underline-offset-4 decoration-sage-200 transition-colors">Inicia sesión</a>
        </p>
    </div>
</div>
@endsection
