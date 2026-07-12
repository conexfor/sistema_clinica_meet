@extends('components.layouts.admin')

@php $active = 'especialistas'; @endphp

@section('content')
<div class="max-w-4xl mx-auto pb-20">
    <div class="mb-10">
        <a href="{{ route('admin.especialistas.index') }}" class="inline-flex items-center gap-2 text-ink-500 hover:text-sage-600 font-bold text-xs uppercase tracking-widest transition-colors mb-6">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m15 18-6-6 6-6"/></svg>
            Volver al listado
        </a>
        <h1 class="font-serif text-3xl md:text-4xl text-sage-900 dark:text-cream-100">Nuevo especialista</h1>
        <p class="text-ink-500 dark:text-sage-300 mt-1">Registra un nuevo profesional y asigna sus servicios iniciales.</p>
    </div>

    <form action="{{ route('admin.especialistas.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        <div class="surface p-8 md:p-12 space-y-10">
            <section class="space-y-6">
                <div class="flex items-center gap-4"><span class="text-xs font-bold uppercase tracking-[0.3em] text-ink-500">Datos de usuario</span><div class="h-px bg-cream-300 flex-1"></div></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="label">Nombre completo</label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" required placeholder="Ej: Dra. Julia Pérez" class="field">
                        @error('nombre') <p class="text-sand-700 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Correo electrónico</label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="doctor@sia.com" class="field">
                        @error('email') <p class="text-sand-700 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div><label class="label">Contraseña</label><input type="password" name="password" required class="field"></div>
                    <div><label class="label">Confirmar contraseña</label><input type="password" name="password_confirmation" required class="field"></div>
                </div>
            </section>

            <section class="space-y-6">
                <div class="flex items-center gap-4"><span class="text-xs font-bold uppercase tracking-[0.3em] text-ink-500">Perfil profesional</span><div class="h-px bg-cream-300 flex-1"></div></div>
                <div>
                    <label class="label">Especialidad</label>
                    <input type="text" name="especialidad" value="{{ old('especialidad', 'Psicología general') }}" required class="field">
                </div>
                <div>
                    <label class="label">Foto de perfil (opcional)</label>
                    <input type="file" name="foto" accept="image/*" class="field cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-sage-50 file:text-sage-700 hover:file:bg-sage-100">
                    @error('foto') <p class="text-sand-700 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Biografía breve</label>
                    <textarea name="biografia" rows="3" class="field resize-none" placeholder="Describe la trayectoria del profesional...">{{ old('biografia') }}</textarea>
                </div>
            </section>

            <section class="space-y-6">
                <div class="flex items-center gap-4"><span class="text-xs font-bold uppercase tracking-[0.3em] text-ink-500">Servicios iniciales</span><div class="h-px bg-cream-300 flex-1"></div></div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($servicios as $servicio)
                        <label class="flex items-center gap-4 p-4 bg-cream-100/60 border border-cream-300 rounded-2xl cursor-pointer hover:bg-sage-50 hover:border-sage-300 transition-all group">
                            <input type="checkbox" name="servicios[]" value="{{ $servicio->id }}" class="w-5 h-5 rounded text-sage-600 focus:ring-sage-500/20 border-cream-400">
                            <div>
                                <p class="text-sm font-bold text-ink-700 dark:text-cream-100 group-hover:text-sage-700 transition-colors">{{ $servicio->nombre }}</p>
                                <p class="text-[10px] text-ink-500 font-medium">{{ $servicio->duracion }} min · S/ {{ $servicio->precio }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="pt-2">
            <button type="submit" class="btn-primary w-full py-5 text-xs uppercase tracking-[0.2em]">
                Registrar profesional
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </button>
        </div>
    </form>
</div>
@endsection
