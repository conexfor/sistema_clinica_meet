@extends('components.layouts.admin')

@php $active = 'desempeno'; @endphp

@section('content')
<div class="max-w-screen-2xl mx-auto pb-16">
    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="font-serif text-3xl text-sage-900 dark:text-cream-100">Desempeño de especialistas</h1>
            <p class="text-sm text-ink-500 dark:text-sage-300 mt-1">Ranking, conversión y actividad — {{ $stats['period_label'] }}.</p>
        </div>
        @include('admin.partials.period-filter', ['route' => 'admin.reportes.desempeno', 'period' => $period])
    </div>

    <!-- Alerta de inactivos -->
    @if(count($stats['inactivos']) > 0)
        <div class="surface p-6 mb-8 border-l-4 border-sand-400">
            <div class="flex items-start gap-3 mb-4">
                <svg class="w-6 h-6 text-sand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-3l-6.93-12a2 2 0 00-3.48 0l-6.93 12a2 2 0 001.74 3z"/></svg>
                <div>
                    <h3 class="font-serif text-lg text-sage-900">Especialistas inactivos</h3>
                    <p class="text-sm text-ink-500">Sin sesiones agendadas en más de {{ $stats['inactive_days'] }} días.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($stats['inactivos'] as $doc)
                    <div class="flex items-center gap-3 bg-sand-50 border border-sand-200 rounded-2xl p-3">
                        <img src="{{ $doc['foto_url'] }}" alt="" class="w-10 h-10 rounded-full object-cover">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-sage-900 truncate">{{ $doc['nombre'] }}</p>
                            <p class="text-xs text-ink-500 truncate">
                                {{ $doc['ultima_sesion'] ? 'Última: ' . $doc['ultima_sesion'] . ' (' . $doc['dias'] . ' días)' : 'Sin sesiones registradas' }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Ranking -->
    <div class="surface overflow-hidden">
        <div class="p-6 border-b border-cream-300">
            <h3 class="font-serif text-xl text-sage-900">Ranking de doctores</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-cream-100">
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">Especialista</th>
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">Sesiones</th>
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">Tasa cancelación</th>
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">Calificación</th>
                        <th class="px-6 py-4 text-xs font-semibold text-ink-500 uppercase tracking-wider">Conversión</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-200">
                    @forelse($stats['ranking'] as $idx => $row)
                        <tr class="hover:bg-cream-100/60 transition-colors">
                            <td class="px-6 py-4"><span class="text-sm font-bold text-ink-400">{{ $idx + 1 }}</span></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $row['foto_url'] }}" alt="" class="w-9 h-9 rounded-full object-cover">
                                    <div>
                                        <p class="text-sm font-bold text-sage-900">{{ $row['nombre'] }}</p>
                                        <p class="text-xs text-ink-500">{{ $row['especialidad'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4"><span class="text-sm font-bold text-sage-900">{{ $row['realizadas'] }}</span> <span class="text-xs text-ink-500">/ {{ $row['total_citas'] }}</span></td>
                            <td class="px-6 py-4">
                                @php $cr = $row['cancel_rate']; @endphp
                                <span class="chip {{ $cr > 25 ? 'chip-sand' : 'chip-sage' }}">{{ number_format($cr, 1) }}%</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($row['reviews'] > 0)
                                    <span class="text-sm font-bold text-sage-900">★ {{ number_format($row['avg_rating'], 1) }}</span>
                                    <span class="text-xs text-ink-500">({{ $row['reviews'] }})</span>
                                @else
                                    <span class="text-xs text-ink-400">Sin reseñas</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-20 h-2 bg-cream-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-sage-500 rounded-full" style="width: {{ min(100, $row['conversion']) }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-ink-500">{{ number_format($row['conversion'], 0) }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-16 text-center text-ink-500 text-sm">No hay especialistas registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <p class="text-xs text-ink-400 mt-3">La tasa de conversión y la calificación son históricas; sesiones y cancelación corresponden al período seleccionado.</p>
</div>
@endsection
