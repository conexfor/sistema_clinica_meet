@extends('components.layouts.app')

@php
    $active = 'profile';
@endphp

@section('content')
<div class="max-w-3xl mx-auto pb-12">
    <div class="surface overflow-hidden">
        <div class="bg-gradient-to-br from-sage-700 to-sage-900 p-8 md:p-10 text-white relative">
            <div class="absolute top-0 right-0 w-64 h-64 bg-sand-300/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="flex items-center gap-5 relative z-10">
                <div class="w-20 h-20 rounded-3xl bg-white/10 border border-white/20 flex items-center justify-center text-3xl font-bold shrink-0">
                    {{ strtoupper(substr($usuario->nombre ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <h1 class="font-serif text-3xl">Mi perfil</h1>
                    <p class="text-cream-100/80 text-sm mt-1">{{ $usuario->email }}</p>
                </div>
            </div>
        </div>

        <div class="p-8 md:p-10">
            @if($errors->any())
                <div class="mb-6 p-4 rounded-2xl border border-sand-300 bg-sand-50 text-sand-800">
                    <p class="text-sm font-bold mb-2">Revisa los siguientes campos:</p>
                    <ul class="list-disc list-inside text-xs font-medium space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="nombre" class="label">Nombre completo</label>
                        <input id="nombre" name="nombre" type="text" value="{{ old('nombre', $usuario->nombre) }}" class="field" required>
                    </div>
                    <div>
                        <label for="email" class="label">Correo electrónico</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $usuario->email) }}" class="field" required>
                    </div>
                    <div class="md:col-span-2">
                        <label for="telefono" class="label">Teléfono</label>
                        <input id="telefono" name="telefono" type="tel" value="{{ old('telefono', $usuario->telefono) }}" class="field" placeholder="Ej. 999 000 000">
                    </div>
                    <div>
                        <label for="password" class="label">Nueva contraseña</label>
                        <input id="password" name="password" type="password" class="field" placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                    </div>
                    <div>
                        <label for="password_confirmation" class="label">Confirmar contraseña</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="field" placeholder="Repite la contraseña" autocomplete="new-password">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ url()->previous() ?: route('inicio') }}" class="btn-secondary px-6 py-3 text-[11px] uppercase tracking-[0.2em]">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary px-8 py-3 text-[11px] uppercase tracking-[0.2em]">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
