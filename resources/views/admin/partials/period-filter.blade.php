@php
    // Uso: @include('admin.partials.period-filter', ['route' => 'admin.reportes.finanzas', 'period' => $period])
    $options = \App\Services\Admin\ReportPeriod::OPTIONS;
    $current = $period ?? 'month';
    $startDate = request('fecha_inicio', request('start_date', ''));
    $endDate = request('fecha_fin', request('end_date', ''));
    $currentLabel = $current === 'custom' && $startDate && $endDate
        ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y')
        : ($options[$current] ?? $options['month']);
@endphp

<div x-data="{
        open: false,
        selected: @js($currentLabel),
        options: @js(collect($options)->map(fn($label, $value) => ['label' => $label, 'value' => $value])->values()),
        base: @js(route($route)),
        select(o) { this.selected = o.label; this.open = false; window.location.href = this.base + '?period=' + o.value; }
    }" class="flex flex-wrap items-center gap-3">
    <div class="relative">
        <button @click="open = !open" class="flex items-center gap-2 px-5 py-2.5 bg-white border border-cream-300 rounded-2xl text-sm font-semibold text-ink-700 hover:border-sage-400 transition-all shadow-soft">
            <svg class="w-4 h-4 text-sage-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span x-text="selected"></span>
            <svg class="w-4 h-4 text-ink-500 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="open" x-cloak @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 surface !rounded-2xl z-[60] p-2">
            <template x-for="option in options" :key="option.value">
                <button @click="select(option)" class="w-full text-left px-4 py-2.5 text-[13px] font-semibold text-ink-600 hover:bg-sage-50 hover:text-sage-700 rounded-xl transition-colors" :class="{'bg-sage-50 text-sage-700': selected === option.label}" x-text="option.label"></button>
            </template>
        </div>
    </div>

    <form method="GET" action="{{ route($route) }}" class="flex flex-wrap items-center gap-2 bg-white border border-cream-300 rounded-2xl p-2 shadow-soft">
        <input type="hidden" name="period" value="custom">
        <label class="text-[11px] font-bold uppercase tracking-[0.2em] text-ink-500">Desde</label>
        <input type="date" name="fecha_inicio" value="{{ $startDate }}" class="rounded-xl border border-cream-300 bg-cream-50 px-3 py-2 text-sm text-ink-700 focus:border-sage-400 focus:outline-none">
        <label class="text-[11px] font-bold uppercase tracking-[0.2em] text-ink-500">Hasta</label>
        <input type="date" name="fecha_fin" value="{{ $endDate }}" class="rounded-xl border border-cream-300 bg-cream-50 px-3 py-2 text-sm text-ink-700 focus:border-sage-400 focus:outline-none">
        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-sage-600 px-3 py-2 text-[11px] font-bold uppercase tracking-[0.18em] text-white hover:bg-sage-700 transition-colors">Aplicar</button>
        @if($startDate || $endDate)
            <a href="{{ route($route, ['period' => 'month']) }}" class="inline-flex items-center rounded-xl border border-cream-300 px-3 py-2 text-[11px] font-bold uppercase tracking-[0.18em] text-ink-600 hover:border-sage-400 transition-colors">Limpiar</a>
        @endif
    </form>
</div>
