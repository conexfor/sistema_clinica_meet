@extends('components.layouts.app')

@section('content')
<div class="max-w-2xl mx-auto pb-16">
    <div class="surface overflow-hidden">
        <div class="p-8 text-white relative bg-gradient-to-br from-sage-700 to-sage-900">
            <div class="absolute top-0 right-0 w-48 h-48 bg-sand-300/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <h4 class="font-serif text-2xl relative z-10 flex items-center gap-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                Crear reunión de Google Meet
            </h4>
        </div>
        <div class="p-8">
            @if (session('error'))
                <div class="mb-6 p-4 bg-sand-50 border border-sand-300 rounded-2xl text-sand-800 text-sm font-medium">{{ session('error') }}</div>
            @endif

            @if (session('meeting_url'))
                <div class="mb-6 p-5 bg-sage-50 border border-sage-200 rounded-2xl">
                    <label class="label !text-sage-700">Enlace de la reunión generada:</label>
                    <div class="flex gap-2">
                        <input type="text" id="meetingUrl" class="field" value="{{ session('meeting_url') }}" readonly>
                        <button class="btn-primary !px-5" type="button" onclick="copyMeetingLink()">Copiar</button>
                    </div>
                    <a href="{{ session('meeting_url') }}" target="_blank" class="btn-outline w-full mt-3">Unirse a la reunión</a>
                </div>
            @endif

            @if (!$isGoogleConnected)
                <div class="text-center py-10">
                    <div class="w-16 h-16 bg-sand-100 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-6">🔗</div>
                    <h3 class="font-serif text-2xl text-sage-900 dark:text-cream-100 mb-2">Conecta tu cuenta de Google</h3>
                    <p class="text-ink-500 mb-6 max-w-sm mx-auto">Para crear reuniones de Google Meet necesitamos permiso para acceder a tu calendario.</p>
                    <a href="{{ route('google.auth') }}" class="btn-primary px-8 py-4">Conectar con Google</a>
                </div>
            @else
                <form action="{{ route('meetings.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <label for="title" class="label">Título de la reunión</label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}" placeholder="Ej: Consulta psicológica" required class="field">
                        @error('title') <p class="text-sand-700 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="description" class="label">Descripción (opcional)</label>
                        <textarea id="description" name="description" rows="3" placeholder="Detalles de la sesión..." class="field resize-none">{{ old('description') }}</textarea>
                        @error('description') <p class="text-sand-700 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="start_time" class="label">Inicio</label>
                            <input type="datetime-local" id="start_time" name="start_time" value="{{ old('start_time') }}" required class="field">
                            @error('start_time') <p class="text-sand-700 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="end_time" class="label">Fin</label>
                            <input type="datetime-local" id="end_time" name="end_time" value="{{ old('end_time') }}" required class="field">
                            @error('end_time') <p class="text-sand-700 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <button type="submit" class="btn-primary w-full py-4">Generar reunión de Meet</button>
                </form>
                <div class="mt-4 text-center">
                    <small class="text-ink-500 text-xs font-medium">
                        <span class="text-sage-600">✔</span> Cuenta de Google conectada.
                        <a href="{{ route('google.auth') }}" class="ml-2 text-sand-600 hover:underline">Reconectar</a>
                    </small>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function copyMeetingLink() {
    var copyText = document.getElementById("meetingUrl");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    alert("¡Enlace copiado al portapapeles!");
}
</script>
@endsection
