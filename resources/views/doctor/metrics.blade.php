@extends('components.layouts.doctor')

@php $active = 'metrics'; @endphp

@section('content')
<div class="max-w-screen-xl mx-auto pb-12">

    <div class="mb-8">
        <h1 class="font-serif text-3xl text-sage-900">Métricas</h1>
        <p class="text-sm text-ink-500 mt-1">Tu actividad clínica del mes en curso, con datos reales.</p>
    </div>

    <!-- KPIs (sin ingresos ni valoración) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        @php
            $kpis = [
                ['t' => 'Citas hoy', 'v' => $stats['kpis']['citas_hoy'], 'sub' => 'Agenda diaria', 'i' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['t' => 'Citas del mes', 'v' => $stats['kpis']['citas_mes'], 'sub' => 'Programadas', 'i' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2'],
                ['t' => 'Completadas', 'v' => $stats['kpis']['completadas_mes'], 'sub' => 'Este mes', 'i' => 'M5 13l4 4L19 7'],
                ['t' => 'Pacientes', 'v' => $stats['kpis']['pacientes_tot'], 'sub' => 'Históricos', 'i' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857'],
            ];
        @endphp
        @foreach ($kpis as $k)
            <div class="surface p-5">
                <div class="w-10 h-10 rounded-2xl bg-sage-100 text-sage-600 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $k['i'] }}"/></svg>
                </div>
                <h3 class="font-serif text-3xl text-sage-900 mb-1">{{ $k['v'] }}</h3>
                <p class="text-xs font-medium text-ink-500">{{ $k['t'] }} · {{ $k['sub'] }}</p>
            </div>
        @endforeach
    </div>

    <!-- Gráficos -->
    <div class="flex flex-col lg:flex-row gap-6">
        <div class="w-full lg:w-2/3 surface p-6 flex flex-col">
            <h3 class="font-serif text-xl text-sage-900 mb-6">Productividad de la semana (mes actual)</h3>
            <div class="w-full flex-1 min-h-[350px] relative">
                <canvas id="horizontalBarChart"></canvas>
            </div>
        </div>

        <div class="w-full lg:w-1/3 surface p-6 flex flex-col">
            <h3 class="font-serif text-xl text-sage-900 mb-4">Tipos de paciente</h3>
            <div class="w-full h-[220px] relative flex items-center justify-center mb-6 mt-2">
                <canvas id="donutChart"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="font-serif text-3xl text-sage-900">{{ array_sum($stats['charts']['type_values']) }}</span>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-ink-500">Total</span>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-y-3 mt-auto">
                @php $donutColors = ['#3480ae', '#c9a98c', '#83bbdd']; @endphp
                @foreach($stats['charts']['type_labels'] as $index => $label)
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $donutColors[$index % count($donutColors)] }};"></span>
                    <span class="text-[11px] font-semibold text-ink-600">{{ $label }}</span>
                    <span class="text-[11px] font-bold text-sage-900 ml-auto">{{ $stats['charts']['type_values'][$index] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const barCtx = document.getElementById('horizontalBarChart');
        if (barCtx) {
            new Chart(barCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($stats['charts']['prod_labels']) !!},
                    datasets: [{
                        data: {!! json_encode($stats['charts']['prod_values']) !!},
                        backgroundColor: '#3480ae', borderRadius: 6, barPercentage: 0.6, categoryPercentage: 0.8
                    }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { backgroundColor: '#213b54', padding: 12, displayColors: false, callbacks: { label: (c) => 'Citas: ' + c.parsed.x } } },
                    scales: {
                        x: { grid: { color: '#e3ded2', borderDash: [4, 4], drawBorder: false }, ticks: { precision: 0, font: { size: 11 }, color: '#64727d' } },
                        y: { grid: { display: false, drawBorder: false }, ticks: { font: { size: 12, weight: '500' }, color: '#414e58' } }
                    }
                }
            });
        }

        const donutCtx = document.getElementById('donutChart');
        if (donutCtx) {
            new Chart(donutCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($stats['charts']['type_labels']) !!},
                    datasets: [{
                        data: {!! json_encode($stats['charts']['type_values']) !!},
                        backgroundColor: ['#3480ae', '#c9a98c', '#83bbdd'], borderWidth: 0, hoverOffset: 4
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { display: false }, tooltip: { backgroundColor: '#213b54', padding: 10 } } }
            });
        }
    });
</script>
@endpush
