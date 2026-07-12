@php $servicio = $servicio ?? null; @endphp

<div class="surface p-8 md:p-12 space-y-10">
    <!-- Detalles -->
    <section class="space-y-6">
        <div class="flex items-center gap-4">
            <span class="text-xs font-bold uppercase tracking-[0.3em] text-ink-500">Detalles del servicio</span>
            <div class="h-px bg-cream-300 flex-1"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="label">Nombre del servicio</label>
                <input type="text" name="nombre" value="{{ old('nombre', $servicio->nombre ?? '') }}" required placeholder="Ej: Terapia de pareja" class="field">
                @error('nombre') <p class="text-sand-700 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Categoría</label>
                <select name="categoria_id" required class="field appearance-none">
                    <option value="" disabled {{ old('categoria_id', $servicio->categoria_id ?? '') === '' ? 'selected' : '' }}>Selecciona una categoría…</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ (string) old('categoria_id', $servicio->categoria_id ?? '') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
                @error('categoria_id') <p class="text-sand-700 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                @if($categorias->isEmpty())
                    <p class="text-xs mt-1.5 text-ink-500">No hay categorías activas. <a href="{{ route('admin.categorias.create') }}" class="font-bold text-sage-600 hover:underline">Crea una categoría</a> primero.</p>
                @endif
            </div>
        </div>
        <div>
            <label class="label">Descripción</label>
            <textarea name="descripcion" rows="3" class="field resize-none" placeholder="¿En qué consiste este servicio?">{{ old('descripcion', $servicio->descripcion ?? '') }}</textarea>
            @error('descripcion') <p class="text-sand-700 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
        </div>
    </section>

    <!-- Precio y tiempo -->
    <section class="space-y-6">
        <div class="flex items-center gap-4">
            <span class="text-xs font-bold uppercase tracking-[0.3em] text-ink-500">Precio y tiempo</span>
            <div class="h-px bg-cream-300 flex-1"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="label">Precio sugerido (S/)</label>
                <input type="number" name="precio" value="{{ old('precio', $servicio->precio ?? '') }}" required placeholder="0.00" class="field">
                @error('precio') <p class="text-sand-700 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Duración (minutos)</label>
                <div class="grid grid-cols-3 gap-3">
                    @foreach($duraciones as $dur)
                        <label class="cursor-pointer relative group">
                            <input type="radio" name="duracion" value="{{ $dur }}" {{ old('duracion', $servicio->duracion ?? 60) == $dur ? 'checked' : '' }} class="peer hidden">
                            <div class="py-4 text-center bg-cream-100 border border-cream-300 rounded-2xl font-bold text-sm text-ink-500 peer-checked:bg-sage-600 peer-checked:text-white peer-checked:border-sage-600 transition-all duration-300">{{ $dur }}'</div>
                        </label>
                    @endforeach
                </div>
                @error('duracion') <p class="text-sand-700 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <!-- Modalidades -->
    <section class="space-y-6">
        <div class="flex items-center gap-4">
            <span class="text-xs font-bold uppercase tracking-[0.3em] text-ink-500">Modalidades de atención</span>
            <div class="h-px bg-cream-300 flex-1"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($modalidades as $mod)
                <label class="relative flex items-center p-6 cursor-pointer bg-cream-100/60 border border-cream-300 rounded-3xl hover:bg-white hover:border-sage-300 transition-all group">
                    <input type="checkbox" name="modalidades[]" value="{{ $mod->id }}"
                        {{ (is_array(old('modalidades')) && in_array($mod->id, old('modalidades'))) || (!old('modalidades') && $servicio && $servicio->modalidades->contains($mod->id)) ? 'checked' : '' }}
                        class="peer hidden">
                    <div class="relative z-10 w-8 h-8 border-2 border-cream-400 rounded-xl flex items-center justify-center bg-white peer-checked:bg-sage-600 peer-checked:border-sage-600 shadow-sm transition-all mr-6">
                        <svg class="w-5 h-5 text-white scale-0 peer-checked:scale-100 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    </div>
                    <div class="relative z-10 flex-1">
                        <span class="block text-base font-bold text-ink-700 dark:text-cream-100 group-hover:text-sage-900">{{ $mod->nombre }}</span>
                        <span class="block text-xs font-medium text-ink-500 leading-tight mt-0.5">{{ $mod->descripcion }}</span>
                    </div>
                </label>
            @endforeach
        </div>
        @error('modalidades') <p class="text-sand-700 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
    </section>
</div>

<div class="pt-2">
    <button type="submit" class="{{ $submitClass ?? 'btn-primary' }} w-full py-5 text-xs uppercase tracking-[0.2em]">
        {{ $submitLabel ?? 'Guardar' }}
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
    </button>
</div>
