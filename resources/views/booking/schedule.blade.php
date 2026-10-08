@extends(auth()->check() && auth()->user()->isReceptionist() ? 'components.layouts.receptionist' : 'components.layouts.app')

@push('styles')
<style>
    .calendar-day-btn:disabled {
        opacity: 0.15;
        cursor: not-allowed;
        filter: grayscale(1);
    }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e3ded2; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #245377; }
</style>
@endpush

@section('content')
<div class="relative min-h-screen pb-20">
    <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-10 pt-6">
        @include('booking.partials.steps', ['current' => 3, 'stepLinks' => array_filter([
            2 => $servicioId ? route('booking.specialist', [
                'servicio_id' => $servicioId,
                'sessions'    => $cantidadSesiones,
                'duracion'    => $duracion,
                'modalidad_id'=> $modalidadId,
            ]) : null,
        ])])

        <!-- Encabezado -->
        <div class="mb-10 md:mb-14">
            <div class="flex items-center gap-2 mb-4">
                <span class="chip-sage">Reserva de sesión</span>
                <div class="w-1.5 h-1.5 rounded-full bg-sage-500 animate-pulse"></div>
            </div>
            <h1 class="font-serif text-3xl md:text-4xl lg:text-5xl text-sage-900 dark:text-cream-100 leading-tight">
                Elige tu momento de <span class="italic text-sand-600">tranquilidad</span>
            </h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start w-full">

            <!-- Calendario -->
            <div class="lg:col-span-4 lg:sticky lg:top-8 w-full">
                <div class="surface p-6 md:p-8 w-full">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="font-serif text-lg md:text-xl text-sage-900 dark:text-cream-100 capitalize">
                            {{ \Carbon\Carbon::parse($fecha)->locale('es')->isoFormat('MMMM YYYY') }}
                        </h3>
                        <div class="flex gap-2">
                            <button type="button" aria-label="Mes anterior" onclick="changeDate('{{ \Carbon\Carbon::parse($fecha)->subMonth()->startOfMonth()->toDateString() }}')" class="w-9 h-9 md:w-10 md:h-10 flex items-center justify-center bg-cream-100 dark:bg-sage-900 text-ink-600 dark:text-sage-300 rounded-xl hover:bg-sage-500 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage-500 focus-visible:ring-offset-2 transition-all">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button type="button" aria-label="Mes siguiente" onclick="changeDate('{{ \Carbon\Carbon::parse($fecha)->addMonth()->startOfMonth()->toDateString() }}')" class="w-9 h-9 md:w-10 md:h-10 flex items-center justify-center bg-cream-100 dark:bg-sage-900 text-ink-600 dark:text-sage-300 rounded-xl hover:bg-sage-500 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage-500 focus-visible:ring-offset-2 transition-all">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-7 mb-4">
                        @foreach(['Dom','Lun','Mar','Mie','Jue','Vie','Sab'] as $day)
                            <div class="text-[10px] font-bold text-ink-500 uppercase tracking-widest text-center">{{ $day[0] }}</div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-7 gap-1">
                        @php
                            $dateObj = \Carbon\Carbon::parse($fecha);
                            $startOfCalendar = $dateObj->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::SUNDAY);
                            $endOfCalendar = $dateObj->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SATURDAY);
                            $current = $startOfCalendar->copy();
                        @endphp

                        @while($current <= $endOfCalendar)
                            @php
                                $isCurrentMonth = $current->month == $dateObj->month;
                                $isSelected = $current->toDateString() == $fecha;
                                $isPast = $current->isPast() && !$current->isToday();
                                $isAvailable = in_array($current->toDateString(), $availableDates);
                            @endphp

                            <button
                                type="button"
                                onclick="changeDate('{{ $current->toDateString() }}')"
                                {{ ($isPast || (!$isAvailable && $isCurrentMonth)) ? 'disabled' : '' }}
                                @if($isSelected) aria-current="date" @endif
                                aria-label="{{ $current->locale('es')->isoFormat('dddd D [de] MMMM') }}{{ $isAvailable ? ', con horarios disponibles' : '' }}"
                                class="calendar-day-btn aspect-square rounded-2xl flex items-center justify-center text-sm font-bold transition-all relative group focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage-500 focus-visible:ring-offset-1
                                {{ !$isCurrentMonth ? 'text-cream-400 dark:text-sage-700' : 'text-ink-700 dark:text-sage-200' }}
                                {{ $isSelected ? 'bg-sage-500 text-white shadow-lg shadow-sage-200 dark:shadow-none scale-105 z-10' : ($isAvailable ? 'bg-sage-50 dark:bg-sage-900/40 hover:bg-sage-100 hover:text-sage-700' : '') }}
                            ">
                                <span aria-hidden="true">{{ $current->day }}</span>
                                @if($isAvailable && !$isSelected)
                                    <div class="absolute top-2 right-2 w-1.5 h-1.5 rounded-full bg-sage-500 animate-pulse"></div>
                                @endif
                                @if($current->isToday() && !$isSelected)
                                    <div class="absolute bottom-1.5 w-1 h-1 rounded-full bg-sand-400"></div>
                                @endif
                            </button>
                            @php $current->addDay(); @endphp
                        @endwhile
                    </div>

                    <div class="mt-8 p-6 bg-sage-800 dark:bg-sage-950 rounded-3xl text-white relative overflow-hidden group">
                        <div class="absolute right-0 top-0 w-24 h-24 bg-sand-300/10 rounded-full blur-2xl"></div>
                        <div class="flex items-center gap-4 relative z-10">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-lg">🌍</div>
                            <div>
                                <p class="text-[9px] font-bold uppercase tracking-[0.2em] text-sand-300 mb-0.5">Zona horaria</p>
                                <p class="text-[11px] font-bold">Lima, Perú (GMT-5)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Horarios -->
            <div class="lg:col-span-8 space-y-8">
                <div class="surface p-6 md:p-10">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10 pb-8 border-b border-cream-300 dark:border-sage-700 w-full">
                        <div class="flex-1">
                            <span class="text-[10px] font-bold text-ink-500 uppercase tracking-widest">Fecha y horario</span>
                            <h2 class="font-serif text-2xl lg:text-3xl text-sage-900 dark:text-cream-100 mt-1">Selecciona tu sesión</h2>
                            <p class="text-sage-700 dark:text-sage-300 font-bold mt-2 text-sm bg-sage-50 dark:bg-sage-900 px-3 py-1 rounded-lg inline-block">
                                📅 {{ \Carbon\Carbon::parse($fecha)->locale('es')->isoFormat('dddd [de] MMMM') }}
                            </p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-ink-500">Escoge un horario disponible:</h3>
                    </div>
                    <div class="space-y-10">
                        @php $hasAny = false; @endphp
                        @foreach(['morning' => ['Mañana', '🌅'], 'afternoon' => ['Tarde', '☀️'], 'evening' => ['Noche', '🌙']] as $key => $meta)
                            @if(isset($availability[$key]) && $availability[$key]->count() > 0)
                            @php $hasAny = true; @endphp
                            <div>
                                <div class="flex items-center gap-4 mb-6">
                                    <span class="text-2xl">{{ $meta[1] }}</span>
                                    <div>
                                        <h4 class="text-xs font-bold uppercase tracking-[0.3em] text-ink-500">{{ $meta[0] }}</h4>
                                        <p class="text-[10px] font-medium text-ink-400 mt-0.5">Ventanas disponibles</p>
                                    </div>
                                    <div class="h-px bg-cream-300 dark:bg-sage-700 flex-1 ml-4"></div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4" role="radiogroup" aria-label="Horarios de la {{ strtolower($meta[0]) }}">
                                    @foreach($availability[$key] as $slot)
                                    <label class="relative group cursor-pointer">
                                        <input type="radio" name="availability_id" value="{{ $slot['id'] }}" class="peer sr-only"
                                            aria-label="{{ $slot['display'] }} a {{ $slot['display_end'] }}, sesión de {{ $slot['duration'] }} minutos con {{ explode(' ', $slot['especialista']->usuario->nombre)[0] }}"
                                            onchange="updateSelectedSlot('{{ \Carbon\Carbon::parse($fecha)->locale('es')->isoFormat('dddd D') }}', '{{ $slot['display'] }}', '{{ $slot['id'] }}', '{{ $slot['display_end'] }}')">
                                        <div class="p-5 rounded-3xl border border-cream-300 dark:border-sage-700 bg-cream-100/50 dark:bg-sage-900/50 hover:bg-white dark:hover:bg-sage-800 hover:border-sage-300 transition-all duration-300 peer-checked:bg-sage-600 peer-checked:border-sage-600 peer-checked:shadow-lg peer-checked:shadow-sage-200 dark:peer-checked:shadow-none peer-focus-visible:ring-2 peer-focus-visible:ring-sage-500 peer-focus-visible:ring-offset-2 group-active:scale-95">
                                            <div class="flex flex-col mb-4">
                                                <div class="text-[10px] font-bold uppercase tracking-widest text-ink-500 group-has-[:checked]:text-sage-100 mb-1 transition-colors">Horario disponible</div>
                                                <div class="text-sm md:text-base font-bold text-sage-900 dark:text-cream-100 group-has-[:checked]:text-white transition-colors">
                                                    {{ $slot['display'] }} — {{ $slot['display_end'] }}
                                                </div>
                                                <div class="mt-3">
                                                    <div class="px-3 py-1.5 bg-sage-50 dark:bg-sage-900 rounded-xl border border-sage-100 dark:border-sage-700 inline-flex items-center gap-2 group-has-[:checked]:bg-white/15 group-has-[:checked]:border-white/25 transition-colors">
                                                        <span class="text-[10px] font-bold uppercase tracking-widest text-sage-600 dark:text-sage-300 group-has-[:checked]:text-white">Sesión de {{ $slot['duration'] }} min</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-white dark:bg-sage-800 p-0.5 shadow-sm border border-cream-300 dark:border-sage-700">
                                                    <img src="{{ $slot['especialista']->foto_url }}" class="w-full h-full object-cover rounded-md">
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-[10px] font-bold uppercase tracking-wider text-ink-500 group-has-[:checked]:text-sage-100 truncate">Especialista</p>
                                                    <p class="text-xs font-bold text-ink-700 dark:text-cream-100 group-has-[:checked]:text-white truncate">{{ explode(' ', $slot['especialista']->usuario->nombre)[0] }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        @endforeach

                        @if(!$hasAny)
                        <div class="py-20 text-center">
                            <div class="w-24 h-24 bg-sage-50 dark:bg-sage-900 rounded-3xl flex items-center justify-center text-5xl mx-auto mb-8">🛋️</div>
                            <h4 class="font-serif text-2xl text-sage-900 dark:text-cream-100">Agenda completa</h4>
                            @if($hasSingleSlots)
                                <div class="mt-4 p-6 bg-sand-50 dark:bg-sage-900 rounded-3xl border border-sand-200 dark:border-sage-700 max-w-sm mx-auto">
                                    <p class="text-sand-800 dark:text-sand-300 font-bold text-sm">Validación de disponibilidad</p>
                                    <p class="text-sand-700/80 dark:text-sand-400/80 text-xs mt-1 font-medium italic">
                                        No hay espacios de {{ $cantidadSesiones }} horas continuas hoy. Prueba con "1 sesión" o cambia de fecha.
                                    </p>
                                    <a href="{{ route('booking.service') }}" class="mt-4 inline-block btn-accent !py-2 !px-6 text-[10px] uppercase tracking-widest">Cambiar cantidad de sesiones</a>
                                </div>
                            @else
                                <p class="text-ink-500 mt-3 max-w-xs mx-auto font-medium">No hay horarios disponibles para este día. Nuestros terapeutas están en sesión.</p>
                                <button onclick="changeDate('{{ \Carbon\Carbon::parse($fecha)->addDay()->toDateString() }}')" class="mt-8 btn-primary !py-3 text-[10px] uppercase tracking-widest">Ver día siguiente →</button>
                            @endif
                        @endif
                        </div>
                    </div>

                    <!-- Lista de espera (opt-in): avísame si se libera un cupo antes con este profesional -->
                    @if($especialista)
                        <div class="mt-8 rounded-3xl border border-cream-300 dark:border-sage-700 bg-cream-50 dark:bg-sage-900/40 p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <div class="w-11 h-11 rounded-2xl bg-sage-100 dark:bg-sage-800 text-sage-600 dark:text-sand-300 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-sage-900 dark:text-cream-100">¿Ningún horario te acomoda?</p>
                                    <p class="text-xs text-ink-500 dark:text-sage-300">Te avisamos si se libera un cupo antes con {{ $especialista->usuario->nombre ?? 'este profesional' }}.</p>
                                </div>
                            </div>
                            @if($enEspera)
                                <span class="chip-sage shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Ya estás en lista de espera
                                </span>
                            @else
                                <form action="{{ route('booking.waitlist') }}" method="POST" class="shrink-0">
                                    @csrf
                                    <input type="hidden" name="especialista_id" value="{{ $especialistaId }}">
                                    <input type="hidden" name="servicio_id" value="{{ $servicioId }}">
                                    <input type="hidden" name="modalidad_id" value="{{ $modalidadId }}">
                                    <button type="submit" class="btn-outline !py-2.5 px-5 text-xs uppercase tracking-widest whitespace-nowrap">Avísame si se libera</button>
                                </form>
                            @endif
                        </div>
                    @endif

                    <!-- Selección actual -->
                    <div class="mt-12 pt-10 border-t border-cream-300 dark:border-sage-700">
                        <div class="bg-gradient-to-br from-sage-800 to-sage-900 dark:from-sage-950 dark:to-sage-900 p-6 md:p-8 rounded-3xl shadow-calm relative overflow-hidden">
                            <div aria-hidden="true" class="absolute -top-10 right-0 w-56 h-56 bg-sand-300/10 rounded-full blur-3xl translate-x-1/3"></div>

                            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center gap-6 lg:gap-8">
                                <!-- Resumen de la selección -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2.5 mb-4">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sand-300"></span>
                                        <p class="text-[10px] font-bold text-sand-300 uppercase tracking-[0.3em]">Tu selección actual</p>
                                    </div>
                                    <div class="flex flex-col gap-3">
                                        <!-- Fecha -->
                                        <div class="flex items-center gap-3 px-4 py-3 bg-white/[0.06] rounded-2xl border border-white/10">
                                            <span class="w-9 h-9 shrink-0 rounded-xl bg-white/10 flex items-center justify-center text-sand-300" aria-hidden="true">
                                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="3"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                            </span>
                                            <div class="min-w-0">
                                                <p class="text-[9px] font-bold uppercase tracking-[0.2em] text-white/40 mb-0.5">Fecha</p>
                                                <span id="selected-date-display" class="block text-sm md:text-base font-bold text-white truncate capitalize">Sin elegir</span>
                                            </div>
                                        </div>
                                        <!-- Horario -->
                                        <div class="flex items-center gap-3 px-4 py-3 bg-sand-300/[0.08] rounded-2xl border border-sand-300/20">
                                            <span class="w-9 h-9 shrink-0 rounded-xl bg-sand-300/15 flex items-center justify-center text-sand-300" aria-hidden="true">
                                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                                            </span>
                                            <div class="min-w-0">
                                                <p class="text-[9px] font-bold uppercase tracking-[0.2em] text-sand-300/60 mb-0.5">Horario</p>
                                                <span id="selected-time-display" class="block text-sm md:text-base font-bold text-sand-200 truncate">--:-- — --:--</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- CTA -->
                                <a href="#" id="next-step-button" role="button" aria-disabled="true" tabindex="-1" class="w-full lg:w-auto shrink-0 px-10 py-5 bg-white/20 text-white rounded-full text-center font-bold uppercase tracking-[0.2em] text-[10px] md:text-xs pointer-events-none transition-all duration-500 whitespace-nowrap focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sand-300 focus-visible:ring-offset-2 focus-visible:ring-offset-sage-900">
                                    Selecciona un horario
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateSelectedSlot(dateLabel, timeLabel, availabilityId, endTimeLabel) {
        const dateDisplay = document.getElementById('selected-date-display');
        const timeDisplay = document.getElementById('selected-time-display');
        const nextButton = document.getElementById('next-step-button');
        const sessions = {{ $cantidadSesiones }};

        if (dateDisplay) {
            dateDisplay.style.opacity = '0';
            setTimeout(() => { dateDisplay.innerText = dateLabel; dateDisplay.style.opacity = '1'; }, 100);
        }
        if (timeDisplay) {
            timeDisplay.style.opacity = '0';
            setTimeout(() => { timeDisplay.innerText = `${timeLabel} — ${endTimeLabel}`; timeDisplay.style.opacity = '1'; }, 100);
        }
        if (nextButton) {
            const baseUrl = "{{ route('booking.checkout') }}";
            const rescheduleId = "{{ $rescheduleId ?? '' }}";
            const especialistaId = "{{ $especialistaId }}";
            const servicioId = "{{ $servicioId }}";
            const duracion = "{{ $duracion }}";
            const modalidadId = "{{ $modalidadId }}";

            let url = `${baseUrl}?availability_id=${availabilityId}&sessions=${sessions}&especialista_id=${especialistaId}&servicio_id=${servicioId}&duracion=${duracion}&modalidad_id=${modalidadId}`;
            if (rescheduleId) url += `&reschedule_id=${rescheduleId}`;

            nextButton.href = url;
            nextButton.classList.remove('bg-white/20', 'pointer-events-none');
            nextButton.classList.add('bg-sand-400', 'hover:bg-sand-500', 'shadow-lg');
            nextButton.setAttribute('aria-disabled', 'false');
            nextButton.setAttribute('tabindex', '0');
            nextButton.innerHTML = `
                <span class="flex items-center gap-3 px-2 justify-center">
                    Continuar reserva
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </span>`;
        }
    }

    function changeDate(newDate) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('fecha', newDate);
        urlParams.set('especialista_id', '{{ $especialistaId }}');
        urlParams.set('servicio_id', '{{ $servicioId }}');
        urlParams.set('duracion', '{{ $duracion }}');
        urlParams.set('modalidad_id', '{{ $modalidadId }}');
        urlParams.set('sessions', '{{ $cantidadSesiones }}');
        document.body.style.opacity = '0.5';
        window.location.search = urlParams.toString();
    }

    document.addEventListener('DOMContentLoaded', () => {
        ['selected-date-display', 'selected-time-display'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.transition = 'opacity 0.2s ease-in-out';
        });
    });
</script>
@endpush
