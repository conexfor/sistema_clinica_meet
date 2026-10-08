@extends('components.layouts.doctor')

@php $active = 'profile'; @endphp

@section('header_title', 'Mi Perfil Profesional')

@section('content')
<div class="max-w-4xl mx-auto pb-12">

    @if(session('success'))
        <div class="mb-8 p-4 bg-sage-50 border border-sage-200 rounded-3xl text-sage-700 font-bold text-sm flex items-center gap-3 alert-auto-dismiss">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="surface overflow-hidden">
        <div class="p-12 text-white relative bg-gradient-to-br from-sage-700 to-sage-900">
            <div class="absolute top-0 right-0 w-64 h-64 bg-sand-300/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <h1 class="font-serif text-3xl relative z-10">Configuración de perfil</h1>
            <p class="text-cream-100/80 mt-2 relative z-10">Personaliza cómo te ven tus pacientes en {{ config('clinic.name') }}.</p>
        </div>

        <form action="{{ route('doctor.profile.update') }}" method="POST" enctype="multipart/form-data" class="p-8 md:p-12">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <div class="md:col-span-1 flex flex-col items-center">
                    <div class="relative group">
                        <div class="w-48 h-48 rounded-3xl overflow-hidden border-4 border-cream-100 shadow-calm relative">
                            <img id="photo-preview" src="{{ $especialista->foto_url }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <div class="absolute inset-0 bg-sage-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                        </div>
                        <input type="file" name="foto" id="foto-input" class="hidden" accept="image/*" onchange="previewImage(this)">
                        <button type="button" onclick="document.getElementById('foto-input').click()" class="absolute -bottom-4 left-1/2 -translate-x-1/2 bg-white text-sage-800 px-6 py-2 rounded-full font-bold text-[10px] uppercase tracking-widest shadow-calm border border-cream-300 hover:bg-cream-100 transition-all">
                            Cambiar foto
                        </button>
                    </div>
                    <p class="text-[10px] text-ink-500 font-bold mt-10 uppercase tracking-widest text-center leading-relaxed">Formatos: JPG, PNG.<br>Máximo 2MB.</p>
                </div>

                <div class="md:col-span-2 space-y-7">
                    <div>
                        <label class="label">Especialidad clínica</label>
                        <input type="text" name="especialidad" value="{{ old('especialidad', $especialista->especialidad) }}" required class="field">
                        @error('especialidad') <p class="text-sand-700 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Biografía profesional</label>
                        <textarea name="biografia" rows="6" required class="field resize-none">{{ old('biografia', $especialista->biografia) }}</textarea>
                        <p class="text-[10px] text-ink-500 font-medium mt-2 ml-1 italic">Describe tu experiencia y enfoque terapéutico para los pacientes.</p>
                        @error('biografia') <p class="text-sand-700 text-[10px] font-bold mt-2 ml-1 italic">{{ $message }}</p> @enderror
                    </div>
                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="btn-primary w-full md:w-auto px-12 py-5 text-xs uppercase tracking-[0.2em]">Guardar cambios</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Conexión con Google Calendar / Meet --}}
    <div class="surface p-8 md:p-10 mt-8">
        <div class="flex flex-col sm:flex-row sm:items-center gap-6">
            <div class="w-14 h-14 rounded-2xl bg-sage-100 flex items-center justify-center text-2xl shrink-0" aria-hidden="true">📅</div>
            <div class="flex-1">
                <h2 class="font-serif text-xl text-sage-900">Google Calendar y Meet</h2>
                <p class="text-ink-500 text-sm mt-1 leading-relaxed">
                    Conecta tu cuenta de Google para generar automáticamente el enlace de Meet de cada sesión virtual
                    y agendar la cita en tu calendario y en el de tus pacientes.
                </p>
            </div>
            <div class="shrink-0">
                @if(auth()->user()->google_access_token)
                    <div class="flex flex-col items-center sm:items-end gap-2">
                        <span class="chip-sage">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Conectado
                        </span>
                        <a href="{{ route('google.auth') }}" class="text-[10px] font-bold text-ink-500 uppercase tracking-widest hover:text-sage-600 transition-colors">Reconectar</a>
                    </div>
                @else
                    <a href="{{ route('google.auth') }}" class="btn-primary !px-6 !py-3 text-xs uppercase tracking-widest">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Conectar Google
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-10 text-center">
        <a href="{{ route('doctor.dashboard') }}" class="inline-flex items-center gap-2 text-[10px] font-bold text-ink-500 uppercase tracking-widest hover:text-sage-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Volver al panel
        </a>
    </div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) { document.getElementById('photo-preview').src = e.target.result; }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
