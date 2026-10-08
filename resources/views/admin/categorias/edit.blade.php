@extends('components.layouts.admin')

@php $active = 'categorias'; @endphp

@section('content')
<div class="max-w-2xl mx-auto pb-20">
    <div class="mb-10">
        <a href="{{ route('admin.categorias.index') }}" class="inline-flex items-center gap-2 text-ink-500 hover:text-sage-600 font-bold text-xs uppercase tracking-widest transition-colors mb-6">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m15 18-6-6 6-6"/></svg>
            Volver al listado
        </a>
        <h1 class="font-serif text-3xl md:text-4xl text-sage-900 dark:text-cream-100">Editar categoría</h1>
        <p class="text-ink-500 dark:text-sage-300 mt-1">Actualiza los datos de esta categoría.</p>
    </div>

    <form action="{{ route('admin.categorias.update', $categoria->id) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')
        @include('admin.categorias._form', ['submitLabel' => 'Actualizar', 'submitClass' => 'btn-primary'])
    </form>
</div>
@endsection
