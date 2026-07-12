{{-- Widget flotante para reportar incidencias de soporte (paciente/doctor). --}}
<div x-data="{ open: false, submitting: false }" x-on:open-support.window="open = true; submitting = false" x-cloak>
    <!-- Botón flotante -->
    <button @click="open = true" type="button"
            class="fixed bottom-6 right-6 z-[70] flex items-center gap-2 px-5 py-3 rounded-full bg-sage-700 text-white shadow-calm hover:bg-sage-800 transition-colors text-sm font-bold">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        <span class="hidden sm:inline">Reportar problema</span>
    </button>

    <!-- Overlay -->
    <div x-show="open" x-transition.opacity class="fixed inset-0 z-[80] bg-ink-900/40 backdrop-blur-sm" @click="open = false"></div>

    <!-- Modal -->
    <div x-show="open" x-transition class="fixed inset-0 z-[90] flex items-center justify-center p-4 pointer-events-none">
        <div class="surface !rounded-3xl w-full max-w-lg pointer-events-auto overflow-hidden" @click.stop>
            <div class="flex items-center justify-between p-6 border-b border-cream-300">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-sage-500">Soporte</p>
                    <h3 class="font-serif text-xl text-sage-900 mt-0.5">Reportar un problema</h3>
                </div>
                <button @click="open = false" type="button" class="p-2 text-ink-400 hover:text-sand-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('soporte.store') }}" method="POST" class="p-6 space-y-4"
                  @submit="submitting ? $event.preventDefault() : (submitting = true)">
                @csrf
                <div>
                    <label class="label">Tipo de problema</label>
                    <select name="categoria" required class="field appearance-none">
                        @foreach(\App\Models\TicketSoporte::CATEGORIAS as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Asunto</label>
                    <input type="text" name="asunto" required maxlength="150" placeholder="Ej: No puedo entrar al Meet" class="field">
                </div>
                <div>
                    <label class="label">Descripción</label>
                    <textarea name="mensaje" rows="4" required maxlength="2000" placeholder="Cuéntanos qué está pasando…" class="field resize-none"></textarea>
                </div>
                <button type="submit" :disabled="submitting"
                        class="btn-primary w-full py-3 disabled:opacity-60 disabled:cursor-not-allowed">
                    <span x-show="!submitting">Enviar reporte</span>
                    <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Enviando…
                    </span>
                </button>
            </form>
        </div>
    </div>
</div>
