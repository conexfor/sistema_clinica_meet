@extends('components.layouts.admin')

@php $active = 'ocupacion'; @endphp

@section('content')
<div class="max-w-screen-2xl mx-auto pb-16">
    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="font-serif text-3xl text-sage-900 dark:text-cream-100">Ocupación y demanda</h1>
            <p class="text-sm text-ink-500 dark:text-sage-300 mt-1">Horarios con más demanda y modalidad — {{ $stats['period_label'] }}.</p>
        </div>
        @include('admin.partials.period-filter', ['route' => 'admin.reportes.ocupacion', 'period' => $period])
    </div>

    <!-- Mapa de calor -->
    <div class="surface p-6 mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-2">
            <div>
                <h3 class="font-serif text-xl text-sage-900">Mapa de calor de ocupación</h3>
                <p class="text-sm text-ink-500">{{ $stats['total_slots'] }} horarios reservados en el período.</p>
            </div>
            <div class="flex items-center gap-2 text-xs text-ink-500">
                <span>Menos</span>
                <span class="w-4 h-4 rounded" style="background-color: rgba(52,128,174,0.12)"></span>
                <span class="w-4 h-4 rounded" style="background-color: rgba(52,128,174,0.4)"></span>
                <span class="w-4 h-4 rounded" style="background-color: rgba(52,128,174,0.7)"></span>
                <span class="w-4 h-4 rounded" style="background-color: rgba(52,128,174,1)"></span>
                <span>Más</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <div class="min-w-[640px]">
                <!-- Cabecera de horas -->
                <div class="flex items-center mb-1">
                    <div class="w-12 shrink-0"></div>
                    @foreach($stats['hours'] as $h)
                        <div class="flex-1 text-center text-[10px] font-bold text-ink-400">{{ $h }}h</div>
                    @endforeach
                </div>
                <!-- Filas por día -->
                @foreach($stats['days'] as $dIdx => $dLabel)
                    <div class="flex items-center mb-1">
                        <div class="w-12 shrink-0 text-xs font-bold text-ink-500">{{ $dLabel }}</div>
                        @foreach($stats['hours'] as $hIdx => $h)
                            @php
                                $count = $stats['matrix'][$dIdx][$hIdx] ?? 0;
                                $alpha = $stats['max'] > 0 && $count > 0 ? max(0.12, $count / $stats['max']) : 0;
                            @endphp
                            <div class="flex-1 px-0.5">
                                <div class="aspect-square rounded-md flex items-center justify-center text-[10px] font-bold {{ $count > 0 && $alpha > 0.55 ? 'text-white' : 'text-ink-500' }}"
                                     style="background-color: {{ $count > 0 ? 'rgba(52,128,174,' . $alpha . ')' : 'rgba(0,0,0,0.03)' }};"
                                     title="{{ $dLabel }} {{ $h }}:00 — {{ $count }} reservas">
                                    {{ $count > 0 ? $count : '' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Presencial vs Virtual -->
    <div class="surface p-6">
        <h3 class="font-serif text-xl text-sage-900 mb-1">Presencial vs. Virtual</h3>
        <p class="text-sm text-ink-500 mb-6">Comparativo de citas por modalidad en el período.</p>
        <div class="space-y-5">
            @php $maxModal = max(1, collect($stats['modalidad_bars'])->max('count')); @endphp
            @foreach($stats['modalidad_bars'] as $i => $bar)
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-sm font-bold text-sage-900">{{ $bar['nombre'] }}</span>
                        <span class="text-sm font-semibold text-ink-600">{{ $bar['count'] }} citas <span class="text-xs text-ink-400">· {{ $bar['pct'] }}%</span></span>
                    </div>
                    <div class="w-full h-5 bg-cream-200 rounded-lg overflow-hidden">
                        <div class="h-full rounded-lg transition-all" style="width: {{ ($bar['count'] / $maxModal) * 100 }}%; background-color: {{ ['#3480ae', '#c9a98c', '#83bbdd', '#d9be9c'][$i % 4] }};"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Lista de espera -->
    <a href="{{ route('admin.lista-espera.index') }}" class="surface surface-hover p-6 mt-8 flex items-center justify-between gap-4 group">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl {{ $stats['waitlist_pendientes'] > 0 ? 'bg-sand-100 text-sand-600' : 'bg-sage-100 text-sage-600' }} flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z"/></svg>
            </div>
            <div>
                <h3 class="font-serif text-lg text-sage-900">Pacientes en lista de espera</h3>
                <p class="text-sm text-ink-500">
                    <span class="font-bold text-sage-900">{{ $stats['waitlist_pendientes'] }}</span> pendientes de {{ $stats['waitlist_total'] }} solicitudes.
                    @if($stats['waitlist_pendientes'] > 0) Faltan cupos/doctores para esta demanda. @endif
                </p>
            </div>
        </div>
        <span class="text-xs font-bold text-sage-600 group-hover:underline whitespace-nowrap">Gestionar →</span>
    </a>
</div>
@endsection
