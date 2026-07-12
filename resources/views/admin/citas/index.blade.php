@extends('components.layouts.admin')

@php $active = 'citas'; @endphp

@section('header_title', 'Reporte de Citas')

@section('content')
<div class="max-w-7xl mx-auto pb-10">
    <div class="mb-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-serif text-3xl text-sage-900 dark:text-cream-100">Reporte de citas</h1>
            <p class="text-ink-500 dark:text-sage-300 mt-1">Supervisión de todas las sesiones agendadas en la clínica.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn-outline !rounded-xl text-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Volver al panel
        </a>
    </div>

    <div class="surface overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-cream-100 border-b border-cream-300">
                        <th class="px-8 py-5 text-[10px] font-bold uppercase tracking-widest text-ink-500">Paciente / especialista</th>
                        <th class="px-8 py-5 text-[10px] font-bold uppercase tracking-widest text-ink-500">Fecha y hora</th>
                        <th class="px-8 py-5 text-[10px] font-bold uppercase tracking-widest text-ink-500">Estado</th>
                        <th class="px-8 py-5 text-[10px] font-bold uppercase tracking-widest text-ink-500">Modalidad</th>
                        <th class="px-8 py-5 text-[10px] font-bold uppercase tracking-widest text-ink-500">Valoración</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-200">
                    @forelse ($citas as $cita)
                    <tr class="hover:bg-cream-100/60 transition-colors group">
                        <td class="px-8 py-5">
                            <div class="flex flex-col gap-1">
                                <span class="font-bold text-sage-900 dark:text-cream-100">{{ $cita->paciente->nombre }}</span>
                                <span class="text-[10px] text-ink-500 font-bold uppercase tracking-widest flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Ps. {{ $cita->especialista->usuario->nombre }}
                                </span>
                            </div>
                        </td>
                        <td class="px-8 py-5">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-ink-700">{{ $cita->disponibilidad->fecha->format('d/m/Y') }}</span>
                                <span class="text-xs text-ink-500 font-medium">{{ \Carbon\Carbon::parse($cita->disponibilidad->hora_inicio)->format('h:i A') }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-5">
                            @php
                                $estColor = match($cita->estado) {
                                    \App\Models\Cita::STATUS_COMPLETED => 'bg-sage-600 text-white',
                                    \App\Models\Cita::STATUS_ATTENTION => 'bg-sand-100 text-sand-700',
                                    \App\Models\Cita::STATUS_CANCELLED => 'bg-slate-200 text-slate-700',
                                    \App\Models\Cita::STATUS_RESCHEDULED => 'bg-sand-100 text-sand-700',
                                    default => 'bg-sage-100 text-sage-700',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $estColor }}">
                                {{ $cita->status_badge['label'] }}
                            </span>
                            @if($cita->estado === \App\Models\Cita::STATUS_CANCELLED)
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1.5">Cancelada por {{ $cita->cancelado_por }}</p>
                                @if($cita->motivo_cancelacion)
                                    <p class="text-xs text-ink-500 italic mt-0.5 max-w-[220px]">“{{ $cita->motivo_cancelacion }}”</p>
                                @endif
                            @endif
                        </td>
                        <td class="px-8 py-5">
                            <span class="px-3 py-1 bg-cream-200 text-ink-600 rounded-lg text-[10px] font-bold uppercase tracking-widest">{{ $cita->modalidad->nombre ?? 'N/A' }}</span>
                        </td>
                        <td class="px-8 py-5">
                            @if($cita->calificacion)
                                <div class="flex items-center gap-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $cita->calificacion ? 'text-sand-400' : 'text-cream-300' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endfor
                                    @if($cita->comentario_paciente)
                                        <div class="relative group/tooltip">
                                            <svg class="w-4 h-4 text-ink-400 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-48 p-3 bg-sage-900 text-white text-[10px] rounded-xl opacity-0 group-hover/tooltip:opacity-100 transition-opacity pointer-events-none font-medium leading-relaxed shadow-calm z-10">"{{ $cita->comentario_paciente }}"</div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span class="text-[10px] font-bold text-ink-400 uppercase italic">Sin calificar</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-8 py-20 text-center text-ink-500 italic">No hay citas registradas en el sistema.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-6 bg-cream-100 border-t border-cream-300">{{ $citas->links() }}</div>
    </div>
</div>
@endsection
