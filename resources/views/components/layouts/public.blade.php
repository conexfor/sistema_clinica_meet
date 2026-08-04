<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<script>
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
</script>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'Sia') . ' · Clínica Psicológica' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:ital,wght@0,300..800;1,300..800&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    @stack('styles')
</head>
<body
    x-data="{ scrolled: false }"
    @scroll.window="scrolled = (window.pageYOffset > 40)"
    class="bg-mesh text-ink-900 min-h-screen flex flex-col antialiased">

    @include('partials.site-header', ['current' => $current ?? null])

    <main class="flex-1 w-full">
        @if (session('success'))
            <div class="max-w-7xl mx-auto px-6 pt-6">
                <div class="alert-auto-dismiss flex items-center gap-3 rounded-2xl border border-sage-200 bg-sage-50 px-5 py-4 text-sage-800">
                    <svg class="w-5 h-5 flex-shrink-0 text-sage-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    @include('partials.site-footer')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.alert-auto-dismiss').forEach(function(alert) {
                setTimeout(function() {
                    alert.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-16px)';
                    setTimeout(function() { alert.remove(); }, 600);
                }, 4000);
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
