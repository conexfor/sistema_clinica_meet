@extends('components.layouts.receptionist')

@php $active = 'dashboard'; @endphp

@section('content')

        <!-- Encabezado -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="font-serif text-3xl text-sage-900 dark:text-cream-100">Gestión de citas</h1>
                <p class="text-sm text-ink-500 dark:text-sage-300 mt-1">Control del flujo de pacientes, agendamiento y reportes de atención.</p>
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto pb-2 md:pb-0">
                <div class="flex items-center bg-white dark:bg-sage-900 border border-cream-300 dark:border-sage-700 rounded-2xl px-4 py-2.5 shadow-soft">
                    <span class="w-2 h-2 rounded-full bg-sage-500 animate-pulse mr-2"></span>
                    <span class="text-[13px] font-bold text-ink-700 dark:text-sage-200">Hoy: {{ now()->format('d M, Y') }}</span>
                </div>
                @if(auth()->user()->hasAction('book_appointments'))
                <a href="{{ route('receptionist.booking.create') }}" class="btn-primary !py-2.5 text-sm whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Nueva reserva
                </a>
                @endif
            </div>
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-5 mb-8">
            @php
                $kpis = [
                    ['t' => 'Agendadas hoy', 'v' => $stats['kpis']['agendadas_hoy'] ?? 0, 'sub' => 'Nuevos ingresos hoy', 'c' => 'sage', 'i' => 'M12 6v6m0 0v6m0-6h6m-6 0H6'],
                    ['t' => 'Realizadas (mes)', 'v' => $stats['kpis']['realizadas_mes'] ?? 0, 'sub' => 'Atenciones completadas', 'c' => 'sage', 'i' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['t' => 'Total citas (mes)', 'v' => $stats['kpis']['total_citas_mes'] ?? 0, 'sub' => 'Agendadas este período', 'c' => 'sand', 'i' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                    ['t' => 'Especialistas disponibles', 'v' => $stats['kpis']['especialistas_disp'] ?? 0, 'sub' => 'Con espacios hoy', 'c' => 'sand', 'i' => 'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['t' => 'Canceladas (mes)', 'v' => $stats['kpis']['canceladas_mes'] ?? 0, 'sub' => 'Bajas registradas', 'c' => 'sand', 'i' => 'M6 18L18 6M6 6l12 12'],
                    ['t' => 'Cobros (mes)', 'v' => 'S/ ' . number_format($stats['kpis']['cobros_mes'] ?? 0, 2), 'sub' => 'Ingresos cobrados', 'c' => 'sage', 'i' => 'M12 8v8m0 0v8m0-8h8m-8 0H4'],
                ];
            @endphp
            @foreach ($kpis as $k)
                <div class="surface p-5">
                    <div class="flex justify-between items-start mb-3">
                        <p class="text-sm font-semibold text-ink-500 dark:text-sage-300">{{ $k['t'] }}</p>
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 {{ $k['c'] === 'sand' ? 'bg-sand-100 text-sand-600' : 'bg-sage-100 text-sage-600' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $k['i'] }}"/></svg>
                        </div>
                    </div>
                    <h3 class="font-serif text-3xl text-sage-900 dark:text-cream-100 mb-1.5">{{ $k['v'] }}</h3>
                    <p class="text-xs font-medium text-ink-500 dark:text-sage-400">{{ $k['sub'] }}</p>
                </div>
            @endforeach
        </div>

        <!-- Filtros rápidos -->
        @php
            $is_hoy = request('fecha_desde') == now()->toDateString();
            $is_disp = request('disponibles_hoy') == '1';
            $is_all = !$is_hoy && !$is_disp;
            $pillOn = 'bg-sage-600 text-white shadow-sm';
            $pillOff = 'bg-white dark:bg-sage-900 border border-cream-300 dark:border-sage-700 text-ink-600 dark:text-sage-300 hover:text-sage-700 hover:border-sage-300';
        @endphp
        <div class="flex items-center gap-2 mb-6 overflow-x-auto pb-2">
            <a href="{{ route('receptionist.dashboard') }}" class="px-5 py-2.5 rounded-full text-sm font-semibold transition-all whitespace-nowrap {{ $is_all ? $pillOn : $pillOff }}">Todas las citas</a>
            <a href="{{ route('receptionist.dashboard', ['fecha_desde' => now()->toDateString(), 'fecha_hasta' => now()->toDateString()]) }}" class="px-5 py-2.5 rounded-full text-sm font-semibold transition-all whitespace-nowrap {{ $is_hoy ? $pillOn : $pillOff }}">Activas hoy</a>
            <a href="{{ route('receptionist.dashboard', ['disponibles_hoy' => 1]) }}" class="px-5 py-2.5 rounded-full text-sm font-semibold transition-all whitespace-nowrap {{ $is_disp ? $pillOn : $pillOff }}">Especialistas disponibles</a>
        </div>

        <!-- Gráfico -->
        <div class="surface p-6 flex flex-col mb-8">
            <h3 class="font-serif text-xl text-sage-900 dark:text-cream-100 mb-6">Flujo de citas diarias (esta semana)</h3>
            <div class="w-full flex-1 min-h-[300px] relative"><canvas id="horizontalBarChart"></canvas></div>
        </div>

        <!-- Tabla -->
        <div class="surface overflow-hidden">
            <div class="p-6 border-b border-cream-300 dark:border-sage-700 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h3 class="font-serif text-xl text-sage-900 dark:text-cream-100">Agenda mensual</h3>
                <form method="GET" action="{{ route('receptionist.dashboard') }}" class="flex items-center gap-2 text-sm">
                    <select name="estado" class="field !py-2 !w-auto">
                        <option value="">Todos los estados</option>
                        @foreach($estados as $key => $est)
                            <option value="{{ $key }}" {{ request('estado') == $key ? 'selected' : '' }}>{{ $est }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-primary !py-2 text-sm">Filtrar</button>
                    <a href="{{ route('receptionist.dashboard') }}" class="text-ink-500 hover:text-ink-700 font-medium px-2">Limpiar</a>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-cream-100 dark:bg-sage-800">
                            <th class="px-6 py-4 text-xs font-semibold text-ink-500 dark:text-sage-300 uppercase tracking-wider whitespace-nowrap">Paciente</th>
                            <th class="px-6 py-4 text-xs font-semibold text-ink-500 dark:text-sage-300 uppercase tracking-wider whitespace-nowrap">Especialista</th>
                            <th class="px-6 py-4 text-xs font-semibold text-ink-500 dark:text-sage-300 uppercase tracking-wider whitespace-nowrap">Fecha / hora</th>
                            <th class="px-6 py-4 text-xs font-semibold text-ink-500 dark:text-sage-300 uppercase tracking-wider whitespace-nowrap text-right">Total</th>
                            <th class="px-6 py-4 text-xs font-semibold text-ink-500 dark:text-sage-300 uppercase tracking-wider whitespace-nowrap text-center">Estado</th>
                            <th class="px-6 py-4 text-xs font-semibold text-ink-500 dark:text-sage-300 uppercase tracking-wider whitespace-nowrap text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-cream-200 dark:divide-sage-800">
                        @forelse($reportes as $cita)
                        <tr class="hover:bg-cream-100/60 dark:hover:bg-sage-800/60 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-sage-100 text-sage-700 flex items-center justify-center font-bold text-sm">
                                        {{ strtoupper(substr($cita->paciente->nombre ?? 'P', 0, 1)) }}
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-sage-900 dark:text-cream-100">{{ $cita->paciente->nombre ?? 'N/A' }}</h4>
                                        <p class="text-[11px] text-ink-500 dark:text-sage-400 font-medium">{{ $cita->paciente->email ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4"><span class="text-sm text-ink-700 dark:text-sage-200 font-semibold block">{{ $cita->especialista->usuario->nombre ?? 'N/A' }}</span></td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-sage-900 dark:text-cream-100 block">{{ \Carbon\Carbon::parse($cita->disponibilidad->fecha)->format('d M, Y') }}</span>
                                <span class="text-xs text-ink-500 dark:text-sage-400 font-medium">{{ \Carbon\Carbon::parse($cita->disponibilidad->hora_inicio)->format('h:i A') }}</span>
                            </td>
                            <td class="px-6 py-4 text-right"><span class="text-sm font-bold text-sage-700 dark:text-sage-300">S/ {{ number_format($cita->total, 2) }}</span></td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $est = strtolower($cita->estado);
                                    $bg = 'bg-cream-200 text-ink-600';
                                    if(in_array($est, ['reservada'])) $bg = 'bg-sage-100 text-sage-700';
                                    if(in_array($est, ['realizada', 'completada'])) $bg = 'bg-sage-600 text-white';
                                    if(in_array($est, ['cancelada'])) $bg = 'bg-sand-200 text-sand-800';
                                @endphp
                                <span class="inline-flex py-1 px-3 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $bg }}">{{ $estados[$cita->estado] ?? $cita->estado }}</span>
                                <x-cancellation-note :cita="$cita" class="mt-1.5 text-center" />
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if(in_array($cita->estado, [\App\Models\Cita::STATUS_RESERVADA, \App\Models\Cita::STATUS_RESCHEDULED]))
                                    <a href="{{ route('receptionist.booking.reschedule', $cita->id) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[11px] font-bold text-sage-700 dark:text-sage-200 bg-sage-50 dark:bg-sage-800 border border-sage-200 dark:border-sage-700 hover:bg-sage-100 dark:hover:bg-sage-700 transition-colors whitespace-nowrap">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        Reprogramar
                                    </a>
                                @else
                                    <span class="text-[11px] text-ink-400 dark:text-sage-500 font-medium">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-sm font-medium text-ink-500 dark:text-sage-400">No se encontraron reservas con los filtros aplicados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($reportes->hasPages())
            <div class="px-6 py-4 border-t border-cream-300 dark:border-sage-700 bg-cream-100 dark:bg-sage-800">
                @php
                    $p = $reportes;
                    $last = $p->lastPage();
                    $cur = $p->currentPage();
                    // Ventana de máximo 7 números centrada en la página actual.
                    $start = max(1, $cur - 3);
                    $end = min($last, $start + 6);
                    $start = max(1, $end - 6);
                @endphp
                <nav class="flex items-center justify-between gap-4 flex-wrap" role="navigation" aria-label="Paginación de la agenda">
                    <p class="text-xs font-medium text-ink-500 dark:text-sage-400">
                        Mostrando <span class="font-bold text-ink-700 dark:text-sage-200">{{ $p->firstItem() }}–{{ $p->lastItem() }}</span> de {{ $p->total() }} citas
                    </p>
                    <div class="flex items-center gap-1.5">
                        {{-- Números de página (máximo 7) --}}
                        @for($i = $start; $i <= $end; $i++)
                            @if($i == $cur)
                                <span aria-current="page" class="w-9 h-9 flex items-center justify-center rounded-xl text-sm font-bold bg-sage-600 text-white shadow-sm">{{ $i }}</span>
                            @else
                                <a href="{{ $p->url($i) }}" class="w-9 h-9 flex items-center justify-center rounded-xl text-sm font-semibold text-ink-600 dark:text-sage-300 bg-white dark:bg-sage-900 border border-cream-300 dark:border-sage-700 hover:border-sage-400 hover:text-sage-700 transition-colors">{{ $i }}</a>
                            @endif
                        @endfor

                        {{-- Flechas de paginación al final --}}
                        <div class="flex items-center gap-1.5 ml-2">
                            @if($p->onFirstPage())
                                <span aria-disabled="true" class="w-9 h-9 flex items-center justify-center rounded-xl text-cream-400 dark:text-sage-700 bg-white/50 dark:bg-sage-900/40 border border-cream-200 dark:border-sage-800 cursor-not-allowed">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </span>
                            @else
                                <a href="{{ $p->previousPageUrl() }}" rel="prev" aria-label="Página anterior" class="w-9 h-9 flex items-center justify-center rounded-xl text-ink-600 dark:text-sage-300 bg-white dark:bg-sage-900 border border-cream-300 dark:border-sage-700 hover:bg-sage-500 hover:text-white hover:border-sage-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </a>
                            @endif
                            @if($p->hasMorePages())
                                <a href="{{ $p->nextPageUrl() }}" rel="next" aria-label="Página siguiente" class="w-9 h-9 flex items-center justify-center rounded-xl text-ink-600 dark:text-sage-300 bg-white dark:bg-sage-900 border border-cream-300 dark:border-sage-700 hover:bg-sage-500 hover:text-white hover:border-sage-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @else
                                <span aria-disabled="true" class="w-9 h-9 flex items-center justify-center rounded-xl text-cream-400 dark:text-sage-700 bg-white/50 dark:bg-sage-900/40 border border-cream-200 dark:border-sage-800 cursor-not-allowed">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            @endif
                        </div>
                    </div>
                </nav>
            </div>
            @endif
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
                        labels: {!! json_encode($stats['charts']['flujo_labels'] ?? []) !!},
                        datasets: [{ data: {!! json_encode($stats['charts']['flujo_values'] ?? []) !!}, backgroundColor: '#3480ae', borderRadius: 6, barPercentage: 0.6, categoryPercentage: 0.8 }]
                    },
                    options: {
                        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { backgroundColor: '#213b54', padding: 12, displayColors: false, callbacks: { label: (c) => 'Reservas: ' + c.parsed.x } } },
                        scales: {
                            x: { grid: { color: '#e3ded2', borderDash: [4, 4], drawBorder: false }, ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 }, color: '#64727d' } },
                            y: { grid: { display: false, drawBorder: false }, ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 12, weight: '500' }, color: '#414e58' } }
                        }
                    }
                });
            }
        });
    </script>
@endpush
