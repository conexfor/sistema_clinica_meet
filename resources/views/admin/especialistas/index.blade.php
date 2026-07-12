@extends('components.layouts.admin')

@php $active = 'especialistas'; @endphp

@section('content')
<div class="max-w-7xl mx-auto pb-20">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-10 gap-6">
        <div>
            <h1 class="font-serif text-3xl md:text-4xl text-sage-900 dark:text-cream-100">Gestión de especialistas</h1>
            <p class="text-ink-500 dark:text-sage-300 mt-1">Administra el equipo profesional de Sia.</p>
        </div>
        <a href="{{ route('admin.especialistas.create') }}" class="btn-primary !rounded-2xl px-8 py-4 text-sm">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Nuevo especialista
        </a>
    </div>

    @if(session('success'))
        <div class="mb-8 bg-sage-50 border border-sage-200 text-sage-700 px-6 py-4 rounded-2xl font-bold flex items-center gap-3 alert-auto-dismiss">
            <svg class="w-5 h-5 text-sage-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($especialistas as $especialista)
            <div class="surface surface-hover p-8 overflow-hidden relative">
                <div class="flex items-center gap-5 mb-8 relative z-10">
                    <div class="w-20 h-20 rounded-2xl overflow-hidden border-4 border-cream-100 shadow-soft shrink-0">
                        <img src="{{ $especialista->foto_url }}" alt="{{ $especialista->usuario->nombre }}" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h3 class="font-serif text-xl text-sage-900 dark:text-cream-100">{{ $especialista->usuario->nombre }}</h3>
                        <p class="text-sand-600 font-bold text-xs uppercase tracking-widest">{{ $especialista->especialidad }}</p>
                    </div>
                </div>

                <div class="space-y-4 mb-8 relative z-10">
                    <div class="flex items-center gap-3 text-ink-500">
                        <svg class="w-4 h-4 text-sage-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        <span class="text-sm font-medium">{{ $especialista->usuario->email }}</span>
                    </div>
                    <div class="pt-4 border-t border-cream-300 dark:border-sage-700">
                        <h4 class="text-[10px] font-bold uppercase tracking-widest text-ink-500 mb-3">Servicios ofrecidos</h4>
                        <div class="flex flex-wrap gap-2">
                            @forelse($especialista->servicios as $servicio)
                                <span class="px-3 py-1 bg-sage-50 text-sage-700 rounded-lg text-[10px] font-bold uppercase tracking-tight border border-sage-100">{{ $servicio->nombre }}</span>
                            @empty
                                <span class="text-xs text-ink-400 italic">Sin servicios asignados</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-6 border-t border-cream-300 dark:border-sage-700 relative z-10">
                    <a href="{{ route('admin.especialistas.edit', $especialista->id) }}" class="flex-1 py-3 bg-cream-100 dark:bg-sage-800 text-ink-700 dark:text-cream-100 rounded-xl font-bold text-xs hover:bg-sage-50 dark:hover:bg-sage-700 transition-colors text-center">Editar perfil</a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center">
                <div class="w-20 h-20 bg-cream-100 rounded-3xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-sage-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h3 class="font-serif text-xl text-sage-900 dark:text-cream-100">No hay especialistas registrados</h3>
                <p class="text-ink-500 mt-1">Comienza añadiendo el primer profesional al sistema.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
