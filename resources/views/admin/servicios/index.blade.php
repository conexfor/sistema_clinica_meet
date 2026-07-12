@extends('components.layouts.admin')

@php $active = 'servicios'; @endphp

@section('content')
<div class="max-w-7xl mx-auto pb-20">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-10 gap-6">
        <div>
            <h1 class="font-serif text-3xl md:text-4xl text-sage-900 dark:text-cream-100">Gestión de servicios</h1>
            <p class="text-ink-500 dark:text-sage-300 mt-1">Administra los tipos de consulta, precios y duraciones de la clínica.</p>
        </div>
        <a href="{{ route('admin.servicios.create') }}" class="btn-primary !rounded-2xl px-8 py-4 text-xs uppercase tracking-widest">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Nuevo servicio
        </a>
    </div>

    @if(session('success'))
        <div class="mb-8 p-4 bg-sage-50 border border-sage-200 text-sage-700 rounded-2xl font-bold text-sm flex items-center gap-3 alert-auto-dismiss">
            <svg class="w-5 h-5 text-sage-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($servicios as $servicio)
            <div class="surface surface-hover p-8 flex flex-col relative overflow-hidden {{ $servicio->esta_activo ? '' : 'opacity-70 border-dashed' }}">
                <div class="relative z-10 flex-1">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <span class="chip-sage">{{ $servicio->categoria }}</span>
                            @if(!$servicio->esta_activo)
                                <span class="chip-sand">Inactivo</span>
                            @endif
                        </div>
                        <div class="flex gap-1">
                            <a href="{{ route('admin.servicios.edit', $servicio->id) }}" class="p-2 text-ink-400 hover:text-sage-600 transition-colors">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                            </a>
                            <form action="{{ route('admin.servicios.toggle', $servicio->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="p-2 {{ $servicio->esta_activo ? 'text-ink-400 hover:text-sand-600' : 'text-sage-500 hover:text-sage-700' }} transition-colors" title="{{ $servicio->esta_activo ? 'Desactivar' : 'Activar' }}">
                                    @if($servicio->esta_activo)
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><line x1="9" x2="15" y1="9" y2="15"/><line x1="15" x2="9" y1="9" y2="15"/></svg>
                                    @else
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                    @endif
                                </button>
                            </form>
                        </div>
                    </div>

                    <h3 class="font-serif text-xl {{ $servicio->esta_activo ? 'text-sage-900 dark:text-cream-100' : 'text-ink-400' }} mb-2">{{ $servicio->nombre }}</h3>
                    <p class="text-ink-500 dark:text-sage-300 text-sm leading-relaxed mb-6">{{ Str::limit($servicio->descripcion, 100) }}</p>

                    <div class="grid grid-cols-2 gap-4 pt-6 border-t border-cream-300 dark:border-sage-700">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500 mb-1">Precio</p>
                            <p class="font-serif text-lg text-sage-900 dark:text-cream-100">S/ {{ number_format($servicio->precio, 0) }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500 mb-1">Duración</p>
                            <p class="font-serif text-lg text-sage-900 dark:text-cream-100">{{ $servicio->duracion }} min</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-cream-300 dark:border-sage-700 flex items-center justify-between relative z-10">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-sage-500"></div>
                        <p class="text-xs font-bold text-ink-600">{{ $servicio->especialistas_count }} profesionales</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @foreach($servicio->modalidades as $mod)
                            <span class="px-2 py-0.5 bg-cream-200 text-ink-500 rounded-md font-bold text-[8px] uppercase tracking-tighter" title="{{ $mod->descripcion }}">{{ $mod->nombre }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center surface !rounded-3xl border-dashed">
                <div class="w-20 h-20 bg-cream-100 rounded-3xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-sage-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                </div>
                <h3 class="font-serif text-xl text-sage-900 dark:text-cream-100 mb-2">No hay servicios registrados</h3>
                <p class="text-ink-500 mb-8">Comienza creando tu primer tipo de consulta.</p>
                <a href="{{ route('admin.servicios.create') }}" class="btn-primary">Crear primer servicio</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
