@extends('components.layouts.admin')

@php $active = 'servicios'; @endphp

@section('content')
<div class="max-w-4xl mx-auto pb-20">
    <div class="mb-10">
        <a href="{{ route('admin.servicios.index') }}" class="inline-flex items-center gap-2 text-ink-500 hover:text-sage-600 font-bold text-xs uppercase tracking-widest transition-colors mb-6">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m15 18-6-6 6-6"/></svg>
            Volver al listado
        </a>
        <h1 class="font-serif text-3xl md:text-4xl text-sage-900 dark:text-cream-100">Editar servicio</h1>
        <p class="text-ink-500 dark:text-sage-300 mt-1">Actualiza las condiciones de {{ $servicio->nombre }}.</p>
    </div>

    <form action="{{ route('admin.servicios.update', $servicio->id) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')
        @include('admin.servicios._form', ['submitLabel' => 'Actualizar', 'submitClass' => 'btn-accent'])
    </form>
</div>
@endsection
