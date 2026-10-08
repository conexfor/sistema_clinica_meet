@extends(auth()->check() && auth()->user()->isReceptionist() ? 'components.layouts.receptionist' : 'components.layouts.app')

@section('content')
<div class="relative min-h-screen pb-20">
    <div class="max-w-5xl mx-auto pt-6 px-2 sm:px-6">
        @include('booking.partials.steps', ['current' => 2])

        <div class="mb-12 max-w-2xl">
            <span class="chip-sage mb-5">Paso 2 · Profesional</span>
            <h1 class="font-serif text-3xl md:text-4xl lg:text-5xl text-sage-900 dark:text-cream-100 mt-4 mb-3 leading-tight">
                Elige a tu <span class="italic text-sand-600">profesional</span>
            </h1>
            <p class="text-ink-500 dark:text-sage-300 text-base md:text-lg">Especialistas expertos en <span class="text-sage-700 dark:text-sage-200 font-semibold">{{ $servicio->nombre }}</span>, verificados y listos para acompañarte.</p>
        </div>

        @if($especialistas->isEmpty())
            {{-- Estado vacío: ningún profesional disponible para este servicio --}}
            <div class="surface p-10 md:p-16 text-center max-w-xl mx-auto">
                <div class="w-24 h-24 bg-sage-50 dark:bg-sage-800 rounded-3xl flex items-center justify-center text-5xl mx-auto mb-8" aria-hidden="true">🗓️</div>
                <h2 class="font-serif text-2xl md:text-3xl text-sage-900 dark:text-cream-100 mb-3">Sin profesionales disponibles</h2>
                <p class="text-ink-500 dark:text-sage-300 max-w-sm mx-auto leading-relaxed">
                    No encontramos especialistas con horarios libres para
                    <span class="font-bold text-sage-700 dark:text-sage-200">{{ $servicio->nombre }}</span>
                    @if($sessions > 1) en bloques de {{ $sessions }} sesiones consecutivas @endif
                    durante los próximos 30 días.
                </p>
                <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('booking.service') }}" class="btn-primary w-full sm:w-auto !px-8 py-4 text-xs uppercase tracking-[0.15em]">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                        Elegir otro servicio
                    </a>
                    @if($sessions > 1)
                    <a href="{{ route('booking.specialist', ['servicio_id' => $servicio->id, 'sessions' => 1, 'duracion' => $duracion, 'modalidad_id' => $modalidadId]) }}" class="btn-outline w-full sm:w-auto !px-8 py-4 text-xs uppercase tracking-[0.15em]">
                        Probar con 1 sesión
                    </a>
                    @endif
                </div>
            </div>
        @else
        <form action="{{ route('booking.schedule') }}" method="GET">
            <input type="hidden" name="servicio_id" value="{{ $servicio->id }}">
            <input type="hidden" name="sessions" value="{{ $sessions }}">
            <input type="hidden" name="duracion" value="{{ $duracion }}">
            <input type="hidden" name="modalidad_id" value="{{ $modalidadId }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" role="radiogroup" aria-label="Profesionales disponibles para {{ $servicio->nombre }}">
                @foreach($especialistas as $especialista)
                @php
                    $disponibleHoy = app(\App\Services\Booking\MedicalBookingService::class)->hasAvailabilityOnDate($especialista->id, now()->toDateString(), $sessions, $duracion);
                @endphp
                <label class="group relative cursor-pointer">
                    <input type="radio" name="especialista_id" value="{{ $especialista->id }}" class="peer sr-only" {{ $loop->first ? 'checked' : '' }}
                        aria-label="{{ $especialista->usuario->nombre }}, {{ $especialista->especialidad }}{{ $disponibleHoy ? ', disponible hoy' : '' }}">
                    <div class="surface surface-hover h-full p-6 md:p-7 transition-all duration-300 peer-checked:border-sage-500 peer-checked:ring-2 peer-checked:ring-sage-500/25 peer-checked:bg-sage-50/60 dark:peer-checked:bg-sage-900/50 peer-focus-visible:ring-2 peer-focus-visible:ring-sage-500 peer-focus-visible:ring-offset-2 group-hover:border-sage-300">
                        <div class="flex items-center gap-5">
                            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full overflow-hidden border-4 border-white dark:border-sage-800 shadow-soft ring-1 ring-cream-300 dark:ring-sage-700 group-hover:scale-105 transition-transform shrink-0">
                                <img src="{{ $especialista->foto_url }}" alt="Foto de {{ $especialista->usuario->nombre }}" loading="lazy" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-serif text-xl sm:text-2xl text-sage-900 dark:text-cream-100 mb-1">{{ $especialista->usuario->nombre }}</h3>
                                <p class="text-sand-600 font-bold text-xs sm:text-sm uppercase tracking-widest">{{ $especialista->especialidad }}</p>
                            </div>
                        </div>

                        @if($especialista->biografia)
                        <div class="mt-6">
                            <p class="text-ink-600 dark:text-sage-300 text-sm line-clamp-3 leading-relaxed">{{ $especialista->biografia }}</p>
                        </div>
                        @endif

                        <div class="mt-8 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                @if($disponibleHoy)
                                    <span class="w-2 h-2 rounded-full bg-sage-500 animate-pulse" aria-hidden="true"></span>
                                    <span class="text-[10px] font-bold text-sage-600 dark:text-sage-300 uppercase tracking-widest">Disponible hoy</span>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-cream-400" aria-hidden="true"></span>
                                    <span class="text-[10px] font-bold text-ink-500 uppercase tracking-widest">Próximos días</span>
                                @endif
                            </div>
                            <div class="w-10 h-10 rounded-full border-2 border-cream-300 dark:border-sage-700 flex items-center justify-center group-has-[:checked]:bg-sage-500 group-has-[:checked]:border-sage-500 transition-colors" aria-hidden="true">
                                <svg class="w-5 h-5 text-white opacity-0 group-has-[:checked]:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>
                    </div>
                </label>
                @endforeach
            </div>

            <div class="mt-12">
                <button type="submit" class="btn-primary w-full py-5 text-sm uppercase tracking-[0.15em]">
                    Ver horarios disponibles
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>
            </div>
        </form>
        @endif
    </div>
</div>
@endsection
