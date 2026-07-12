@php $categoria = $categoria ?? null; @endphp

<div class="surface p-8 md:p-12 space-y-8">
    <div class="flex items-center gap-4">
        <span class="text-xs font-bold uppercase tracking-[0.3em] text-ink-500">Detalles de la categoría</span>
        <div class="h-px bg-cream-300 flex-1"></div>
    </div>

    <div>
        <label class="label">Nombre de la categoría</label>
        <input type="text" name="nombre" value="{{ old('nombre', $categoria->nombre ?? '') }}" required placeholder="Ej: Psicología" class="field">
        @error('nombre') <p class="text-sand-700 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="label">Descripción</label>
        <textarea name="descripcion" rows="3" class="field resize-none" placeholder="Breve descripción de la categoría (opcional)">{{ old('descripcion', $categoria->descripcion ?? '') }}</textarea>
        @error('descripcion') <p class="text-sand-700 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
    </div>

    <label class="flex items-center gap-3 cursor-pointer select-none">
        <input type="hidden" name="esta_activo" value="0">
        <input type="checkbox" name="esta_activo" value="1" {{ old('esta_activo', $categoria->esta_activo ?? true) ? 'checked' : '' }} class="peer sr-only">
        <span class="relative w-11 h-6 bg-cream-300 rounded-full transition-colors peer-checked:bg-sage-600 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-transform peer-checked:after:translate-x-5"></span>
        <span class="text-sm font-bold text-ink-600">Categoría activa</span>
    </label>
</div>

<div class="pt-2">
    <button type="submit" class="{{ $submitClass ?? 'btn-primary' }} w-full py-5 text-xs uppercase tracking-[0.2em]">
        {{ $submitLabel ?? 'Guardar' }}
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
    </button>
</div>
