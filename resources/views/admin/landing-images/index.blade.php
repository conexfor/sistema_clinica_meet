@extends('components.layouts.admin')

@php $active = 'landing-images'; @endphp

@section('content')
<div class="max-w-6xl mx-auto pb-20">
    <div class="mb-10">
        <h1 class="font-serif text-3xl md:text-4xl text-sage-900 dark:text-cream-100">Imágenes de la página principal</h1>
        <p class="text-ink-500 dark:text-sage-300 mt-1">Fotografías de cada sección de la landing pública. Si un espacio queda vacío se usa una imagen por defecto.</p>
    </div>

    @if(session('success'))
        <div class="alert-auto-dismiss mb-6 flex items-center gap-3 rounded-2xl border border-sage-200 bg-sage-50 px-5 py-4 text-sage-800">
            <svg class="w-5 h-5 flex-shrink-0 text-sage-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ route('admin.landing-images.update') }}" method="POST" enctype="multipart/form-data" class="space-y-12">
        @csrf

        @foreach(collect(\App\Models\LandingImage::SLOTS)->groupBy('group', true) as $group => $slots)
            <section>
                <h2 class="eyebrow mb-5">{{ $group }}</h2>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($slots as $position => $slot)
                        @php
                            $record = $images[$position] ?? null;
                            $resolved = \App\Models\LandingImage::resolve($images, $position);
                            $isOwn = (bool) $record?->url;
                        @endphp
                        <div class="surface p-6 rounded-3xl">
                            <div class="flex items-baseline justify-between gap-3 mb-4">
                                <h3 class="font-semibold text-sage-900">{{ $slot['label'] }}</h3>
                                <span class="text-xs text-ink-500 whitespace-nowrap">{{ $slot['hint'] }}</span>
                            </div>
                            <div class="relative rounded-2xl overflow-hidden ring-1 ring-cream-300 aspect-[4/3] mb-4 bg-cream-50">
                                <img src="{{ $resolved['url'] }}" alt="{{ $resolved['alt'] }}" class="w-full h-full object-cover {{ $isOwn ? '' : 'opacity-60' }}">
                                @unless($isOwn)
                                    <span class="absolute top-3 left-3 chip-sand">Imagen por defecto</span>
                                @endunless
                            </div>
                            <label class="block text-sm font-semibold text-ink-700 mb-2">Subir imagen</label>
                            <input type="file" name="images[{{ $position }}]" accept="image/*" class="w-full text-sm text-ink-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-sage-50 file:text-sage-700 hover:file:bg-sage-100 cursor-pointer">
                            @error("images.{$position}") <p class="text-sand-700 text-xs mt-2 font-bold">{{ $message }}</p> @enderror

                            <label class="block text-sm font-semibold text-ink-700 mt-4 mb-2">Texto alternativo</label>
                            <input type="text" name="alts[{{ $position }}]" value="{{ old('alts.' . $position, $record->alt_text ?? '') }}" class="field" placeholder="{{ $slot['alt'] }}">
                            @error("alts.{$position}") <p class="text-sand-700 text-xs mt-2 font-bold">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        <button type="submit" class="btn-accent w-full py-5 text-xs uppercase tracking-[0.2em]">
            Guardar cambios
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </button>
    </form>
</div>
@endsection
