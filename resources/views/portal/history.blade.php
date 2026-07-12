@extends('components.layouts.portal')

@php $active = 'history'; @endphp

@section('header_title', 'Historial Terapéutico')

@section('content')
<div class="max-w-5xl mx-auto">

    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-10 gap-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('portal.index') }}" class="w-11 h-11 rounded-2xl bg-white dark:bg-sage-800 border border-cream-300 dark:border-sage-700 text-ink-500 flex items-center justify-center hover:bg-sage-50 dark:hover:bg-sage-700 transition-colors shadow-soft">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="font-serif text-3xl text-sage-900 dark:text-cream-100">Tu trayectoria</h1>
                <p class="text-ink-500 dark:text-sage-300 font-medium mt-1">Revisa cada paso de tu camino hacia el bienestar.</p>
            </div>
        </div>
        <div class="hidden sm:flex items-center gap-3 px-6 py-3 bg-sage-50 dark:bg-sage-900 rounded-full border border-sage-200 dark:border-sage-700">
            <div class="w-2 h-2 rounded-full bg-sage-500 animate-pulse"></div>
            <span class="text-[10px] font-bold uppercase tracking-widest text-sage-700 dark:text-sage-300">Total de sesiones: {{ $appointments->count() }}</span>
        </div>
    </div>

    <!-- Línea de tiempo -->
    <div class="surface p-8 md:p-12">
        <h3 class="font-serif text-2xl text-sage-900 dark:text-cream-100 mb-12 flex items-center gap-4">
            Línea de tiempo terapéutica
            <div class="h-px bg-cream-300 dark:bg-sage-700 flex-1"></div>
        </h3>

        <div class="relative space-y-10 before:content-[''] before:absolute before:left-[1.35rem] md:before:left-[1.85rem] before:top-2 before:bottom-2 before:w-0.5 before:bg-cream-300 dark:before:bg-sage-700">
            @forelse($appointments as $appointment)
            @php
                $esPasada = $appointment->end_at->lt(now());
                $tieneResumen = $appointment->resumen_compartido && filled($appointment->resumen_sesion);
                $tareasSesion = $appointment->tareas ?? collect();
                $expandible = $tieneResumen || $tareasSesion->isNotEmpty();
            @endphp
            <div class="relative pl-12 md:pl-16 group" x-data="{ open: false }">
                <div class="absolute left-0 top-1 w-11 h-11 md:w-14 md:h-14 {{ $esPasada ? 'bg-sage-100 dark:bg-sage-800 text-sage-600 dark:text-sage-300 border-sage-200 dark:border-sage-700' : 'bg-sand-100 dark:bg-sage-800 text-sand-600 dark:text-sand-300 border-sand-200 dark:border-sage-700' }} rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform z-10 border">
                    {!! $appointment->status_badge['icon'] !!}
                </div>

                <div class="bg-cream-100/60 dark:bg-sage-900/50 p-6 md:p-8 rounded-3xl border border-transparent hover:border-cream-300 dark:hover:border-sage-700 hover:bg-white dark:hover:bg-sage-800 hover:shadow-calm transition-all">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                        <div>
                            <span class="text-[10px] font-bold text-sand-600 uppercase tracking-[0.2em] flex items-center gap-2 mb-1">
                                {{ $appointment->disponibilidad->fecha->locale('es')->isoFormat('dddd D [de] MMMM, YYYY') }}
                                <span class="px-2 py-0.5 rounded-full {{ $esPasada ? 'bg-cream-300 text-ink-600' : 'bg-sage-100 text-sage-700' }}">{{ $esPasada ? 'Pasada' : 'Próxima' }}</span>
                            </span>
                            <h4 class="font-serif text-xl text-sage-900 dark:text-cream-100">Sesión clínica especializada</h4>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 self-start">
                            <x-session-badge :cita="$appointment" />
                            <span class="inline-flex px-4 py-1.5 bg-sage-100 dark:bg-sage-800 text-sage-700 dark:text-sage-200 rounded-full text-[9px] font-bold uppercase tracking-widest border border-sage-200 dark:border-sage-700">
                                {{ $appointment->status_badge['label'] }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center pt-6 border-t border-cream-300 dark:border-sage-700">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-white dark:bg-sage-800 p-0.5 shadow-soft border border-cream-300 dark:border-sage-700">
                                <img src="{{ $appointment->especialista->foto_url }}" alt="Foto de {{ $appointment->especialista->usuario->nombre }}" class="w-full h-full object-cover rounded-[0.8rem]">
                            </div>
                            <div>
                                <p class="text-[9px] text-ink-500 font-bold uppercase tracking-widest">Profesional</p>
                                <p class="text-sm font-bold text-ink-700 dark:text-cream-100">Ps. {{ $appointment->especialista->usuario->nombre }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between sm:justify-end gap-6">
                            <div class="text-right">
                                <p class="text-[9px] text-ink-500 font-bold uppercase tracking-widest">Horario</p>
                                <p class="text-sm font-bold text-ink-700 dark:text-cream-100">
                                    {{ \Carbon\Carbon::parse($appointment->disponibilidad->hora_inicio)->format('h:i A') }} —
                                    {{ \Carbon\Carbon::parse($appointment->hora_fin)->format('h:i A') }}
                                </p>
                            </div>
                            @if($tieneResumen)
                                <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-sage-700 dark:text-sage-300 bg-sage-100 dark:bg-sage-800 px-3 py-1.5 rounded-full uppercase tracking-widest">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Con resumen
                                </span>
                            @else
                                <div class="w-10 h-10 rounded-xl bg-white dark:bg-sage-800 border border-cream-300 dark:border-sage-700 flex items-center justify-center text-sage-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($appointment->estado === \App\Models\Cita::STATUS_CANCELLED)
                    <div class="mt-5 pt-5 border-t border-cream-300 dark:border-sage-700">
                        <div class="rounded-2xl bg-slate-100 dark:bg-sage-900/60 border border-slate-200 dark:border-sage-700 p-4">
                            <p class="text-[10px] font-bold text-slate-600 dark:text-sage-300 uppercase tracking-widest">Cita cancelada por {{ $appointment->cancelado_por === 'Paciente' ? 'ti' : 'el especialista' }}</p>
                            @if($appointment->motivo_cancelacion)
                                <p class="text-sm text-ink-600 dark:text-sage-200 italic mt-1">“{{ $appointment->motivo_cancelacion }}”</p>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($expandible)
                    <div class="mt-6 pt-6 border-t border-cream-300 dark:border-sage-700 space-y-6">
                        @if($tieneResumen)
                        <div class="bg-sage-50 dark:bg-sage-900/60 border border-sage-200 dark:border-sage-700 rounded-2xl p-5">
                            <p class="text-[10px] font-bold text-sage-600 dark:text-sage-300 uppercase tracking-[0.2em] mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Resumen de tu sesión
                            </p>
                            <p class="text-sm text-ink-700 dark:text-sage-200 leading-relaxed whitespace-pre-line">{{ $appointment->resumen_sesion }}</p>
                        </div>
                        @endif

                        @if($tareasSesion->isNotEmpty())
                        <div>
                            <p class="text-[10px] font-bold text-sage-600 uppercase tracking-[0.2em] mb-3">Tareas de esta sesión</p>
                            <div class="space-y-2">
                                @foreach($tareasSesion as $tarea)
                                <div class="flex items-start gap-3 text-sm">
                                    <span class="mt-0.5 w-5 h-5 rounded-md flex items-center justify-center shrink-0 {{ $tarea->completada ? 'bg-sage-500 text-white' : 'border-2 border-cream-400' }}">
                                        @if($tarea->completada)<svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>@endif
                                    </span>
                                    <span class="{{ $tarea->completada ? 'line-through text-ink-500' : 'text-ink-700 dark:text-sage-200' }}">{{ $tarea->titulo }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center py-24 text-center">
                <div class="w-20 h-20 bg-cream-100 dark:bg-sage-900 rounded-3xl flex items-center justify-center text-4xl mb-6 border border-cream-300 dark:border-sage-700">📭</div>
                <h4 class="font-serif text-xl text-sage-900 dark:text-cream-100">Sin historial registrado</h4>
                <p class="text-ink-500 mt-2 max-w-xs mx-auto font-medium">Cada sesión completada aparecerá aquí como parte de tu evolución.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
