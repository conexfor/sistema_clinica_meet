@extends('components.layouts.admin')

@php $active = 'dashboard'; @endphp

@section('content')

        <!-- Encabezado -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="font-serif text-3xl text-sage-900 dark:text-cream-100">Reportes y analíticas</h1>
                <p class="text-sm text-ink-500 dark:text-sage-300 mt-1">Una mirada integral al rendimiento y bienestar de la clínica.</p>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto pb-2 md:pb-0 flex-wrap">
                <div x-data="{
                        open: false,
                        selected: '{{ [
                            'today' => 'Hoy', 'week' => 'Esta semana', 'month' => 'Este mes',
                            'last_month' => 'Mes anterior', '6_months' => 'Últimos 6 meses', 'year' => 'Año actual'
                        ][$stats['current_period'] ?? '6_months'] ?? 'Últimos 6 meses' }}',
                        options: [
                            { label: 'Hoy', value: 'today' }, { label: 'Esta semana', value: 'week' },
                            { label: 'Este mes', value: 'month' }, { label: 'Mes anterior', value: 'last_month' },
                            { label: 'Últimos 6 meses', value: '6_months' }, { label: 'Año actual', value: 'year' }
                        ],
                        select(o) { this.selected = o.label; this.open = false; window.location.href = '{{ route('admin.dashboard') }}?period=' + o.value; }
                    }" class="relative">
                    <button @click="open = !open" class="flex items-center gap-2 px-5 py-2.5 bg-white border border-cream-300 rounded-2xl text-sm font-semibold text-ink-700 hover:border-sage-400 transition-all shadow-soft">
                        <svg class="w-4 h-4 text-sage-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span x-text="selected"></span>
                        <svg class="w-4 h-4 text-ink-500 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-cloak @click.away="open = false" x-transition class="absolute left-0 mt-2 w-48 surface !rounded-2xl z-[60] p-2">
                        <template x-for="option in options" :key="option.value">
                            <button @click="select(option)" class="w-full text-left px-4 py-2.5 text-[13px] font-semibold text-ink-600 hover:bg-sage-50 hover:text-sage-700 rounded-xl transition-colors" :class="{'bg-sage-50 text-sage-700': selected === option.label}" x-text="option.label"></button>
                        </template>
                    </div>
                </div>

                <button onclick="window.print()" class="btn-outline !py-2.5 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Exportar PDF
                </button>
            </div>
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 mb-8">
            @php
                $kpis = [
                    ['t' => 'Ingresos totales', 'v' => 'S/ ' . number_format($stats['kpis']['total_revenue'] ?? 0, 0), 'sub' => ($stats['kpis']['revenue_growth'] ?? '0.0') . '% vs. mes anterior', 'c' => 'sage', 'i' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['t' => 'Margen neto', 'v' => 'S/ ' . number_format($stats['kpis']['net_profit'] ?? 0, 0), 'sub' => ($stats['kpis']['profit_margin'] ?? '0.0') . '% margen total', 'c' => 'sand', 'i' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                    ['t' => 'Inasistencias', 'v' => ($stats['kpis']['no_show_rate'] ?? '0.0') . '%', 'sub' => ($stats['kpis']['no_show_improve'] ?? '0.0') . '% mejora', 'c' => 'sand', 'i' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['t' => 'Sesiones pendientes', 'v' => number_format($stats['kpis']['pending_sessions'] ?? 0), 'sub' => 'Próximas a realizarse', 'c' => 'sage', 'i' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['t' => 'Calificación promedio', 'v' => ($stats['kpis']['avg_rating'] ?? '0.0') . ' / 5', 'sub' => 'Nivel de satisfacción', 'c' => 'sage', 'i' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.382-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
                ];
            @endphp
            @foreach ($kpis as $k)
                <div class="surface p-5">
                    <div class="flex justify-between items-start mb-3">
                        <p class="text-sm font-semibold text-ink-500">{{ $k['t'] }}</p>
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 {{ $k['c'] === 'sand' ? 'bg-sand-100 text-sand-600' : 'bg-sage-100 text-sage-600' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $k['i'] }}"/></svg>
                        </div>
                    </div>
                    <h3 class="font-serif text-2xl text-sage-900 mb-1.5">{{ $k['v'] }}</h3>
                    <p class="text-xs font-medium text-ink-500">{{ $k['sub'] }}</p>
                </div>
            @endforeach
        </div>

        <!-- Gráficos -->
        <div class="flex flex-col lg:flex-row gap-6 mb-8">
            <div class="w-full lg:w-2/3 surface p-6 flex flex-col">
                <h3 class="font-serif text-xl text-sage-900 mb-6">Ingresos por servicio</h3>
                <div class="w-full flex-1 min-h-[350px] relative"><canvas id="horizontalBarChart"></canvas></div>
            </div>
            <div class="w-full lg:w-1/3 surface p-6 flex flex-col">
                <h3 class="font-serif text-xl text-sage-900 mb-4">Distribución de volumen</h3>
                <div class="w-full h-[220px] relative flex items-center justify-center mb-6 mt-2">
                    <canvas id="donutChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="font-serif text-3xl text-sage-900">{{ array_sum($stats['charts']['volume_values'] ?? [0]) }}</span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-ink-500">Total citas</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3 mt-auto">
                    @php $donutColors = ['#3480ae', '#c9a98c', '#83bbdd', '#d9be9c', '#529bc8', '#e7d5bf']; @endphp
                    @foreach(($stats['charts']['volume_labels'] ?? []) as $index => $label)
                    <div class="flex items-center gap-2 min-w-0 py-0.5">
                        <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $donutColors[$index % count($donutColors)] }};"></span>
                        <span class="flex-1 min-w-0 text-[11px] font-semibold text-ink-600 truncate leading-relaxed" title="{{ $label }}">{{ $label }}</span>
                        <span class="shrink-0 text-[11px] font-bold text-sage-900 text-right leading-none pr-0.5">{{ $stats['charts']['volume_values'][$index] ?? 0 }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="surface overflow-hidden">
            <div class="p-6 border-b border-cream-300 flex items-center justify-between">
                <h3 class="font-serif text-xl text-sage-900">Rendimiento por servicio</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-cream-100">
                            <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider whitespace-nowrap">Servicio</th>
                            <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider whitespace-nowrap">Conteo</th>
                            <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider whitespace-nowrap">Ingresos</th>
                            <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider whitespace-nowrap">Ticket promedio</th>
                            <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider whitespace-nowrap">% del total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-cream-200">
                        @php $totalRev = array_sum($stats['charts']['revenue_values'] ?? [1]); @endphp
                        @foreach($stats['charts']['revenue_labels'] ?? [] as $i => $label)
                        <tr class="hover:bg-cream-100/60 transition-colors">
                            <td class="px-6 py-4"><span class="text-sm font-semibold text-sage-900">{{ $label }}</span></td>
                            <td class="px-6 py-4"><span class="text-sm text-ink-600 font-medium">{{ $stats['charts']['volume_values'][$i] ?? 0 }}</span></td>
                            <td class="px-6 py-4"><span class="text-sm font-bold text-sage-900">S/ {{ number_format($stats['charts']['revenue_values'][$i] ?? 0, 0) }}</span></td>
                            <td class="px-6 py-4">
                                @php $vol = ($stats['charts']['volume_values'][$i] ?? 1); $avg = ($stats['charts']['revenue_values'][$i] ?? 0) / ($vol == 0 ? 1 : $vol); @endphp
                                <span class="text-sm text-ink-600 font-medium">S/ {{ number_format($avg, 0) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @php $val = ($stats['charts']['revenue_values'][$i] ?? 0); $pct = $totalRev > 0 ? ($val / $totalRev) * 100 : 0; @endphp
                                <div class="flex items-center gap-3">
                                    <div class="w-24 h-2 bg-cream-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-sage-500 rounded-full" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-ink-500 min-w-[35px]">{{ number_format($pct, 1) }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const barCtx = document.getElementById('horizontalBarChart');
            if(barCtx) {
                new Chart(barCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($stats['charts']['revenue_labels'] ?? []) !!},
                        datasets: [{ data: {!! json_encode($stats['charts']['revenue_values'] ?? []) !!}, backgroundColor: '#3480ae', borderRadius: 6, barPercentage: 0.6, categoryPercentage: 0.8 }]
                    },
                    options: {
                        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { backgroundColor: '#213b54', padding: 12, displayColors: false, callbacks: { label: (c) => 'Ingresos: S/ ' + c.parsed.x.toLocaleString() } } },
                        scales: {
                            x: { grid: { color: '#e3ded2', borderDash: [4, 4], drawBorder: false }, ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 }, color: '#64727d' } },
                            y: { grid: { display: false, drawBorder: false }, ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 12, weight: '500' }, color: '#414e58' } }
                        }
                    }
                });
            }
            const donutCtx = document.getElementById('donutChart');
            if(donutCtx) {
                new Chart(donutCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($stats['charts']['volume_labels'] ?? []) !!},
                        datasets: [{ data: {!! json_encode($stats['charts']['volume_values'] ?? []) !!}, backgroundColor: ['#3480ae', '#c9a98c', '#83bbdd', '#d9be9c', '#529bc8', '#e7d5bf'], borderWidth: 0, hoverOffset: 4 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { display: false }, tooltip: { backgroundColor: '#213b54', padding: 10, bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 13 }, callbacks: { label: (c) => ' ' + c.formattedValue + ' citas' } } } }
                });
            }
        });
    </script>
@endpush
