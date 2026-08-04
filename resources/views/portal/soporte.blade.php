@extends('components.layouts.portal')

@php $active = 'soporte'; @endphp

@section('header_title', 'Mis reportes')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-10 gap-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('portal.index') }}" class="w-11 h-11 rounded-2xl bg-white dark:bg-sage-800 border border-cream-300 dark:border-sage-700 text-ink-500 flex items-center justify-center hover:bg-sage-50 dark:hover:bg-sage-700 transition-colors shadow-soft">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="font-serif text-3xl text-sage-900 dark:text-cream-100">Mis reportes de soporte</h1>
                <p class="text-ink-500 dark:text-sage-300 font-medium mt-1">Aquí ves el estado de tus reportes y las respuestas del equipo.</p>
            </div>
        </div>
        <button x-data @click="$dispatch('open-support')" class="btn-primary !rounded-2xl px-6 py-3 text-xs uppercase tracking-widest shrink-0">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Nuevo reporte
        </button>
    </div>

    <div class="space-y-5">
        @forelse($tickets as $ticket)
            @php
                $accentMap = ['abierto' => 'var(--color-sand-400), var(--color-sand-600)', 'en_proceso' => 'var(--color-sage-300), var(--color-sage-500)', 'resuelto' => 'var(--color-sage-500), var(--color-sage-700)'];
                $accent = $accentMap[$ticket->estado] ?? 'var(--color-cream-300), var(--color-cream-400)';
            @endphp
            <div class="surface relative overflow-hidden p-6">
                <span class="absolute top-0 left-0 right-0 h-1" style="background: linear-gradient(90deg, {{ $accent }});"></span>
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                            @php $b = ['abierto' => 'chip-sand', 'en_proceso' => 'chip', 'resuelto' => 'chip-sage'][$ticket->estado] ?? 'chip'; @endphp
                            <span class="{{ $b }}">{{ ['abierto' => 'Abierto', 'en_proceso' => 'En proceso', 'resuelto' => 'Resuelto'][$ticket->estado] ?? ucfirst($ticket->estado) }}</span>
                            <span class="text-xs font-semibold text-ink-500 dark:text-sage-400">{{ $ticket->categoria_label }}</span>
                        </div>
                        <h3 class="font-serif text-lg text-sage-900 dark:text-cream-100">{{ $ticket->asunto }}</h3>
                    </div>
                    <span class="text-xs text-ink-400 whitespace-nowrap shrink-0">{{ $ticket->created_at->format('d/m/Y') }}</span>
                </div>

                <p class="text-sm text-ink-600 dark:text-sage-300 whitespace-pre-line bg-cream-100/60 dark:bg-sage-800/40 rounded-2xl p-4">{{ $ticket->mensaje }}</p>

                @if($ticket->respuesta)
                    <div class="mt-4 bg-sage-50 dark:bg-sage-900 border border-sage-200 dark:border-sage-700 rounded-2xl p-4">
                        <div class="flex items-center gap-2 mb-1.5">
                            <svg class="w-4 h-4 text-sage-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-sage-600">Respuesta del equipo{{ $ticket->respondido_at ? ' · ' . $ticket->respondido_at->format('d/m/Y') : '' }}</p>
                        </div>
                        <p class="text-sm text-sage-900 dark:text-cream-100 whitespace-pre-line">{{ $ticket->respuesta }}</p>
                    </div>
                @else
                    <p class="mt-4 text-xs text-ink-400 dark:text-sage-400 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Aún no hay respuesta. Te avisaremos por notificación cuando el equipo responda.
                    </p>
                @endif
            </div>
        @empty
            <div class="surface p-16 text-center">
                <div class="w-16 h-16 bg-cream-100 dark:bg-sage-800 rounded-3xl flex items-center justify-center text-3xl mx-auto mb-4">💬</div>
                <h3 class="font-serif text-xl text-sage-900 dark:text-cream-100 mb-1">No has enviado reportes</h3>
                <p class="text-sm text-ink-500 dark:text-sage-300 mb-6">Si tienes un problema (con una cita, el Meet o tu cuenta), repórtalo y te ayudaremos.</p>
                <button x-data @click="$dispatch('open-support')" class="btn-primary">Crear un reporte</button>
            </div>
        @endforelse
    </div>
</div>
@endsection
