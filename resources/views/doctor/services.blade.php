@extends('components.layouts.doctor')

@php $active = 'services'; @endphp

@section('content')
<div class="max-w-4xl mx-auto pb-20">
    <div class="mb-10">
        <h1 class="font-serif text-3xl md:text-4xl text-sage-900 dark:text-cream-100">Mis servicios</h1>
        <p class="text-ink-500 dark:text-sage-300 mt-1">Selecciona los servicios que ofreces para que aparezcan en tu calendario de citas.</p>
    </div>

    @if(session('success'))
        <div class="mb-8 bg-sage-50 border border-sage-200 text-sage-700 px-6 py-4 rounded-2xl font-bold flex items-center gap-3 alert-auto-dismiss">
            <svg class="w-5 h-5 text-sage-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('doctor.update_services') }}" method="POST">
        @csrf
        <div class="surface p-8 md:p-12">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                @foreach($serviciosDisponibles as $servicio)
                    <label class="relative group cursor-pointer">
                        <input type="checkbox" name="servicios[]" value="{{ $servicio->id }}" {{ in_array($servicio->id, $misServiciosIds) ? 'checked' : '' }} class="peer sr-only">
                        <div class="h-full p-6 rounded-3xl border border-cream-300 dark:border-sage-700 peer-checked:border-sage-500 peer-checked:bg-sage-50 dark:peer-checked:bg-sage-800 bg-cream-100/40 dark:bg-sage-900/40 hover:border-sage-300 transition-all duration-300">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 rounded-xl bg-white dark:bg-sage-800 border border-cream-300 dark:border-sage-700 flex items-center justify-center text-xl shadow-sm group-hover:scale-110 transition-transform">
                                    @if(str_contains(strtolower($servicio->nombre), 'psicolog')) 🧠 @elseif(str_contains(strtolower($servicio->nombre), 'yoga')) 🧘 @else 🌿 @endif
                                </div>
                                <div class="w-6 h-6 rounded-full border-2 border-cream-300 flex items-center justify-center peer-checked:bg-sage-500 peer-checked:border-sage-500 transition-colors">
                                    <svg class="w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="4"><path d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </div>
                            <h3 class="font-serif text-lg text-sage-900 dark:text-cream-100 mb-1">{{ $servicio->nombre }}</h3>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-sand-600 mb-3">{{ $servicio->duracion }} minutos</p>
                            <p class="text-sm text-ink-500 dark:text-sage-300 line-clamp-2 leading-relaxed">{{ $servicio->descripcion }}</p>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" class="btn-primary px-10 py-5 text-xs uppercase tracking-[0.2em]">
                Guardar configuración
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            </button>
        </div>
    </form>
</div>
@endsection
