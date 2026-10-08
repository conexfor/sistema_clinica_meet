@extends(auth()->check() && auth()->user()->isReceptionist() ? 'components.layouts.receptionist' : 'components.layouts.app')

@section('content')
<div class="relative min-h-screen pb-20">
    <div class="max-w-4xl mx-auto pt-6 px-2 sm:px-6">

        @php
            // Parámetros para volver a pasos anteriores desde el checkout.
            $chkServicioId    = optional($servicio)->id;
            $chkEspecialistaId = $availability->especialista_id;
            $chkDuracion      = $baseDuration ?? ($selectedType['max_duration'] ?? 60);
            $chkFecha         = \Carbon\Carbon::parse($availability->fecha)->toDateString();
        @endphp
        @include('booking.partials.steps', ['current' => 4, 'stepLinks' => array_filter([
            2 => $chkServicioId ? route('booking.specialist', [
                'servicio_id' => $chkServicioId,
                'sessions'    => $cantidadSesiones,
                'duracion'    => $chkDuracion,
                'modalidad_id'=> $modalidadId,
            ]) : null,
            3 => $chkEspecialistaId ? route('booking.schedule', array_filter([
                'especialista_id' => $chkEspecialistaId,
                'servicio_id'     => $chkServicioId,
                'duracion'        => $chkDuracion,
                'modalidad_id'    => $modalidadId,
                'sessions'        => $cantidadSesiones,
                'fecha'           => $chkFecha,
                'reschedule_id'   => $rescheduleId ?? null,
            ])) : null,
        ])])

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 md:gap-10">

            <!-- Resumen -->
            <div class="lg:col-span-5 order-1">
                <div class="bg-gradient-to-br from-sage-800 to-sage-900 rounded-3xl p-8 text-white sticky top-10 shadow-calm relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-sand-300/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>

                    <h3 class="font-serif text-xl mb-8 relative z-10 flex items-center gap-3">
                        Resumen de cita
                        <span class="w-1.5 h-1.5 rounded-full bg-sand-300"></span>
                    </h3>

                    <div class="space-y-7 relative z-10">
                        <div class="flex gap-5">
                            <div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center text-2xl">📅</div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-sand-300 mb-1">Fecha y hora</p>
                                <p class="text-lg font-bold">
                                    {{ \Carbon\Carbon::parse($availability->fecha)->locale('es')->isoFormat('dddd D') }} <br>
                                    <span class="text-white/60 text-sm font-medium">a las</span> {{ \Carbon\Carbon::parse($availability->hora_inicio)->format('h:i A') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-5">
                            <div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center overflow-hidden">
                                <img src="{{ $availability->especialista->foto_url }}" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-sand-300 mb-1">Especialista</p>
                                <p class="text-lg font-bold">{{ $availability->especialista->usuario->nombre }}</p>
                            </div>
                        </div>

                        <div class="pt-7 border-t border-white/10">
                            @php
                                $precioUnitario = $servicio->precio ?? ($availability->especialista->servicios->first()->precio ?? 120);
                                $calculatedTotal = $cantidadSesiones * $precioUnitario;
                            @endphp

                            <div class="mb-6 space-y-2">
                                <span class="text-sand-300/70 font-bold text-[10px] tracking-[0.2em] uppercase">Servicio seleccionado</span>
                                <div class="flex justify-between items-start gap-4">
                                    <h4 class="text-lg font-bold text-white leading-tight">{{ $servicio->nombre ?? 'Consulta Psicológica' }}</h4>
                                    <span class="text-white/50 font-bold text-sm">S/ {{ number_format($precioUnitario, 2) }}</span>
                                </div>
                            </div>

                            <div class="justify-between items-center mb-4 hidden md:flex">
                                <span class="text-white/50 font-bold text-xs tracking-widest uppercase">{{ $selectedType['name'] }} x {{ $cantidadSesiones }}</span>
                                <span class="text-xl font-bold">S/ {{ number_format($calculatedTotal, 2) }}</span>
                            </div>

                            @if($isReceptionist)
                                <div class="space-y-3 mb-6">
                                    <label class="block text-[10px] font-bold uppercase tracking-widest text-sand-300 ml-1">Editar total (solo recepción)</label>
                                    <div class="relative">
                                        <span class="absolute left-6 top-1/2 -translate-y-1/2 text-2xl font-bold text-white">S/</span>
                                        <input type="number" step="0.01" name="total_manual" id="total_manual" value="{{ $calculatedTotal }}" form="checkout-form"
                                               class="w-full bg-white/10 border-2 border-sand-300/30 rounded-2xl pl-16 pr-6 py-4 text-3xl font-bold text-white focus:border-sand-300 outline-none transition-all">
                                    </div>
                                </div>
                            @endif

                            <div class="flex justify-between items-center text-sand-300">
                                <span class="font-bold text-xs tracking-widest uppercase">Total a pagar</span>
                                <span class="text-3xl font-bold tracking-tight" id="display_total">S/ {{ number_format($calculatedTotal, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 p-4 bg-white/5 border border-white/10 rounded-2xl text-[10px] text-cream-100/80 font-bold text-center leading-relaxed">
                        💡 Recibirás los detalles de conexión por correo 15 minutos antes de tu sesión.
                    </div>
                </div>
            </div>

            <!-- Pago -->
            <div class="lg:col-span-7 order-2">
                <div class="surface p-8 md:p-12">
                    <div class="mb-10">
                        <span class="chip-sage mb-4">Paso 4 · Pago</span>
                        <h2 class="font-serif text-2xl md:text-3xl text-sage-900 dark:text-cream-100 mt-3">Finalizar reserva</h2>
                        <p class="text-ink-500 dark:text-sage-300 mt-1 text-sm">Tu bienestar mental es la mejor inversión.</p>
                    </div>

                    @if(session('error'))
                        <div class="mb-8 p-4 bg-sand-50 border border-sand-300 rounded-2xl flex items-center gap-3 text-sand-800 alert-auto-dismiss">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-xs font-bold">{{ session('error') }}</p>
                        </div>
                    @endif
                    @if(session('info'))
                        <div class="mb-8 p-4 bg-sage-50 border border-sage-200 rounded-2xl flex items-center gap-3 text-sage-700">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-xs font-bold">{{ session('info') }}</p>
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="mb-8 p-4 bg-sand-50 border border-sand-300 rounded-2xl flex items-start gap-3 text-sand-800">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div>
                                <p class="text-xs font-bold mb-1">No pudimos confirmar tu reserva. Revisa lo siguiente:</p>
                                <ul class="text-xs font-medium list-disc list-inside space-y-0.5">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <form id="checkout-form" action="{{ route('booking.store') }}" method="POST" class="space-y-8">
                        @csrf
                        <input type="hidden" name="disponibilidad_id" value="{{ $availability->id }}">
                        <input type="hidden" name="servicio_id" value="{{ $chkServicioId }}">
                        <input type="hidden" name="cantidad_sesiones" value="{{ $cantidadSesiones }}">
                        <input type="hidden" name="tipo_consulta" value="{{ $typeCode }}">
                        <input type="hidden" name="duracion_minutos" value="{{ $baseDuration ?? $selectedType['max_duration'] }}">
                        <input type="hidden" name="modalidad_id" value="{{ $modalidadId }}">
                        @if(isset($rescheduleId))<input type="hidden" name="reschedule_id" value="{{ $rescheduleId }}">@endif
                        @if(isset($pacienteId))<input type="hidden" name="paciente_id" value="{{ $pacienteId }}">@endif

                        <div class="space-y-5">
                            <div class="flex items-center gap-4">
                                <span class="text-xs font-bold uppercase tracking-[0.3em] text-ink-500">Método de pago</span>
                                <div class="h-px bg-cream-300 flex-1"></div>
                            </div>
                            @if($isReceptionist)
                                {{-- Recepción: selección completa de método de pago --}}
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4" role="radiogroup" aria-label="Método de pago">
                                    @foreach($metodosPago as $key => $label)
                                        <label class="relative group cursor-pointer">
                                            <input type="radio" name="metodo_pago" value="{{ $key }}" {{ $loop->first ? 'checked' : '' }} class="peer sr-only">
                                            <div class="relative p-4 rounded-2xl border border-cream-300 dark:border-sage-700 bg-white dark:bg-sage-800 hover:border-sage-300 peer-checked:border-sage-500 peer-checked:bg-sage-50 dark:peer-checked:bg-sage-900 peer-checked:shadow-sm peer-focus-visible:ring-2 peer-focus-visible:ring-sage-500 peer-focus-visible:ring-offset-2 transition-all text-center">
                                                <span aria-hidden="true" class="absolute top-2 right-2 w-4 h-4 rounded-full bg-sage-500 text-white items-center justify-center hidden group-has-[:checked]:flex">
                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                                                </span>
                                                <p class="text-[10px] font-bold uppercase tracking-widest text-ink-600 dark:text-sage-300 group-has-[:checked]:text-sage-700 dark:group-has-[:checked]:text-sage-200">{{ $label }}</p>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                {{-- Cliente: método fijo. La reserva se confirma solo con el botón. --}}
                                <input type="hidden" name="metodo_pago" value="efectivo">
                                <div class="flex items-center gap-4 p-4 rounded-2xl border border-cream-300 dark:border-sage-700 bg-cream-100/60 dark:bg-sage-800">
                                    <span class="w-11 h-11 shrink-0 rounded-xl bg-sage-100 dark:bg-sage-900 flex items-center justify-center text-sage-600 dark:text-sage-300" aria-hidden="true">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M6 12h.01M18 12h.01"/></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500 mb-0.5">Método seleccionado</p>
                                        <p class="text-sm font-bold text-sage-800 dark:text-cream-100">{{ $metodosPago['efectivo'] ?? 'Efectivo' }}</p>
                                    </div>
                                    <span class="ml-auto shrink-0 chip-sage !text-[10px]">Pago en clínica</span>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-5">
                            <div class="flex items-center gap-4">
                                <span class="text-xs font-bold uppercase tracking-[0.3em] text-ink-500">Comprobante de pago</span>
                                <div class="h-px bg-cream-300 flex-1"></div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="label !text-[10px] !uppercase !tracking-widest">Tipo de documento</label>
                                    <select name="tipo_comprobante" id="tipo_comprobante" class="field appearance-none cursor-pointer">
                                        @foreach($documentosConfig as $config)
                                            <option value="{{ $config->tipo }}" data-serie="{{ $config->serie }}" data-correlativo="{{ $config->ultimo_correlativo + 1 }}">
                                                {{ ucfirst(str_replace('_', ' ', $config->tipo)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex gap-4">
                                    <div class="w-28 md:w-32">
                                        <label class="label !text-[10px] !uppercase !tracking-widest">Serie</label>
                                        <input type="text" id="display_serie" readonly class="field !bg-cream-100 text-center tracking-widest font-bold">
                                    </div>
                                    <div class="flex-1">
                                        <label class="label !text-[10px] !uppercase !tracking-widest">Correlativo</label>
                                        <input type="text" id="display_correlativo" readonly class="field !bg-cream-100 text-center tracking-widest font-bold">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6">
                            @if($isReceptionist && isset($rescheduleId))
                                <div class="mb-4">
                                    <label class="label !text-[10px] !uppercase !tracking-widest">Solicitante</label>
                                    <select name="solicitante" class="field appearance-none cursor-pointer">
                                        <option value="recepcionista" selected>Recepción (yo)</option>
                                        <option value="paciente">Paciente (solicitado por paciente)</option>
                                    </select>
                                </div>
                            @endif

                            <button type="submit" class="btn-primary w-full py-5 text-xs uppercase tracking-[0.2em]">
                                {{ isset($rescheduleId) ? 'Confirmar reprogramación' : 'Confirmar y pagar S/ ' . number_format($calculatedTotal, 2) }}
                            </button>
                            <p class="mt-5 text-[10px] font-bold text-ink-500 flex items-center justify-center gap-2">
                                <svg class="w-3 h-3 text-sage-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                Tu reserva se confirma al instante · Pagarás en la clínica
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    const tipoComp = document.getElementById('tipo_comprobante');
    const displaySerie = document.getElementById('display_serie');
    const displayCorr = document.getElementById('display_correlativo');
    const totalManual = document.getElementById('total_manual');
    const displayTotal = document.getElementById('display_total');
    const submitBtn = document.querySelector('#checkout-form button[type="submit"]');

    function updateDocInfo() {
        const selected = tipoComp.options[tipoComp.selectedIndex];
        displaySerie.value = selected.dataset.serie;
        displayCorr.value = String(selected.dataset.correlativo).padStart(6, '0');
    }
    if (tipoComp) { tipoComp.addEventListener('change', updateDocInfo); updateDocInfo(); }

    if (totalManual) {
        totalManual.addEventListener('input', (e) => {
            const val = parseFloat(e.target.value) || 0;
            displayTotal.textContent = 'S/ ' + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            if (!submitBtn.textContent.includes('Reprogramación')) {
                submitBtn.textContent = 'Confirmar y pagar S/ ' + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        });
    }

    // Estado de carga al confirmar: evita doble envío y da feedback visual.
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm && submitBtn) {
        checkoutForm.addEventListener('submit', () => {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-70', 'cursor-wait');
            submitBtn.innerHTML = `
                <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                    <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Procesando…`;
        });
    }
</script>
@endpush
@endsection
