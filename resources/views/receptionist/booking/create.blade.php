@extends('components.layouts.receptionist')

@php $active = 'booking'; @endphp

@section('header_title', 'Nueva Reserva de Recepción')

@push('styles')
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cfc8b8; border-radius: 10px; }
    /* Selects con chevron propio */
    .select-wrap { position: relative; }
    .select-wrap::after {
        content: ''; position: absolute; right: 1rem; top: 50%; width: 0.6rem; height: 0.6rem;
        border-right: 2px solid #64727d; border-bottom: 2px solid #64727d;
        transform: translateY(-65%) rotate(45deg); pointer-events: none;
    }
    .stepper-btn {
        width: 2.75rem; height: 2.75rem; border-radius: 1rem; display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem; font-weight: 700; color: #245377; background: #eff6fb; border: 1px solid #b3d6ec;
        transition: all .2s; user-select: none;
    }
    .stepper-btn:hover { background: #3480ae; color: #fff; border-color: #3480ae; }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto pb-12">

    <!-- Encabezado -->
    <div class="mb-8">
        <a href="{{ route('receptionist.dashboard') }}" class="inline-flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-ink-500 hover:text-sage-600 transition-colors mb-5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
            Volver al panel
        </a>
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-sage-500 to-sage-700 text-white flex items-center justify-center shadow-soft shrink-0">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14v3m-1.5-1.5h3"/></svg>
            </div>
            <div>
                <h1 class="font-serif text-3xl md:text-4xl text-sage-900 dark:text-cream-100">Agendar nueva cita</h1>
                <p class="text-ink-500 dark:text-sage-300 mt-1">Registra una atención en 3 pasos: paciente, servicio y horario.</p>
            </div>
        </div>
    </div>

    <form action="{{ route('receptionist.booking.store') }}" method="POST" id="bookingForm">
        @csrf

        @if($errors->any())
            <div class="mb-6 p-6 bg-sand-50 border border-sand-300 rounded-3xl">
                <div class="flex items-center gap-3 text-sand-800 mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-bold uppercase tracking-[0.2em] text-[10px]">Errores de validación</span>
                </div>
                <div class="space-y-1">
                    @foreach($errors->all() as $error)<p class="text-sand-700 text-xs font-bold ml-9">• {{ $error }}</p>@endforeach
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-6 bg-sand-50 border border-sand-300 rounded-3xl flex items-center gap-4 text-sand-800">
                <div class="w-10 h-10 rounded-xl bg-sand-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-xs font-bold">{{ session('error') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

            <!-- Columna del formulario -->
            <div class="lg:col-span-2 space-y-6">

                <!-- 1: Paciente -->
                <div class="surface p-6 md:p-8">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-11 h-11 rounded-2xl bg-sage-600 text-white flex items-center justify-center text-lg font-bold shadow-soft shrink-0">1</div>
                        <div>
                            <h3 class="font-serif text-xl text-sage-900 dark:text-cream-100">Paciente</h3>
                            <p class="text-xs text-ink-500">Busca uno existente o registra uno nuevo</p>
                        </div>
                    </div>

                    <label class="label">Buscar paciente</label>
                    <div class="relative">
                        <input type="text" id="patientSearch" placeholder="Nombre o email del paciente..." class="field !pl-12" autocomplete="off">
                        <svg class="w-5 h-5 text-ink-500 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <div id="searchResults" class="absolute z-50 left-0 right-0 mt-2 bg-white rounded-2xl border border-cream-300 shadow-calm hidden max-h-60 overflow-y-auto custom-scrollbar"></div>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-4 flex-wrap">
                        <button type="button" id="showNewPatient" class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-sage-600 hover:text-sage-800 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                            Registrar nuevo paciente
                        </button>
                        <button type="button" id="clearPatient" class="hidden inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-sand-600 hover:text-sand-800 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                            Limpiar
                        </button>
                    </div>

                    <input type="hidden" name="paciente_id" id="paciente_id">

                    <div class="space-y-5 mt-6 hidden" id="nuevo-paciente-fields">
                        <div class="h-px bg-cream-300"></div>
                        <div>
                            <label class="label">Nombre completo</label>
                            <input type="text" name="nombre" id="nombre" placeholder="Ej: Nicolás Rojas" class="field">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="label">Email</label>
                                <input type="email" name="email" id="email" placeholder="paciente@mail.com" class="field">
                            </div>
                            <div>
                                <label class="label">Teléfono</label>
                                <input type="text" name="telefono" id="telefono" placeholder="Ej: 999 888 777" class="field">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2: Servicio -->
                <div class="surface p-6 md:p-8">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-11 h-11 rounded-2xl bg-sage-600 text-white flex items-center justify-center text-lg font-bold shadow-soft shrink-0">2</div>
                        <div>
                            <h3 class="font-serif text-xl text-sage-900 dark:text-cream-100">Servicio y modalidad</h3>
                            <p class="text-xs text-ink-500">Elige el tipo de atención y cuántas sesiones</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <label class="label">Servicio</label>
                            <div class="select-wrap">
                                <select name="servicio_id" id="servicio_id" required class="field appearance-none cursor-pointer !pr-10">
                                    <option value="">Elegir servicio...</option>
                                    @foreach($servicios as $srv)
                                        <option value="{{ $srv->id }}" data-modalidades="{{ $srv->modalidades->pluck('id')->join(',') }}" data-precio="{{ $srv->precio }}" data-nombre="{{ $srv->nombre }}">
                                            {{ $srv->nombre }} · S/ {{ number_format($srv->precio, 0) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="label">Cantidad de sesiones</label>
                            <div class="flex items-center gap-2">
                                <button type="button" id="sesMinus" class="stepper-btn" aria-label="Menos">−</button>
                                <input type="number" name="cantidad_sesiones" id="cantidad_sesiones" value="1" min="1" class="field text-center font-bold !text-lg">
                                <button type="button" id="sesPlus" class="stepper-btn" aria-label="Más">+</button>
                            </div>
                        </div>
                        <div>
                            <label class="label">Modalidad</label>
                            <div class="select-wrap">
                                <select name="modalidad_id" id="modalidad_id" required class="field appearance-none cursor-pointer !pr-10">
                                    <option value="">Elegir modalidad...</option>
                                    @foreach($modalidades as $mod)
                                        <option value="{{ $mod->id }}" class="modalidad-option">{{ $mod->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3: Profesional -->
                <div class="surface p-6 md:p-8">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-11 h-11 rounded-2xl bg-sage-600 text-white flex items-center justify-center text-lg font-bold shadow-soft shrink-0">3</div>
                        <div>
                            <h3 class="font-serif text-xl text-sage-900 dark:text-cream-100">Profesional y horario</h3>
                            <p class="text-xs text-ink-500">Selecciona especialista, fecha y una ventana disponible</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <label class="label">Especialista</label>
                            <div class="select-wrap">
                                <select name="especialista_id" id="especialista_id" required class="field appearance-none cursor-pointer !pr-10">
                                    <option value="">Primero elige un servicio...</option>
                                </select>
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="label">Fecha de atención</label>
                            <input type="hidden" id="fetchAvailabilityDate" name="fecha_raw">
                            <div class="rounded-2xl border border-cream-300 dark:border-sage-700 bg-cream-50 dark:bg-sage-950 p-3 w-full max-w-[17rem]">
                                <!-- Estado inicial: aún no hay especialista -->
                                <div id="calendarPlaceholder" class="py-6 text-center">
                                    <div class="w-9 h-9 bg-cream-100 dark:bg-sage-800 rounded-xl flex items-center justify-center text-lg mx-auto mb-2">📅</div>
                                    <p class="text-xs text-ink-500 dark:text-sage-400 font-medium leading-relaxed">Elige un especialista para ver los días con disponibilidad.</p>
                                </div>

                                <!-- Calendario -->
                                <div id="calendarBody" class="hidden">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 id="calMonthLabel" class="font-serif text-sm text-sage-900 dark:text-cream-100 capitalize">—</h4>
                                        <div class="flex gap-1.5">
                                            <button type="button" id="calPrev" aria-label="Mes anterior" class="w-7 h-7 flex items-center justify-center bg-white dark:bg-sage-800 border border-cream-300 dark:border-sage-700 text-ink-600 dark:text-sage-300 rounded-lg hover:bg-sage-500 hover:text-white disabled:opacity-30 disabled:pointer-events-none transition-all">
                                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
                                            </button>
                                            <button type="button" id="calNext" aria-label="Mes siguiente" class="w-7 h-7 flex items-center justify-center bg-white dark:bg-sage-800 border border-cream-300 dark:border-sage-700 text-ink-600 dark:text-sage-300 rounded-lg hover:bg-sage-500 hover:text-white transition-all">
                                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-7 mb-1">
                                        @foreach(['D','L','M','M','J','V','S'] as $d)
                                            <div class="text-[9px] font-bold text-ink-500 dark:text-sage-400 uppercase tracking-wider text-center">{{ $d }}</div>
                                        @endforeach
                                    </div>
                                    <div id="calGrid" class="grid grid-cols-7 gap-0.5"></div>
                                    <div id="calLoading" class="hidden py-5 text-center text-[10px] text-ink-500 dark:text-sage-400 font-semibold uppercase tracking-widest">Cargando disponibilidad…</div>
                                    <div class="mt-3 flex items-center gap-3 text-[9px] font-semibold text-ink-500 dark:text-sage-400">
                                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-sage-500"></span> Con horarios</span>
                                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-cream-300 dark:bg-sage-700"></span> Sin disponibilidad</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="label">Horario disponible</label>
                            <div class="select-wrap">
                                <select name="disponibilidad_id" id="disponibilidad_id" required class="field appearance-none cursor-pointer !pr-10">
                                    <option value="">Elige fecha y profesional...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna resumen (sticky) -->
            <aside class="lg:col-span-1">
                <div class="surface p-7 sticky top-24 overflow-hidden">
                    <div class="flex items-center gap-2 mb-6">
                        <h3 class="font-serif text-xl text-sage-900 dark:text-cream-100">Resumen</h3>
                        <span class="w-1.5 h-1.5 rounded-full bg-sage-500"></span>
                    </div>

                    <dl class="space-y-4 text-sm">
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-ink-500 font-medium flex items-center gap-2">
                                <svg class="w-4 h-4 text-sage-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Paciente
                            </dt>
                            <dd id="sum-paciente" class="font-bold text-sage-900 dark:text-cream-100 text-right">—</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-ink-500 font-medium flex items-center gap-2">
                                <svg class="w-4 h-4 text-sage-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                Servicio
                            </dt>
                            <dd id="sum-servicio" class="font-bold text-sage-900 dark:text-cream-100 text-right">—</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-ink-500 font-medium flex items-center gap-2">
                                <svg class="w-4 h-4 text-sage-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                Sesiones
                            </dt>
                            <dd id="sum-sesiones" class="font-bold text-sage-900 dark:text-cream-100 text-right">1 sesión</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-ink-500 font-medium flex items-center gap-2">
                                <svg class="w-4 h-4 text-sage-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                Modalidad
                            </dt>
                            <dd id="sum-modalidad" class="font-bold text-sage-900 dark:text-cream-100 text-right">—</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-ink-500 font-medium flex items-center gap-2">
                                <svg class="w-4 h-4 text-sage-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Especialista
                            </dt>
                            <dd id="sum-especialista" class="font-bold text-sage-900 dark:text-cream-100 text-right">—</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-ink-500 font-medium flex items-center gap-2">
                                <svg class="w-4 h-4 text-sage-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Fecha y hora
                            </dt>
                            <dd class="font-bold text-sage-900 dark:text-cream-100 text-right">
                                <span id="sum-fecha">—</span>
                                <span id="sum-hora" class="block text-xs text-sand-600 font-semibold"></span>
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-6 pt-6 border-t border-cream-300">
                        <div class="flex items-center justify-between mb-6">
                            <span class="text-xs font-bold uppercase tracking-widest text-ink-500">Total</span>
                            <span id="sum-total" class="font-serif text-3xl text-sage-800 dark:text-cream-100">S/ 0.00</span>
                        </div>

                        <button type="submit" class="btn-primary w-full py-4 text-xs uppercase tracking-[0.15em]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Pagar y agendar
                        </button>
                        <a href="{{ route('receptionist.dashboard') }}" class="block text-center mt-3 text-[11px] font-bold uppercase tracking-widest text-ink-500 hover:text-ink-700 transition-colors py-2">Cancelar</a>

                        <p class="mt-4 text-[10px] text-ink-500 flex items-center justify-center gap-1.5 text-center leading-relaxed">
                            <svg class="w-3.5 h-3.5 text-sage-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Los datos del paciente se guardan de forma segura.
                        </p>
                    </div>
                </div>
            </aside>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const patientSearch = document.getElementById('patientSearch');
    const searchResults = document.getElementById('searchResults');
    const pacienteIdInput = document.getElementById('paciente_id');
    const clearPatientBtn = document.getElementById('clearPatient');

    const serviceSelect = document.getElementById('servicio_id');
    const modalitySelect = document.getElementById('modalidad_id');
    const specialistSelect = document.getElementById('especialista_id');
    const sessionsInput = document.getElementById('cantidad_sesiones');
    const dateInput = document.getElementById('fetchAvailabilityDate');
    const dispSelect = document.getElementById('disponibilidad_id');
    const modalityOptions = document.querySelectorAll('.modalidad-option');
    const nombreInput = document.getElementById('nombre');

    /* ---------- Resumen en vivo ---------- */
    function optText(sel) {
        const o = sel.options[sel.selectedIndex];
        return (sel.value && o) ? o.textContent.trim() : '';
    }
    function fmtDate(s) {
        if (!s) return '';
        const [y, m, d] = s.split('-');
        const meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
        return `${d} ${meses[parseInt(m) - 1]} ${y}`;
    }
    function setTxt(id, val, empty = '—') {
        const el = document.getElementById(id);
        if (el) el.textContent = (val && val.trim()) ? val : empty;
    }
    function updateSummary() {
        setTxt('sum-paciente', nombreInput.value);
        const so = serviceSelect.options[serviceSelect.selectedIndex];
        setTxt('sum-servicio', (serviceSelect.value && so) ? (so.dataset.nombre || so.textContent.trim()) : '');
        const n = parseInt(sessionsInput.value || '1');
        setTxt('sum-sesiones', n + (n === 1 ? ' sesión' : ' sesiones'));
        setTxt('sum-modalidad', optText(modalitySelect));
        setTxt('sum-especialista', optText(specialistSelect));
        setTxt('sum-fecha', fmtDate(dateInput.value));
        const horaTxt = optText(dispSelect);
        setTxt('sum-hora', horaTxt ? horaTxt.split('—')[0].trim() : '', '');
        const precio = (serviceSelect.value && so) ? parseFloat(so.dataset.precio || '0') : 0;
        document.getElementById('sum-total').textContent = 'S/ ' + (precio * (n || 1)).toFixed(2);
    }

    /* ---------- Búsqueda de pacientes ---------- */
    let timeout = null;
    patientSearch.addEventListener('input', (e) => {
        clearTimeout(timeout);
        const query = e.target.value;
        if (query.length < 2) { searchResults.classList.add('hidden'); return; }
        timeout = setTimeout(() => {
            fetch(`{{ route('receptionist.patients.search') }}?q=${query}`)
                .then(res => res.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(p => {
                            const div = document.createElement('div');
                            div.className = 'p-4 hover:bg-sage-50 cursor-pointer border-b border-cream-200 last:border-0 transition-colors flex items-center gap-3';
                            div.innerHTML = `
                                <div class="w-9 h-9 rounded-full bg-sage-100 text-sage-700 flex items-center justify-center font-bold text-xs shrink-0">${(p.nombre || 'P').substring(0,1).toUpperCase()}</div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-sage-900 truncate">${p.nombre}</p>
                                    <p class="text-[10px] text-ink-500 font-semibold uppercase truncate">${p.email}${p.telefono ? ' · ' + p.telefono : ''}</p>
                                </div>`;
                            div.onclick = () => selectPatient(p);
                            searchResults.appendChild(div);
                        });
                        searchResults.classList.remove('hidden');
                    } else {
                        searchResults.innerHTML = '<div class="p-4 text-center text-xs text-ink-500 font-medium">Sin resultados. Puedes registrar un paciente nuevo.</div>';
                        searchResults.classList.remove('hidden');
                    }
                });
        }, 300);
    });
    document.addEventListener('click', (e) => {
        if (!searchResults.contains(e.target) && e.target !== patientSearch) searchResults.classList.add('hidden');
    });

    function selectPatient(p) {
        pacienteIdInput.value = p.id;
        nombreInput.value = p.nombre;
        document.getElementById('email').value = p.email;
        document.getElementById('telefono').value = p.telefono || '';
        searchResults.classList.add('hidden');
        patientSearch.value = p.nombre;
        clearPatientBtn.classList.remove('hidden');
        document.getElementById('nuevo-paciente-fields').classList.remove('hidden');
        updateSummary();
    }

    const showNewPatientBtn = document.getElementById('showNewPatient');
    const nuevoPacienteFields = document.getElementById('nuevo-paciente-fields');

    showNewPatientBtn.onclick = () => {
        pacienteIdInput.value = '';
        ['nombre', 'email', 'telefono'].forEach(id => document.getElementById(id).value = '');
        patientSearch.value = '';
        nuevoPacienteFields.classList.remove('hidden');
        clearPatientBtn.classList.remove('hidden');
        nombreInput.focus();
        updateSummary();
    };
    clearPatientBtn.onclick = () => {
        pacienteIdInput.value = '';
        ['nombre', 'email', 'telefono'].forEach(id => document.getElementById(id).value = '');
        patientSearch.value = '';
        clearPatientBtn.classList.add('hidden');
        nuevoPacienteFields.classList.add('hidden');
        updateSummary();
    };
    nombreInput.addEventListener('input', updateSummary);

    /* ---------- Stepper de sesiones ---------- */
    document.getElementById('sesMinus').onclick = () => {
        sessionsInput.value = Math.max(1, (parseInt(sessionsInput.value || '1') - 1));
        sessionsInput.dispatchEvent(new Event('change'));
    };
    document.getElementById('sesPlus').onclick = () => {
        sessionsInput.value = (parseInt(sessionsInput.value || '1') + 1);
        sessionsInput.dispatchEvent(new Event('change'));
    };
    sessionsInput.addEventListener('input', updateSummary);

    /* ---------- Servicio → modalidades + especialistas ---------- */
    serviceSelect.addEventListener('change', () => {
        const selectedId = serviceSelect.value;
        const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
        if (!selectedId) {
            modalityOptions.forEach(opt => opt.style.display = 'block');
            specialistSelect.innerHTML = '<option value="">Primero elige un servicio...</option>';
            updateSummary();
            return;
        }
        const allowedModalidades = selectedOption.dataset.modalidades.split(',');
        modalityOptions.forEach(opt => { opt.style.display = allowedModalidades.includes(opt.value) ? 'block' : 'none'; });
        modalitySelect.value = '';
        updateSpecialists();
        updateSummary();
    });
    sessionsInput.addEventListener('change', () => { updateSpecialists(); updateSummary(); });
    modalitySelect.addEventListener('change', updateSummary);

    function updateSpecialists() {
        const servicioId = serviceSelect.value;
        const sessions = sessionsInput.value;
        if (!servicioId) return;
        specialistSelect.innerHTML = '<option value="">Buscando especialistas...</option>';
        dispSelect.innerHTML = '<option value="">Elige fecha y profesional...</option>';
        fetch(`/receptionist/api/especialistas/filter?servicio_id=${servicioId}&sessions=${sessions}`)
            .then(res => res.json())
            .then(data => {
                specialistSelect.innerHTML = '<option value="">Elegir profesional...</option>';
                if (data.length === 0) specialistSelect.innerHTML = '<option value="">Sin especialistas disponibles este mes</option>';
                data.forEach(esp => {
                    const opt = document.createElement('option');
                    opt.value = esp.id;
                    opt.textContent = `${esp.nombre} - ${esp.especialidad}`;
                    specialistSelect.appendChild(opt);
                });
                updateSummary();
            })
            .catch(err => { console.error(err); specialistSelect.innerHTML = '<option value="">Error al cargar especialistas</option>'; });
    }

    function updateAvailability() {
        const espId = specialistSelect.value;
        const fecha = dateInput.value;
        const sessions = sessionsInput.value;
        const servicioId = serviceSelect.value;
        if (!espId || !fecha || !servicioId) {
            dispSelect.innerHTML = '<option value="">Elige servicio, fecha y profesional...</option>';
            updateSummary();
            return;
        }
        dispSelect.innerHTML = '<option value="">Cargando horarios...</option>';
        fetch(`/receptionist/api/availability/search?especialista_id=${espId}&fecha=${fecha}&sessions=${sessions}&servicio_id=${servicioId}`)
            .then(res => res.json())
            .then(data => {
                dispSelect.innerHTML = '<option value="">Selecciona un horario</option>';
                if (data.length > 0) {
                    data.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.id;
                        opt.textContent = `${d.hora_inicio} — Sesión de ${d.duracion_min || 60}min`;
                        dispSelect.appendChild(opt);
                    });
                } else { dispSelect.innerHTML = '<option value="">No hay horarios para este día</option>'; }
                updateSummary();
            });
    }

    specialistSelect.addEventListener('change', () => { updateAvailability(); updateSummary(); });
    dateInput.addEventListener('change', () => { updateAvailability(); updateSummary(); });
    dispSelect.addEventListener('change', updateSummary);

    /* ---------- Calendario de disponibilidad (solo días con horarios) ---------- */
    const calPlaceholder = document.getElementById('calendarPlaceholder');
    const calBody = document.getElementById('calendarBody');
    const calGrid = document.getElementById('calGrid');
    const calMonthLabel = document.getElementById('calMonthLabel');
    const calLoading = document.getElementById('calLoading');
    const calPrev = document.getElementById('calPrev');
    const calNext = document.getElementById('calNext');

    const calMonthNames = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];

    function startOfMonth(d) { return new Date(d.getFullYear(), d.getMonth(), 1); }
    function ymd(d) { return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`; }

    const calState = { ref: startOfMonth(new Date()), available: new Set(), selected: null };

    function resetCalendar() {
        calState.selected = null;
        calState.available = new Set();
        calState.ref = startOfMonth(new Date());
        dateInput.value = '';
        dispSelect.innerHTML = '<option value="">Elige fecha y profesional...</option>';
        calBody.classList.add('hidden');
        calPlaceholder.classList.remove('hidden');
        updateSummary();
    }

    function loadCalendarMonth() {
        const esp = specialistSelect.value;
        if (!esp) { resetCalendar(); return; }
        calPlaceholder.classList.add('hidden');
        calBody.classList.remove('hidden');
        calGrid.innerHTML = '';
        calLoading.classList.remove('hidden');
        const mes = ymd(calState.ref);
        fetch(`/receptionist/api/availability/dates?especialista_id=${esp}&mes=${mes}&sessions=${sessionsInput.value}&servicio_id=${serviceSelect.value}`)
            .then(r => r.json())
            .then(data => {
                calState.available = new Set(data.dates || []);
                calLoading.classList.add('hidden');
                renderCalendar();
            })
            .catch(() => {
                calLoading.classList.add('hidden');
                calGrid.innerHTML = '<p class="col-span-7 py-6 text-center text-xs text-sand-600 font-semibold">Error al cargar la disponibilidad.</p>';
            });
    }

    function renderCalendar() {
        const ref = calState.ref;
        calMonthLabel.textContent = `${calMonthNames[ref.getMonth()]} ${ref.getFullYear()}`;

        // No permitir navegar a meses ya pasados.
        calPrev.disabled = startOfMonth(ref) <= startOfMonth(new Date());

        const first = startOfMonth(ref);
        const start = new Date(first);
        start.setDate(first.getDate() - first.getDay()); // retroceder al domingo
        const todayStr = ymd(new Date());

        let html = '';
        for (let i = 0; i < 42; i++) {
            const d = new Date(start);
            d.setDate(start.getDate() + i);
            const dStr = ymd(d);
            const inMonth = d.getMonth() === ref.getMonth();
            const isAvail = calState.available.has(dStr);
            const isPast = dStr < todayStr;
            const isSelected = calState.selected === dStr;
            const isToday = dStr === todayStr;
            const disabled = !isAvail || isPast;

            let cls = 'aspect-square rounded-lg flex items-center justify-center text-[11px] font-bold transition-all relative ';
            cls += inMonth ? 'text-ink-700 dark:text-sage-200 ' : 'text-cream-400 dark:text-sage-700 ';
            if (isSelected) cls += 'bg-sage-500 !text-white shadow-md scale-105 z-10 ';
            else if (isAvail && !isPast) cls += 'bg-sage-50 dark:bg-sage-900/50 hover:bg-sage-100 hover:text-sage-700 cursor-pointer ';
            if (disabled) cls += 'opacity-30 cursor-not-allowed ';

            html += `<button type="button" data-date="${dStr}" ${disabled ? 'disabled' : ''} class="${cls}">
                <span>${d.getDate()}</span>
                ${(isAvail && !isPast && !isSelected) ? '<span class="absolute top-1 right-1 w-1 h-1 rounded-full bg-sage-500"></span>' : ''}
                ${(isToday && !isSelected) ? '<span class="absolute bottom-0.5 w-1 h-1 rounded-full bg-sand-400"></span>' : ''}
            </button>`;
        }
        calGrid.innerHTML = html;
    }

    calGrid.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-date]');
        if (!btn || btn.disabled) return;
        calState.selected = btn.dataset.date;
        dateInput.value = btn.dataset.date;
        renderCalendar();
        dateInput.dispatchEvent(new Event('change')); // dispara updateAvailability + updateSummary
    });

    calPrev.onclick = () => {
        if (calPrev.disabled) return;
        calState.ref = new Date(calState.ref.getFullYear(), calState.ref.getMonth() - 1, 1);
        loadCalendarMonth();
    };
    calNext.onclick = () => {
        calState.ref = new Date(calState.ref.getFullYear(), calState.ref.getMonth() + 1, 1);
        loadCalendarMonth();
    };

    // Al elegir un especialista, mostrar su calendario del mes actual.
    specialistSelect.addEventListener('change', () => {
        calState.selected = null;
        dateInput.value = '';
        calState.ref = startOfMonth(new Date());
        loadCalendarMonth();
    });
    // Cambiar servicio o cantidad de sesiones reconstruye la lista de especialistas: reiniciar calendario.
    serviceSelect.addEventListener('change', resetCalendar);
    sessionsInput.addEventListener('change', resetCalendar);

    updateSummary();
</script>
@endpush
