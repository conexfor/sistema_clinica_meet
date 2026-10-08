@extends('components.layouts.doctor')

@php $active = 'schedule'; @endphp

@section('header_title', 'Gestión de Horarios')

@push('styles')
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e3ded2; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cfc8b8; }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto pb-10" x-data="{ cancel: { open: false, action: '', nombre: '' } }">

    @if($errors->any())
        <div class="mb-8 p-4 bg-sand-50 border border-sand-300 text-sand-800 rounded-2xl font-bold text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <!-- Encabezado -->
    <div class="flex flex-col xl:flex-row items-start xl:items-center justify-between mb-10 gap-6">
        <div>
            <nav class="flex mb-3 text-[10px] font-bold uppercase tracking-[0.2em] text-ink-500">
                <ol class="flex items-center gap-2">
                    <li><a href="{{ route('doctor.dashboard') }}" class="hover:text-sage-600 transition-colors">Panel</a></li>
                    <li><span class="mx-1">/</span></li>
                    <li class="text-sage-600">Agenda</li>
                </ol>
            </nav>
            <h1 class="font-serif text-3xl md:text-4xl text-sage-900 dark:text-cream-100">Tu agenda profesional</h1>
            <p class="text-ink-500 dark:text-sage-300 mt-2 text-sm md:text-base">Configura tus ventanas de atención para que los pacientes puedan agendar sesiones.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full xl:w-auto">
            <button onclick="document.getElementById('weeklyModal').classList.remove('hidden')" class="btn-outline !rounded-2xl px-6 py-3.5 text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Horario fijo semanal
            </button>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="btn-primary !rounded-2xl px-6 py-3.5 text-sm group">
                <svg class="w-5 h-5 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Bloque específico
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Próximas citas + ventanas -->
        <div class="lg:col-span-1 space-y-8 order-2 lg:order-1">

            <!-- Próximas citas -->
            <div class="surface p-6 md:p-8">
                <h3 class="font-serif text-xl text-sage-900 dark:text-cream-100 mb-6 flex items-center justify-between">
                    <span>Próximas citas</span>
                    <span class="px-3 py-1 bg-sage-100 text-sage-700 text-[10px] font-bold rounded-full">{{ $upcomingCitas->count() }}</span>
                </h3>
                <div class="space-y-4 max-h-[420px] overflow-y-auto pr-2 custom-scrollbar">
                    @forelse($upcomingCitas as $cita)
                        @php
                            $yaPaso = $cita->end_at->isPast();
                            $enAtencion = $cita->estado === \App\Models\Cita::STATUS_ATTENTION;
                            if ($yaPaso && !$enAtencion) { $estadoChip = ['Pendiente de cerrar','bg-red-100 text-red-700']; }
                            elseif ($enAtencion) { $estadoChip = ['En atención','bg-sand-100 text-sand-700']; }
                            elseif ($cita->estado === \App\Models\Cita::STATUS_RESCHEDULED) { $estadoChip = ['Reprogramada','bg-sand-100 text-sand-700']; }
                            else { $estadoChip = ['Reservada','bg-sage-100 text-sage-700']; }
                        @endphp
                        <div class="p-4 bg-cream-100 dark:bg-sage-900 rounded-2xl border border-transparent {{ $yaPaso && !$enAtencion ? '!border-red-200' : '' }}">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-sage-900 dark:text-cream-100 truncate">{{ $cita->paciente->nombre ?? 'Paciente' }}</p>
                                    <p class="text-[11px] text-ink-500 font-semibold">
                                        {{ $cita->disponibilidad->fecha->locale('es')->isoFormat('ddd D MMM') }} ·
                                        {{ \Carbon\Carbon::parse($cita->disponibilidad->hora_inicio)->format('h:i A') }}
                                    </p>
                                </div>
                                <span class="shrink-0 px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider {{ $estadoChip[1] }}">{{ $estadoChip[0] }}</span>
                            </div>
                            <div class="flex items-center gap-2 mt-3">
                                <a href="{{ route('doctor.session', $cita) }}" class="flex-1 text-center text-[11px] font-bold text-sage-700 bg-white dark:bg-sage-800 border border-cream-300 dark:border-sage-700 rounded-xl py-2 hover:border-sage-300 transition-colors">Abrir</a>
                                @if(!$enAtencion)
                                    <form action="{{ route('doctor.appointments.update_status', $cita) }}" method="POST" class="flex-1">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="estado" value="en_atencion">
                                        <button type="submit" class="w-full text-[11px] font-bold text-sand-700 bg-sand-50 border border-sand-200 rounded-xl py-2 hover:bg-sand-100 transition-colors">Iniciar</button>
                                    </form>
                                @endif
                                <form action="{{ route('doctor.appointments.update_status', $cita) }}" method="POST" class="flex-1" data-confirm="¿Marcar esta cita como realizada?" data-confirm-ok="Finalizar">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="estado" value="realizada">
                                    <button type="submit" class="w-full text-[11px] font-bold text-white bg-sage-600 rounded-xl py-2 hover:bg-sage-700 transition-colors">Finalizar</button>
                                </form>
                            </div>
                            <button type="button" @click="cancel = { open: true, action: '{{ route('doctor.appointments.update_status', $cita) }}', nombre: @js($cita->paciente->nombre ?? 'Paciente') }"
                                    class="mt-2 w-full text-[11px] font-bold text-red-600 hover:text-red-700 hover:underline transition-colors">
                                Cancelar cita
                            </button>
                        </div>
                    @empty
                        <div class="text-center py-8"><p class="text-ink-500 text-sm italic">No tienes citas próximas.</p></div>
                    @endforelse
                </div>
            </div>

            <!-- Ventanas de disponibilidad -->
            <div class="surface p-6 md:p-8">
                <h3 class="font-serif text-xl text-sage-900 dark:text-cream-100 mb-8 flex items-center justify-between">
                    <span>Ventanas de disponibilidad</span>
                    <span class="px-3 py-1 bg-cream-200 text-ink-600 text-[10px] font-bold rounded-full">{{ $availability->count() }}</span>
                </h3>

                <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2 custom-scrollbar">
                    @forelse($availability->groupBy(fn($item) => $item->fecha->format('Y-m-d')) as $fecha => $bloques)
                        <div class="space-y-3">
                            <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] text-sand-600 mb-2 px-2">
                                {{ \Carbon\Carbon::parse($fecha)->locale('es')->isoFormat('dddd, D [de] MMMM') }}
                            </h4>
                            @foreach($bloques as $bloque)
                                <div class="group flex items-center justify-between p-4 bg-cream-100 dark:bg-sage-900 rounded-2xl hover:bg-white dark:hover:bg-sage-800 hover:shadow-soft transition-all border border-transparent hover:border-cream-300">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-white dark:bg-sage-800 border border-cream-300 dark:border-sage-700 flex items-center justify-center text-sm shadow-sm">🕒</div>
                                        <div>
                                            <p class="text-sm font-bold text-sage-900 dark:text-cream-100">{{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i A') }}</p>
                                            <p class="text-[10px] {{ $bloque->esta_reservada ? 'text-sand-600' : 'text-sage-600' }} font-bold uppercase">{{ $bloque->esta_reservada ? 'Reservado' : 'Disponible' }}</p>
                                        </div>
                                    </div>
                                    @if(!$bloque->esta_reservada)
                                        <form action="{{ route('doctor.schedule.destroy', $bloque->id) }}" method="POST" data-confirm="¿Eliminar este bloque de disponibilidad?" data-confirm-ok="Eliminar" data-confirm-variant="danger">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-8 h-8 rounded-lg bg-white dark:bg-sage-800 border border-cream-300 text-ink-400 hover:text-sand-600 hover:border-sand-200 flex items-center justify-center transition-all opacity-0 group-hover:opacity-100">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <div class="text-center py-10"><p class="text-ink-500 text-sm italic">No tienes bloques configurados.</p></div><
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="lg:col-span-2 space-y-8 order-1 lg:order-2">
            <div class="rounded-3xl p-8 md:p-10 text-white relative overflow-hidden shadow-calm min-h-[350px] bg-gradient-to-br from-sage-700 to-sage-900">
                <div class="absolute top-0 right-0 w-80 h-80 bg-sand-300/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                <div class="relative z-10">
                    <span class="px-4 py-1.5 bg-sand-400 text-white text-[10px] font-bold uppercase tracking-[0.2em] rounded-full">Resumen de agenda</span>
                    <h2 class="font-serif text-3xl md:text-4xl mt-6 mb-4 leading-tight">Optimiza tu tiempo clínico</h2>
                    <p class="text-cream-100/80 text-base md:text-lg leading-relaxed max-w-md">
                        Los pacientes confían en tu puntualidad. Los bloques de 60 minutos son los más populares entre nuestros usuarios.
                    </p>
                    <div class="grid grid-cols-2 gap-6 mt-10">
                        <div class="p-6 bg-white/5 border border-white/10 rounded-3xl">
                            <p class="font-serif text-4xl">{{ $availability->where('esta_reservada', false)->count() }}</p>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-sand-300 mt-1">Libres</p>
                        </div>
                        <div class="p-6 bg-white/5 border border-white/10 rounded-3xl">
                            <p class="font-serif text-4xl">{{ $availability->where('esta_reservada', true)->count() }}</p>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-cream-200 mt-1">Reservados</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Horario semanal fijo -->
            <div class="surface p-8 md:p-10 space-y-8">
                <h3 class="font-serif text-xl text-sage-900 dark:text-cream-100 flex items-center justify-between">
                    <span>Horario semanal fijo</span>
                    <span class="text-[10px] font-bold uppercase text-sand-600">Recurrente</span>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    @forelse($weeklySchedules as $weekly)
                        <div class="p-6 bg-sage-50 dark:bg-sage-900 rounded-3xl border border-sage-100 dark:border-sage-700 flex items-center justify-between hover:bg-white dark:hover:bg-sage-800 hover:shadow-soft transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-sage-600 text-white flex items-center justify-center font-bold text-xs">{{ substr($weekly->nombre_dia, 0, 1) }}</div>
                                <div>
                                    <p class="text-sm font-bold text-sage-900 dark:text-cream-100">{{ $weekly->nombre_dia }}</p>
                                    <p class="text-xs font-bold text-sage-600 dark:text-sage-300">{{ \Carbon\Carbon::parse($weekly->hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($weekly->hora_fin)->format('h:i A') }}</p>
                                </div>
                            </div>
                            <form action="{{ route('doctor.weekly.destroy', $weekly->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg text-ink-400 hover:text-sand-600 hover:bg-sand-50 flex items-center justify-center transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="col-span-full py-10 text-center border-2 border-dashed border-cream-300 rounded-3xl">
                            <p class="text-ink-500 text-sm">No has definido horarios fijos semanales.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Nota -->
            <div class="surface p-8 md:p-10 flex items-center gap-6">
                <div class="w-20 h-20 bg-sand-100 rounded-3xl flex items-center justify-center text-3xl flex-shrink-0">💡</div>
                <div>
                    <h4 class="font-serif text-lg text-sage-900 dark:text-cream-100">Sincronización inteligente</h4>
                    <p class="text-ink-500 dark:text-sage-300 text-sm leading-relaxed mt-1">
                        Tus horarios aparecerán instantáneamente en la página de reservas. Evita superposiciones revisando primero tus compromisos externos.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: cancelar cita (doctor) -->
    <div x-show="cancel.open" x-cloak class="fixed inset-0 z-[100]">
        <div class="absolute inset-0 bg-sage-950/60 backdrop-blur-sm" @click="cancel.open = false"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-4">
            <div class="surface p-8 border-t-4 border-red-400">
                <h3 class="font-serif text-2xl text-red-700 mb-1">Cancelar cita</h3>
                <p class="text-sm text-ink-500 mb-6">Vas a cancelar la cita de <span class="font-bold" x-text="cancel.nombre"></span>. Puedes indicar un motivo (opcional).</p>
                <form :action="cancel.action" method="POST" class="space-y-5">
                    @csrf @method('PATCH')
                    <input type="hidden" name="estado" value="cancelada">
                    <div>
                        <label for="doc_motivo" class="label">Motivo (opcional)</label>
                        <textarea id="doc_motivo" name="motivo_cancelacion" rows="3" maxlength="500" class="field resize-none" placeholder="Ej. Imprevisto, reprogramación externa…"></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="cancel.open = false" class="flex-1 btn bg-cream-200 text-ink-700 hover:bg-cream-300 py-3">Volver</button>
                        <button type="submit" class="flex-1 btn bg-red-600 text-white hover:bg-red-700 py-3">Cancelar cita</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: bloque específico -->
<div id="addModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-sage-950/60 backdrop-blur-sm" onclick="document.getElementById('addModal').classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg p-4">
        <div class="surface p-8 md:p-10">
            <h3 class="font-serif text-2xl text-sage-900 dark:text-cream-100 mb-2">Nueva ventana de atención</h3>
            <p class="text-ink-500 dark:text-sage-300 mb-8">Define cuándo estarás disponible para tus pacientes.</p>
            <form action="{{ route('doctor.schedule.store') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="label">Fecha</label>
                    <input type="date" name="fecha" required min="{{ date('Y-m-d') }}" class="field">
                </div>
                <div class="grid grid-cols-2 gap-6">
                    <div><label class="label">Inicio</label><input type="time" name="hora_inicio" required class="field"></div>
                    <div><label class="label">Fin</label><input type="time" name="hora_fin" required class="field"></div>
                </div>
                <div class="pt-4 flex gap-4">
                    <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="flex-1 py-3.5 text-ink-500 font-bold uppercase tracking-widest text-xs hover:text-ink-700">Cancelar</button>
                    <button type="submit" class="flex-[2] btn-primary py-3.5">Guardar horario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: horario semanal -->
<div id="weeklyModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-sage-950/60 backdrop-blur-sm" onclick="document.getElementById('weeklyModal').classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg p-4">
        <div class="surface p-8 md:p-10">
            <h3 class="font-serif text-2xl text-sage-900 dark:text-cream-100 mb-2">Horario recurrente</h3>
            <p class="text-ink-500 dark:text-sage-300 mb-8">Define tu jornada fija semanal.</p>
            <form action="{{ route('doctor.weekly.store') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="label">Día de la semana</label>
                    <select name="dia_semana" required class="field appearance-none cursor-pointer">
                        <option value="1">Lunes</option><option value="2">Martes</option><option value="3">Miércoles</option>
                        <option value="4">Jueves</option><option value="5">Viernes</option><option value="6">Sábado</option><option value="0">Domingo</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-6">
                    <div><label class="label">Inicio</label><input type="time" name="hora_inicio" required class="field"></div>
                    <div><label class="label">Fin</label><input type="time" name="hora_fin" required class="field"></div>
                </div>
                <div class="pt-4 flex gap-4">
                    <button type="button" onclick="document.getElementById('weeklyModal').classList.add('hidden')" class="flex-1 py-3.5 text-ink-500 font-bold uppercase tracking-widest text-xs hover:text-ink-700">Cancelar</button>
                    <button type="submit" class="flex-[2] btn-accent py-3.5">Establecer horario</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
