@extends('components.layouts.admin')

@php $active = 'control'; @endphp

@section('content')
<div class="max-w-screen-2xl mx-auto pb-16">
    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="font-serif text-3xl text-sage-900 dark:text-cream-100">Panel de control</h1>
            <p class="text-sm text-ink-500 dark:text-sage-300 mt-1">Alertas y eficiencia operativa — {{ $stats['period_label'] }}.</p>
        </div>
        @include('admin.partials.period-filter', ['route' => 'admin.reportes.control', 'period' => $period])
    </div>

    <!-- Alerta de cancelaciones masivas -->
    @if($stats['cancel_spike'])
        <button type="button" @click="$dispatch('open-drill', { metric: 'cancellations', title: 'Cancelaciones (últimos 7 días)' })"
                class="w-full surface p-6 mb-8 border-l-4 border-sand-500 text-left hover:border-sand-600 transition-all group">
            <div class="flex items-start gap-3">
                <svg class="w-7 h-7 text-sand-600 shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-3l-6.93-12a2 2 0 00-3.48 0l-6.93 12a2 2 0 001.74 3z"/></svg>
                <div>
                    <h3 class="font-serif text-lg text-sage-900">Pico inusual de cancelaciones</h3>
                    <p class="text-sm text-ink-600 mt-0.5">
                        {{ $stats['cancel_last7'] }} cancelaciones en los últimos 7 días vs. un promedio de {{ $stats['cancel_prev_avg'] }}/semana.
                        <span class="font-bold text-sage-600 group-hover:underline">Ver detalle →</span>
                    </p>
                </div>
            </div>
        </button>
    @endif

    <!-- KPIs operativos -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        <!-- Tasa de ausentismo (Lupa) -->
        <button type="button" @click="$dispatch('open-drill', { metric: 'no_shows', title: 'Ausencias / cancelaciones del paciente' })" class="surface p-5 text-left hover:border-sage-400 transition-all group">
            <div class="flex justify-between items-start mb-3">
                <p class="text-sm font-semibold text-ink-500">Tasa de ausentismo</p>
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center bg-sand-100 text-sand-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </div>
            </div>
            <h3 class="font-serif text-2xl text-sage-900 mb-1.5">{{ number_format($stats['no_show_rate'], 1) }}%</h3>
            <p class="text-xs font-medium text-ink-500">{{ $stats['no_show_count'] }} de {{ $stats['total_citas'] }} citas · <span class="text-sage-600 group-hover:underline">ver</span></p>
        </button>

        <!-- Cancelaciones últimos 7 días -->
        <div class="surface p-5">
            <div class="flex justify-between items-start mb-3">
                <p class="text-sm font-semibold text-ink-500">Cancelaciones (7 días)</p>
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center bg-sage-100 text-sage-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
            </div>
            <h3 class="font-serif text-2xl text-sage-900 mb-1.5">{{ $stats['cancel_last7'] }}</h3>
            <p class="text-xs font-medium text-ink-500">Promedio previo: {{ $stats['cancel_prev_avg'] }}/semana</p>
        </div>

        <!-- Total citas periodo -->
        <div class="surface p-5">
            <div class="flex justify-between items-start mb-3">
                <p class="text-sm font-semibold text-ink-500">Citas del período</p>
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center bg-sage-100 text-sage-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <h3 class="font-serif text-2xl text-sage-900 mb-1.5">{{ number_format($stats['total_citas']) }}</h3>
            <p class="text-xs font-medium text-ink-500">Total agendadas en el rango</p>
        </div>
    </div>

    <p class="text-xs text-ink-400 mb-8">La tasa de ausentismo cuenta las citas marcadas como "No asistió" por el especialista sobre el total de citas del período.</p>

    <!-- Placeholders Fase 2 -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="surface p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl {{ $stats['soporte_abiertos'] > 0 ? 'bg-sand-100 text-sand-600' : 'bg-sage-100 text-sage-600' }} flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-serif text-lg text-sage-900">Solicitudes de soporte</h3>
                        <p class="text-xs text-ink-500">{{ $stats['soporte_abiertos'] }} sin resolver</p>
                    </div>
                </div>
                <a href="{{ route('admin.soporte.index') }}" class="text-xs font-bold text-sage-600 hover:underline">Ver bandeja →</a>
            </div>
            @if($stats['soporte_recent']->isEmpty())
                <p class="text-sm text-ink-500 py-4 text-center">Sin incidencias reportadas aún.</p>
            @else
                <ul class="divide-y divide-cream-200">
                    @foreach($stats['soporte_recent'] as $t)
                        <li class="py-2.5 flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-sage-900 truncate">{{ $t->asunto }}</p>
                                <p class="text-xs text-ink-400">{{ $t->usuario->nombre ?? 'Usuario' }} · {{ $t->categoria_label }}</p>
                            </div>
                            @php $b = ['abierto' => 'chip-sand', 'en_proceso' => 'chip', 'resuelto' => 'chip-sage'][$t->estado] ?? 'chip'; @endphp
                            <span class="{{ $b }} shrink-0">{{ ucfirst(str_replace('_',' ',$t->estado)) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="surface p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-sage-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-sage-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="font-serif text-lg text-sage-900">Log de auditoría</h3>
                </div>
                <a href="{{ route('admin.reportes.auditoria') }}" class="text-xs font-bold text-sage-600 hover:underline">Ver todo →</a>
            </div>
            @if($stats['audit_recent']->isEmpty())
                <p class="text-sm text-ink-500 py-4 text-center">Sin eventos registrados aún.</p>
            @else
                <ul class="divide-y divide-cream-200">
                    @foreach($stats['audit_recent'] as $ev)
                        <li class="py-2.5 flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm text-ink-700 truncate">{{ $ev->descripcion }}</p>
                                <p class="text-xs text-ink-400">{{ $ev->usuario->nombre ?? 'Sistema' }}</p>
                            </div>
                            <span class="text-xs text-ink-400 whitespace-nowrap">{{ $ev->created_at->diffForHumans(null, true) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>

@include('admin.partials.drill-modal', ['period' => $period])
@endsection
