@extends('components.layouts.doctor')

@php $active = 'patients'; @endphp

@section('header_title', 'Listado de Pacientes')

@section('content')
<div class="max-w-6xl mx-auto pb-10">

    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-10 gap-6">
        <div>
            <h1 class="font-serif text-3xl md:text-4xl text-sage-900 dark:text-cream-100">Tus pacientes</h1>
            <p class="text-ink-500 dark:text-sage-300 mt-2 text-sm md:text-base">Gestiona el historial y seguimiento de todos tus pacientes asignados.</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative group flex-1">
                <input type="text" placeholder="Buscar paciente..." class="field !pl-12 md:w-80">
                <svg class="w-5 h-5 text-ink-500 absolute left-4 top-1/2 -translate-y-1/2 group-focus-within:text-sage-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Leyenda de seguimiento -->
    <div class="flex flex-wrap items-center gap-4 mb-5 text-[11px] font-semibold text-ink-500">
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-sage-500"></span> Con próxima cita</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-sand-400"></span> Sin próxima cita</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Seguimiento urgente (+21 días)</span>
    </div>

    <!-- Lista -->
    <div class="surface overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left whitespace-nowrap">
                <thead>
                    <tr class="bg-cream-100 border-b border-cream-300">
                        <th class="px-6 py-5 text-[10px] font-bold uppercase tracking-widest text-ink-500">Paciente</th>
                        <th class="px-6 py-5 text-[10px] font-bold uppercase tracking-widest text-ink-500">Próxima cita</th>
                        <th class="px-6 py-5 text-[10px] font-bold uppercase tracking-widest text-ink-500 text-center">Sesiones</th>
                        <th class="px-6 py-5 text-[10px] font-bold uppercase tracking-widest text-ink-500">Última interacción</th>
                        <th class="px-6 py-5 text-[10px] font-bold uppercase tracking-widest text-ink-500">Seguimiento</th>
                        <th class="px-6 py-5 text-[10px] font-bold uppercase tracking-widest text-ink-500 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-200">
                    @forelse($pacientes as $paciente)
                    @php
                        $proxima = $paciente->proxima_cita_fecha ? \Carbon\Carbon::parse($paciente->proxima_cita_fecha) : null;
                        $ultima = $paciente->ultima_interaccion ? \Carbon\Carbon::parse($paciente->ultima_interaccion) : null;
                        $diasUltima = $ultima ? $ultima->diffInDays(now()) : null;

                        if ($proxima) {
                            $segColor = 'bg-sage-100 text-sage-700'; $segDot = 'bg-sage-500'; $segLabel = 'Con próxima cita';
                        } elseif ($diasUltima !== null && $diasUltima > 21) {
                            $segColor = 'bg-red-100 text-red-700'; $segDot = 'bg-red-500'; $segLabel = 'Seguimiento urgente';
                        } else {
                            $segColor = 'bg-sand-100 text-sand-700'; $segDot = 'bg-sand-400'; $segLabel = 'Sin próxima cita';
                        }
                    @endphp
                    <tr class="group hover:bg-cream-100/60 transition-colors">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-4">
                                <div class="w-11 h-11 rounded-full bg-sage-100 text-sage-700 font-bold flex items-center justify-center border-2 border-white shadow-sm ring-1 ring-cream-300 uppercase">
                                    {{ substr($paciente->nombre, 0, 2) }}
                                </div>
                                <div>
                                    <p class="font-bold text-sage-900 dark:text-cream-100 flex items-center gap-2">
                                        {{ $paciente->nombre }}
                                        @if(($paciente->crisis_count ?? 0) > 0)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-[9px] font-bold uppercase tracking-wider">⚠️ Crisis</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-ink-500 font-medium">{{ $paciente->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            @if($proxima)
                                <span class="text-sm font-bold text-sage-700">{{ $proxima->locale('es')->isoFormat('D MMM, YYYY') }}</span>
                                <p class="text-[10px] text-ink-500 font-bold mt-0.5">{{ $proxima->diffForHumans() }}</p>
                            @else
                                <span class="text-sm font-semibold text-ink-400 italic">Sin agendar</span>
                            @endif
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex items-center justify-center">
                                <div class="w-9 h-9 rounded-xl bg-sage-100 text-sage-700 flex items-center justify-center font-bold text-sm" title="Sesiones acumuladas">{{ $paciente->citas_count }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            @if($ultima)
                                <span class="text-sm font-bold text-ink-700">{{ $ultima->diffForHumans() }}</span>
                                <p class="text-[10px] text-ink-500 font-medium mt-0.5">{{ $ultima->locale('es')->isoFormat('D MMM, YYYY') }}</p>
                            @else
                                <span class="text-sm font-semibold text-ink-400 italic">Sin sesiones aún</span>
                            @endif
                        </td>
                        <td class="px-6 py-5">
                            <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $segColor }}">
                                <span class="w-2 h-2 rounded-full {{ $segDot }}"></span> {{ $segLabel }}
                            </span>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <a href="{{ route('doctor.patients.show', $paciente->id) }}" class="inline-flex p-2.5 bg-white border border-cream-300 text-sage-600 rounded-xl hover:bg-sage-600 hover:text-white transition-all shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-8 py-10 text-center text-ink-500 italic">No tienes pacientes asignados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-8 py-5 bg-cream-100 border-t border-cream-300 flex items-center justify-between gap-4">
            <p class="text-[10px] font-bold text-ink-500 uppercase tracking-widest">Mostrando {{ $pacientes->count() }} pacientes</p>
        </div>
    </div>
</div>
@endsection
