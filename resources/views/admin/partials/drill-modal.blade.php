@php
    // Uso: @include('admin.partials.drill-modal', ['period' => $period])
    // Los KPIs disparan la Lupa con:
    //   @click="$dispatch('open-drill', { metric: 'active_patients', title: 'Pacientes activos' })"
    $drillPeriod = $period ?? 'month';
@endphp

<div
    x-data="{
        open: false,
        loading: false,
        title: '',
        items: [],
        count: 0,
        error: false,
        base: @js(url('admin/reportes/drill')),
        period: @js($drillPeriod),
        async load(metric, title) {
            this.title = title;
            this.open = true;
            this.loading = true;
            this.error = false;
            this.items = [];
            this.count = 0;
            try {
                const res = await fetch(`${this.base}/${metric}?period=${this.period}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) throw new Error('bad response');
                const data = await res.json();
                this.items = data.items ?? [];
                this.count = data.count ?? this.items.length;
            } catch (e) {
                this.error = true;
            } finally {
                this.loading = false;
            }
        },
        close() { this.open = false; }
    }"
    x-on:open-drill.window="load($event.detail.metric, $event.detail.title)"
    x-on:keydown.escape.window="close()"
    x-cloak
>
    <div x-show="open" x-transition.opacity class="fixed inset-0 z-[80] bg-ink-900/40 backdrop-blur-sm" @click="close()"></div>

    <div x-show="open" x-transition class="fixed inset-0 z-[90] flex items-start justify-center p-4 sm:p-8 overflow-y-auto pointer-events-none">
        <div class="surface !rounded-3xl w-full max-w-2xl mt-8 pointer-events-auto overflow-hidden" @click.stop>
            <div class="flex items-center justify-between p-6 border-b border-cream-300">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-sage-500">Detalle</p>
                    <h3 class="font-serif text-xl text-sage-900 mt-0.5" x-text="title"></h3>
                </div>
                <div class="flex items-center gap-4">
                    <span x-show="!loading && !error" class="chip-sage" x-text="count + ' registros'"></span>
                    <button @click="close()" class="p-2 text-ink-400 hover:text-sand-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <div class="max-h-[65vh] overflow-y-auto">
                <div x-show="loading" class="py-16 text-center text-ink-500 text-sm">Cargando…</div>
                <div x-show="error" class="py-16 text-center text-sand-700 text-sm">No se pudo cargar el detalle.</div>
                <div x-show="!loading && !error && items.length === 0" class="py-16 text-center text-ink-500 text-sm">Sin registros para este período.</div>

                <ul x-show="!loading && !error && items.length > 0" class="divide-y divide-cream-200">
                    <template x-for="(item, i) in items" :key="i">
                        <li class="flex items-center justify-between gap-4 px-6 py-3.5 hover:bg-cream-100/60 transition-colors">
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-sage-900 truncate" x-text="item.titulo"></p>
                                <p class="text-xs text-ink-500 truncate" x-text="item.subtitulo"></p>
                            </div>
                            <span class="text-xs font-semibold text-ink-600 whitespace-nowrap" x-text="item.extra"></span>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
    </div>
</div>
