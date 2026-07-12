@extends('components.layouts.admin')

@php $active = 'finanzas'; @endphp

@section('content')
<div class="max-w-screen-2xl mx-auto pb-16">
    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="font-serif text-3xl text-sage-900 dark:text-cream-100">Finanzas</h1>
            <p class="text-sm text-ink-500 dark:text-sage-300 mt-1">Ingresos, actividad y retención — {{ $stats['period_label'] }}.</p>
        </div>
        @include('admin.partials.period-filter', ['route' => 'admin.reportes.finanzas', 'period' => $period])
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- Ingresos brutos -->
        <div class="surface p-5">
            <div class="flex justify-between items-start mb-3">
                <p class="text-sm font-semibold text-ink-500">Ingresos brutos</p>
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center bg-sage-100 text-sage-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <h3 class="font-serif text-2xl text-sage-900 mb-1.5">S/ {{ number_format($stats['revenue'], 0) }}</h3>
            @php $g = $stats['revenue_growth']; @endphp
            <p class="text-xs font-medium {{ $g >= 0 ? 'text-sage-600' : 'text-sand-700' }}">
                {{ $g >= 0 ? '▲' : '▼' }} {{ number_format(abs($g), 1) }}% vs. período anterior
            </p>
        </div>

        <!-- Sesiones realizadas (Lupa) -->
        <button type="button" @click="$dispatch('open-drill', { metric: 'sessions', title: 'Sesiones realizadas' })" class="surface p-5 text-left hover:border-sage-400 transition-all group">
            <div class="flex justify-between items-start mb-3">
                <p class="text-sm font-semibold text-ink-500">Sesiones realizadas</p>
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center bg-sage-100 text-sage-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
            </div>
            <h3 class="font-serif text-2xl text-sage-900 mb-1.5">{{ number_format($stats['sessions']) }}</h3>
            <p class="text-xs font-semibold text-sage-600 group-hover:underline">Ver detalle →</p>
        </button>

        <!-- Pacientes activos (Lupa) -->
        <button type="button" @click="$dispatch('open-drill', { metric: 'active_patients', title: 'Pacientes activos (30 días)' })" class="surface p-5 text-left hover:border-sage-400 transition-all group">
            <div class="flex justify-between items-start mb-3">
                <p class="text-sm font-semibold text-ink-500">Pacientes activos</p>
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center bg-sand-100 text-sand-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z"/></svg>
                </div>
            </div>
            <h3 class="font-serif text-2xl text-sage-900 mb-1.5">{{ number_format($stats['active_patients']) }}</h3>
            <p class="text-xs font-medium text-ink-500">Con sesión en últimos 30 días</p>
        </button>

        <!-- Tasa de retención -->
        <div class="surface p-5">
            <div class="flex justify-between items-start mb-3">
                <p class="text-sm font-semibold text-ink-500">Tasa de retención</p>
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center bg-sage-100 text-sage-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
            </div>
            <h3 class="font-serif text-2xl text-sage-900 mb-1.5">{{ number_format($stats['retention_rate'], 1) }}%</h3>
            <p class="text-xs font-medium text-ink-500">{{ $stats['recurrentes'] }} de {{ $stats['con_primera'] }} pacientes vuelven</p>
        </div>
    </div>

    <!-- Ingreso por modalidad -->
    <div class="flex flex-col lg:flex-row gap-6">
        <div class="w-full lg:w-1/2 surface p-6">
            <h3 class="font-serif text-xl text-sage-900 mb-1">Ingreso por modalidad</h3>
            <p class="text-sm text-ink-500 mb-6">Cuánto genera lo virtual vs. lo presencial.</p>
            <div class="space-y-5">
                @php $totalModalRev = max(1, $stats['modalidades']->sum('revenue')); @endphp
                @foreach($stats['modalidades'] as $i => $mod)
                    @php $pct = ($mod['revenue'] / $totalModalRev) * 100; @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-sm font-bold text-sage-900">{{ $mod['nombre'] }}</span>
                            <span class="text-sm font-bold text-sage-900">S/ {{ number_format($mod['revenue'], 0) }} <span class="text-xs font-medium text-ink-500">· {{ $mod['count'] }} citas</span></span>
                        </div>
                        <div class="w-full h-3 bg-cream-200 rounded-full overflow-hidden">
                            <div class="h-full rounded-full" style="width: {{ $pct }}%; background-color: {{ ['#3480ae', '#c9a98c', '#83bbdd', '#d9be9c'][$i % 4] }};"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="w-full lg:w-1/2 surface p-6 flex flex-col">
            <h3 class="font-serif text-xl text-sage-900 mb-4">Distribución de ingresos</h3>
            <div class="w-full h-[240px] relative flex items-center justify-center">
                <canvas id="modalidadChart"></canvas>
            </div>
        </div>
    </div>
</div>

@include('admin.partials.drill-modal', ['period' => $period])
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('modalidadChart');
        if (ctx) {
            new Chart(ctx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($stats['modalidades']->pluck('nombre')) !!},
                    datasets: [{ data: {!! json_encode($stats['modalidades']->pluck('revenue')) !!}, backgroundColor: ['#3480ae', '#c9a98c', '#83bbdd', '#d9be9c'], borderWidth: 0, hoverOffset: 4 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { position: 'bottom', labels: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 12 }, color: '#414e58', padding: 16 } }, tooltip: { backgroundColor: '#213b54', padding: 10, callbacks: { label: (c) => ' S/ ' + c.parsed.toLocaleString() } } } }
            });
        }
    });
</script>
@endpush
