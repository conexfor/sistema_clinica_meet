@extends('components.layouts.portal')

@php $active = 'profile'; @endphp

@section('content')
<div class="max-w-4xl mx-auto pb-12">

    <!-- Encabezado -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('portal.index') }}" class="w-11 h-11 rounded-2xl bg-white dark:bg-sage-800 border border-cream-300 dark:border-sage-700 text-ink-500 flex items-center justify-center hover:bg-sage-50 dark:hover:bg-sage-700 transition-colors shadow-soft shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="font-serif text-3xl text-sage-900 mb-1">Mi perfil</h1>
            <p class="text-sm text-ink-500 font-medium">Revisa y actualiza tus datos personales y de acceso.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-2xl border border-sand-300 bg-sand-50 text-sand-800">
            <p class="text-sm font-bold mb-1">Revisa los siguientes campos:</p>
            <ul class="list-disc list-inside text-xs font-medium space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('portal.profile.update') }}" method="POST" class="space-y-8">
        @csrf @method('PUT')

        <!-- Datos de la cuenta -->
        <div class="surface overflow-hidden">
            <div class="p-8 md:p-10 text-white relative bg-gradient-to-br from-sage-700 to-sage-900 ring-1 ring-inset ring-white/10">
                <div class="orb top-0 right-0 w-64 h-64 bg-sand-300/10 -translate-y-1/2 translate-x-1/2"></div>
                <div class="orb bottom-0 left-0 w-40 h-40 bg-sage-400/10 -translate-x-1/3 translate-y-1/3" style="animation-delay:-4s"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="w-20 h-20 rounded-3xl bg-white/10 border border-white/20 flex items-center justify-center text-3xl font-bold shrink-0">
                        {{ strtoupper(substr($usuario->nombre ?? 'P', 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="font-serif text-2xl">{{ $usuario->nombre }}</h2>
                        <p class="text-cream-100/80 text-sm mt-1">{{ $usuario->email }}</p>
                    </div>
                </div>
            </div>

            <div class="p-8 md:p-10">
                <div class="flex items-center gap-4 mb-6">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-ink-500">Datos de la cuenta</span>
                    <div class="h-px bg-cream-300 flex-1"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="nombre" class="label">Nombre completo</label>
                        <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $usuario->nombre) }}" required class="field @error('nombre') !border-sand-400 @enderror">
                        @error('nombre') <p class="text-sand-700 text-[11px] font-bold mt-2 ml-1 italic">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="label">Correo electrónico</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $usuario->email) }}" required autocomplete="email" class="field @error('email') !border-sand-400 @enderror">
                        @error('email') <p class="text-sand-700 text-[11px] font-bold mt-2 ml-1 italic">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="telefono" class="label">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" value="{{ old('telefono', $usuario->telefono) }}" inputmode="tel" autocomplete="tel" placeholder="Ej. 999 000 000" class="field @error('telefono') !border-sand-400 @enderror">
                        @error('telefono') <p class="text-sand-700 text-[11px] font-bold mt-2 ml-1 italic">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Datos personales -->
        <div class="surface p-8 md:p-10">
            <div class="flex items-center gap-4 mb-6">
                <span class="text-xs font-bold uppercase tracking-[0.25em] text-ink-500">Datos personales</span>
                <div class="h-px bg-cream-300 flex-1"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="dni" class="label">Documento (DNI)</label>
                    <input type="text" id="dni" name="dni" value="{{ old('dni', $paciente->dni) }}" inputmode="numeric" placeholder="Ej. 12345678" class="field @error('dni') !border-sand-400 @enderror">
                    @error('dni') <p class="text-sand-700 text-[11px] font-bold mt-2 ml-1 italic">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="fecha_nacimiento" class="label">Fecha de nacimiento</label>
                    @php
                        $fechaNac = old('fecha_nacimiento', $paciente->fecha_nacimiento
                            ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->format('Y-m-d')
                            : '');
                    @endphp
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ $fechaNac }}" max="{{ now()->toDateString() }}" class="field @error('fecha_nacimiento') !border-sand-400 @enderror">
                    @error('fecha_nacimiento') <p class="text-sand-700 text-[11px] font-bold mt-2 ml-1 italic">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="distrito" class="label">Distrito</label>
                    <input type="text" id="distrito" name="distrito" value="{{ old('distrito', $paciente->distrito) }}" placeholder="Ej. Miraflores" class="field @error('distrito') !border-sand-400 @enderror">
                    @error('distrito') <p class="text-sand-700 text-[11px] font-bold mt-2 ml-1 italic">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="direccion" class="label">Dirección</label>
                    <input type="text" id="direccion" name="direccion" value="{{ old('direccion', $paciente->direccion) }}" placeholder="Av. / Calle y número" class="field @error('direccion') !border-sand-400 @enderror">
                    @error('direccion') <p class="text-sand-700 text-[11px] font-bold mt-2 ml-1 italic">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Seguridad -->
        <div class="surface p-8 md:p-10" x-data="{ change: false }">
            <div class="flex items-center gap-4 mb-6">
                <span class="text-xs font-bold uppercase tracking-[0.25em] text-ink-500">Seguridad</span>
                <div class="h-px bg-cream-300 flex-1"></div>
            </div>

            <label class="inline-flex items-center gap-3 cursor-pointer mb-6">
                <input type="checkbox" x-model="change" class="peer sr-only">
                <span class="w-11 h-6 rounded-full bg-cream-300 peer-checked:bg-sage-500 relative transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:after:translate-x-5"></span>
                <span class="text-sm font-semibold text-ink-700">Cambiar mi contraseña</span>
            </label>

            <div x-show="change" x-cloak x-transition class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="password" class="label">Nueva contraseña</label>
                    <input type="password" id="password" name="password" autocomplete="new-password" placeholder="Mínimo 8 caracteres" class="field @error('password') !border-sand-400 @enderror" x-bind:disabled="!change">
                    @error('password') <p class="text-sand-700 text-[11px] font-bold mt-2 ml-1 italic">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="label">Confirmar contraseña</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" placeholder="Repite la contraseña" class="field" x-bind:disabled="!change">
                </div>
                <p class="md:col-span-2 text-[11px] text-ink-500 font-medium italic ml-1">Déjalo desactivado si no quieres cambiar tu contraseña.</p>
            </div>
        </div>

        <!-- Acciones -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <a href="{{ route('portal.index') }}" class="inline-flex items-center gap-2 text-[11px] font-bold text-ink-500 uppercase tracking-widest hover:text-sage-600 transition-colors order-2 sm:order-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver a mi panel
            </a>
            <button type="submit" class="btn-primary w-full sm:w-auto px-12 py-4 text-xs uppercase tracking-[0.2em] order-1 sm:order-2">
                Guardar cambios
            </button>
        </div>
    </form>
</div>
@endsection
