<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Galería Multimedia – {{ config('clinic.name') }} Clínica Psicológica</title>
    <meta name="description" content="Conoce nuestras instalaciones, equipo y recursos multimedia de {{ config('clinic.name') }} Clínica Psicológica.">

    {{-- Google Fonts: mismos que el dashboard principal --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* ─── Tipografía base ─── */
        body          { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f7f5f0; color: #2e3a33; overflow-x: hidden; }
        .plus-jakarta { font-family: 'Fraunces', serif; }

        /* ─── Navbar glassmorphic ─── */
        .glass-nav { background: rgba(252,251,248,0); backdrop-filter: blur(0px); transition: all .4s cubic-bezier(.4,0,.2,1); }
        .glass-nav.scrolled { background: rgba(252,251,248,.9); backdrop-filter: blur(12px); border-color: #e3ded2; box-shadow: 0 25px 50px -12px rgba(34,51,61,.10); }

        /* ─── Sombras ─── */
        .premium-shadow { box-shadow: 0 20px 40px -15px rgba(34,51,61,.08); }
        .card-shadow    { box-shadow: 0 8px 32px -8px rgba(34,51,61,.12); }

        /* ─── Botón salvia sólido ─── */
        .solid-blue-btn { background-color: #496755 !important; color: #fff !important; border: 0 !important; }
        .solid-blue-btn:hover { background-color: #3c5446 !important; transform: translateY(-2px); box-shadow: 0 16px 24px -6px rgba(74,103,85,.4); }

        /* ─── Fondo animado con cuadrícula radial ─── */
        .bg-grid-pattern { background-image: radial-gradient(#dfe8dd 1px, transparent 1px); background-size: 32px 32px; }
        .bg-mesh-gradient {
            background-color: #f7f5f0;
            background-image:
                radial-gradient(at 0% 0%, rgba(90,125,106,.08) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(201,169,140,.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(90,125,106,.08) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(201,169,140,.08) 0px, transparent 50%);
        }
        .animate-blob { animation: blob 10s infinite alternate; }
        .animation-delay-2000 { animation-delay: 2s; }
        @keyframes blob {
            0%   { transform: translate(0,0) scale(1); }
            33%  { transform: translate(30px,-50px) scale(1.1); }
            66%  { transform: translate(-20px,20px) scale(.9); }
            100% { transform: translate(0,0) scale(1); }
        }

        /* ─── Animaciones de entrada ─── */
        [x-cloak] { display: none !important; }
        .reveal { opacity: 0; transform: translateY(28px); transition: all .8s cubic-bezier(.2,.8,.2,1); }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        /* ─── Tarjeta de media (foto / video) ─── */
        .media-card { transition: transform .4s cubic-bezier(.4,0,.2,1), box-shadow .4s; border-radius: 1.5rem; overflow: hidden; cursor: pointer; }
        .media-card:hover { transform: translateY(-6px); box-shadow: 0 32px 56px -12px rgba(0,0,0,.14); }
        .media-card img,
        .media-card video { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .6s cubic-bezier(.4,0,.2,1); }
        .media-card:hover img,
        .media-card:hover video { transform: scale(1.06); }

        /* ─── Overlay de tarjeta ─── */
        .card-overlay { position: absolute; inset: 0; background: linear-gradient(to top, rgba(15,23,42,.85) 0%, transparent 60%); opacity: 0; transition: opacity .3s; }
        .media-card:hover .card-overlay { opacity: 1; }

        /* ─── Chip de categoría ─── */
        .chip { display: inline-flex; align-items: center; gap: .375rem; padding: .35rem .85rem; border-radius: 9999px; font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; cursor: pointer; transition: all .2s; }
        .chip-active   { background-color: #496755; color: #fff; box-shadow: 0 4px 14px rgba(74,103,85,.35); }
        .chip-inactive { background-color: #fff; color: #6b756c; border: 1.5px solid #e3ded2; }
        .chip-inactive:hover { border-color: #9dbaa1; color: #496755; }

        /* ─── Zona de upload drag-and-drop ─── */
        .drop-zone { border: 2.5px dashed #cfc8b8; border-radius: 1.5rem; background: #fff; transition: all .25s; }
        .drop-zone.drag-over { border-color: #5a7d6a; background: #f2f6f2; }

        /* ─── Lightbox backdrop ─── */
        .lightbox-bg { background: rgba(19,30,24,.92); backdrop-filter: blur(16px); }

        /* ─── Progress bar de upload ─── */
        @keyframes progress { from{width:0} to{width:var(--pct)} }
        .progress-bar { animation: progress .8s ease forwards; }

        /* ─── Badge tipo media ─── */
        .badge-video { background: rgba(74,103,85,.85); color:#fff; }
        .badge-photo { background: rgba(255,255,255,.85); color:#2e3a33; }
    </style>
</head>

{{-- ── Componente principal Alpine.js ── --}}
<body
    x-data="{
        /* ---- Estado de navegación ---- */
        scrolled: false,

        /* ---- Filtro activo ---- */
        activeFilter: 'todos',
        filters: [
            { key:'todos',          label:'Todos' },
            { key:'instalaciones',  label:'Instalaciones' },
            { key:'equipo',         label:'Equipo' },
            { key:'recursos',       label:'Recursos' },
            { key:'videos',         label:'Videos' }
        ],

        /* ---- Lightbox ---- */
        lightboxOpen: false,
        lightboxItem: null,

        /* ---- Upload ---- */
        uploadOpen: false,
        dragOver: false,
        files: [],
        uploading: false,
        uploadDone: false,

        /* ---- Galería de items (demo) ---- */
        items: [
            { id:1,  type:'photo', cat:'instalaciones', src:'https://images.unsplash.com/photo-1631049552057-403cdb8f0658?w=800&auto=format&fit=crop', title:'Sala de Espera Principal',  size:'large'  },
            { id:2,  type:'photo', cat:'equipo',        src:'https://images.unsplash.com/photo-1551836022-d5d88e9218df?w=800&auto=format&fit=crop', title:'Dra. Ana Quiroga – Psicóloga Clínica', size:'normal' },
            { id:3,  type:'video', cat:'videos',        src:'https://www.w3schools.com/html/mov_bbb.mp4',                                            title:'Recorrido Virtual {{ config('clinic.name') }}',           size:'normal' },
            { id:4,  type:'photo', cat:'instalaciones', src:'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=800&auto=format&fit=crop', title:'Consultorio Terapéutico A',       size:'normal' },
            { id:5,  type:'photo', cat:'recursos',      src:'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&auto=format&fit=crop', title:'Naturaleza y Bienestar',          size:'large'  },
            { id:6,  type:'photo', cat:'equipo',        src:'https://images.unsplash.com/photo-1607990281513-2c110a25bd8c?w=800&auto=format&fit=crop', title:'Equipo Interdisciplinario',       size:'normal' },
            { id:7,  type:'video', cat:'videos',        src:'https://www.w3schools.com/html/mov_bbb.mp4',                                            title:'Testimonio de Bienestar',         size:'normal' },
            { id:8,  type:'photo', cat:'instalaciones', src:'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=800&auto=format&fit=crop', title:'Área de Meditación',              size:'normal' },
            { id:9,  type:'photo', cat:'recursos',      src:'https://images.unsplash.com/photo-1516534775068-ba3e7458af70?w=800&auto=format&fit=crop', title:'Taller de Mindfulness',           size:'normal' },
        ],

        /* ---- Items filtrados reactivamente ---- */
        get filtered() {
            if (this.activeFilter === 'todos') return this.items;
            if (this.activeFilter === 'videos') return this.items.filter(i => i.type === 'video');
            return this.items.filter(i => i.cat === this.activeFilter);
        },

        /* ---- Abrir lightbox ---- */
        openLightbox(item) { this.lightboxItem = item; this.lightboxOpen = true; document.body.style.overflow='hidden'; },
        closeLightbox()    { this.lightboxOpen = false; this.lightboxItem = null; document.body.style.overflow=''; },

        /* ---- Manejar archivos drop/input ---- */
        handleFiles(fileList) {
            Array.from(fileList).forEach(f => {
                if (this.files.length >= 10) return;
                this.files.push({ name: f.name, size: (f.size/1024/1024).toFixed(2)+' MB', type: f.type, progress: 0, done: false, url: URL.createObjectURL(f) });
            });
        },
        removeFile(idx) { this.files.splice(idx,1); },

        /* ---- Simular upload ---- */
        async simulateUpload() {
            if (!this.files.length) return;
            this.uploading = true;
            for (let f of this.files) {
                for (let p = 0; p <= 100; p += 10) { f.progress = p; await new Promise(r => setTimeout(r, 60)); }
                f.done = true;
            }
            this.uploading = false;
            this.uploadDone = true;
            setTimeout(() => { this.uploadDone = false; this.files = []; this.uploadOpen = false; }, 2200);
        }
    }"
    @scroll.window="scrolled = (window.pageYOffset > 50)"
    @keydown.escape.window="closeLightbox(); uploadOpen = false;"
    class="antialiased min-h-screen text-ink-900 relative"
>

{{-- ══════════════════════════════════════════
     FONDO ANIMADO (idéntico al dashboard)
══════════════════════════════════════════ --}}
<div class="fixed inset-0 z-[-1] bg-grid-pattern bg-mesh-gradient pointer-events-none">
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-sage-400/15 rounded-full blur-[120px] animate-blob"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-sand-400/15 rounded-full blur-[120px] animate-blob animation-delay-2000"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-transparent via-[#F8FAFC]/40 to-[#F8FAFC]"></div>
</div>

{{-- ══════════════════════════════════════════
     NAVBAR FLOTANTE GLASSMORPHIC
══════════════════════════════════════════ --}}
<div class="px-6 sm:px-8 pt-6 pb-2 z-50 sticky top-0">
    <nav
        :class="{ 'scrolled py-3': scrolled, 'py-4 sm:py-5 border-transparent': !scrolled }"
        class="max-w-7xl mx-auto w-full glass-nav rounded-2xl px-6 flex items-center justify-between border premium-shadow"
    >
        {{-- Logo --}}
        <a href="{{ route('landing') }}" class="flex items-center gap-3 group cursor-pointer">
            <span class="text-2xl font-extrabold plus-jakarta tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-sage-800 to-sage-600">{{ config('clinic.name') }}.</span>
        </a>

        {{-- Acciones navbar --}}
        <div class="flex items-center gap-3">
            @auth
                {{-- Botón Subir Media (solo admin/doctor) --}}
                @if(auth()->user()->isAdmin() || auth()->user()->isDoctor())
                <button
                    @click="uploadOpen = true"
                    id="btn-upload-media"
                    class="hidden sm:flex items-center gap-2 px-5 py-2.5 text-sm font-extrabold text-white rounded-full shadow-lg transition-all solid-blue-btn"
                    aria-label="Subir foto o video"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Subir Media
                </button>
                @endif

                {{-- Dropdown usuario --}}
                <div x-data="{ open: false }" class="relative">
                    <div @click="open = !open" @click.away="open = false"
                         class="flex items-center gap-3 cursor-pointer group p-1.5 pr-4 bg-slate-50 hover:bg-sage-50 border border-slate-100 hover:border-sage-200 rounded-full transition-all">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-sage-600 to-sage-800 text-white flex items-center justify-center text-sm font-bold shadow-sm group-hover:scale-105 transition-transform">
                            {{ strtoupper(substr(auth()->user()->nombre ?? 'U', 0, 1)) }}
                        </div>
                        <span class="text-[13px] font-bold text-slate-800 group-hover:text-sage-600 transition-colors hidden sm:block">{{ auth()->user()->nombre ?? 'Usuario' }}</span>
                    </div>
                    <div x-show="open" x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         class="absolute right-0 mt-3 w-52 bg-white/90 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/50 py-2 z-50 origin-top-right"
                         style="display:none;">
                        <a href="{{ route('landing') }}" class="flex items-center gap-3 px-5 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-sage-600 transition-colors font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-9 4v8m4-8v8M5 10l7-7 7 7"/></svg>
                            Inicio
                        </a>
                        <div class="border-t border-slate-100 my-2"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center gap-3 px-5 py-2.5 text-sm text-sand-700 hover:bg-sand-50 transition-colors font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="px-6 py-2.5 text-sm font-extrabold text-white rounded-full shadow-lg solid-blue-btn transition-all">Iniciar Sesión</a>
            @endauth
        </div>
    </nav>
</div>

{{-- ══════════════════════════════════════════
     CONTENIDO PRINCIPAL
══════════════════════════════════════════ --}}
<main class="max-w-7xl mx-auto px-6 sm:px-8 py-10 sm:py-14">

    {{-- ── HERO SECTION ── --}}
    <section class="reveal text-center mb-14" x-data="{ visible: false }" x-intersect.once="visible = true" :class="{ 'visible': visible }">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-full text-[11px] font-black uppercase tracking-widest text-sage-600 mb-8 shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Galería Multimedia
        </div>

        <h1 class="text-4xl md:text-6xl font-extrabold plus-jakarta text-slate-900 leading-tight tracking-tight mb-5 max-w-4xl mx-auto">
            Conoce Nuestro
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-sage-600 via-sage-500 to-sand-500"> Espacio Clínico</span>
        </h1>
        <p class="text-lg text-slate-500 font-medium max-w-2xl mx-auto leading-relaxed">
            Instalaciones, equipo profesional y recursos visuales de {{ config('clinic.name') }} — diseñados para transmitir paz, confianza y excelencia clínica.
        </p>

        @auth
            @if(auth()->user()->isAdmin() || auth()->user()->isDoctor())
            <button
                @click="uploadOpen = true"
                class="mt-8 sm:hidden inline-flex items-center gap-2 px-7 py-3.5 text-sm font-extrabold text-white rounded-full shadow-lg solid-blue-btn transition-all"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Subir Media
            </button>
            @endif
        @endauth
    </section>

    {{-- ── FILTROS ── --}}
    <div class="flex flex-wrap gap-3 justify-center mb-10 reveal" x-data="{ visible: false }" x-intersect.once="visible = true" :class="{ 'visible': visible }">
        <template x-for="f in filters" :key="f.key">
            <button
                @click="activeFilter = f.key"
                :class="activeFilter === f.key ? 'chip chip-active' : 'chip chip-inactive'"
                :aria-pressed="activeFilter === f.key"
                x-text="f.label"
            ></button>
        </template>
    </div>

    {{-- ── GRID DE GALERÍA ── --}}
    <section
        class="columns-1 sm:columns-2 lg:columns-3 gap-5 mb-20 reveal"
        x-data="{ visible: false }"
        x-intersect.once="visible = true"
        :class="{ 'visible': visible }"
        aria-label="Galería de fotos y videos"
    >
        <template x-for="item in filtered" :key="item.id">
            <div
                class="media-card relative bg-white card-shadow mb-5 break-inside-avoid group"
                :class="item.type === 'video' ? 'border-2 border-sage-200' : ''"
                @click="openLightbox(item)"
                :aria-label="'Ver: ' + item.title"
                role="button"
                tabindex="0"
                @keydown.enter="openLightbox(item)"
                :style="item.size === 'large' ? 'min-height:340px' : 'min-height:220px'"
            >
                {{-- Foto --}}
                <template x-if="item.type === 'photo'">
                    <img :src="item.src" :alt="item.title" loading="lazy" class="w-full h-full object-cover" style="min-height:inherit">
                </template>

                {{-- Video --}}
                <template x-if="item.type === 'video'">
                    <div class="relative" style="min-height:inherit">
                        <video :src="item.src" muted loop preload="none" class="w-full h-full object-cover" style="min-height:inherit"></video>
                        {{-- Botón play centrado --}}
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-14 h-14 rounded-full bg-white/90 backdrop-blur flex items-center justify-center shadow-xl group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-6 h-6 text-sage-600 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Overlay con info --}}
                <div class="card-overlay rounded-[1.5rem]"></div>
                <div class="absolute bottom-0 left-0 right-0 p-5 translate-y-2 opacity-0 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 z-10">
                    {{-- Badge tipo --}}
                    <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-full mb-2 inline-block"
                          :class="item.type === 'video' ? 'badge-video' : 'badge-photo'"
                          x-text="item.type === 'video' ? '▶ Video' : '📷 Foto'">
                    </span>
                    <p class="text-white font-bold text-sm leading-snug" x-text="item.title"></p>
                </div>
            </div>
        </template>

        {{-- Estado vacío --}}
        <template x-if="filtered.length === 0">
            <div class="col-span-full text-center py-24 text-slate-400">
                <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <p class="font-semibold">No hay elementos en esta categoría.</p>
            </div>
        </template>
    </section>

</main>

{{-- ══════════════════════════════════════════
     LIGHTBOX MODAL (Alpine.js)
══════════════════════════════════════════ --}}
<div
    x-show="lightboxOpen"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[100] flex items-center justify-center p-4 lightbox-bg"
    @click.self="closeLightbox()"
    role="dialog"
    aria-modal="true"
    aria-label="Vista ampliada"
    style="display:none"
>
    <div
        x-show="lightboxOpen"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        class="relative bg-white rounded-[2rem] overflow-hidden max-w-4xl w-full shadow-2xl"
        style="max-height:90vh"
    >
        {{-- Boton cerrar --}}
        <button
            @click="closeLightbox()"
            class="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-white/90 backdrop-blur-sm flex items-center justify-center shadow-md hover:bg-slate-50 transition-colors cursor-pointer"
            aria-label="Cerrar"
        >
            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- Contenido del lightbox --}}
        <template x-if="lightboxItem">
            <div>
                {{-- Foto --}}
                <template x-if="lightboxItem.type === 'photo'">
                    <img :src="lightboxItem.src" :alt="lightboxItem.title" class="w-full object-contain" style="max-height:72vh">
                </template>
                {{-- Video --}}
                <template x-if="lightboxItem.type === 'video'">
                    <video :src="lightboxItem.src" controls autoplay class="w-full" style="max-height:72vh; background:#000"></video>
                </template>
                {{-- Meta info --}}
                <div class="px-8 py-5 flex items-center justify-between border-t border-slate-100 bg-slate-50/60">
                    <div>
                        <p class="font-bold text-slate-900 text-base" x-text="lightboxItem.title"></p>
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-widest mt-0.5" x-text="lightboxItem.cat"></p>
                    </div>
                    <span class="text-xs font-black uppercase tracking-widest px-3 py-1.5 rounded-full"
                          :class="lightboxItem.type === 'video' ? 'bg-sage-100 text-sage-600' : 'bg-slate-100 text-slate-600'"
                          x-text="lightboxItem.type === 'video' ? 'Video' : 'Fotografía'">
                    </span>
                </div>
            </div>
        </template>
    </div>
</div>

{{-- ══════════════════════════════════════════
     MODAL UPLOAD (solo admin/doctor)
══════════════════════════════════════════ --}}
@auth
@if(auth()->user()->isAdmin() || auth()->user()->isDoctor())
<div
    x-show="uploadOpen"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-4 lightbox-bg"
    @click.self="uploadOpen = false"
    role="dialog"
    aria-modal="true"
    aria-label="Subir archivos multimedia"
    style="display:none"
>
    <div
        x-show="uploadOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        class="bg-white rounded-[2rem] w-full max-w-xl overflow-hidden shadow-2xl"
    >
        {{-- Header del modal --}}
        <div class="px-8 pt-8 pb-6 border-b border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-black plus-jakarta text-slate-900">Subir Archivos Multimedia</h2>
                    <p class="text-sm text-slate-500 font-medium mt-1">Fotos (.jpg, .png, .webp) o Videos (.mp4, .mov)</p>
                </div>
                <button @click="uploadOpen = false" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors cursor-pointer" aria-label="Cerrar modal">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <div class="px-8 py-6 space-y-5">

            {{-- Zona Drag & Drop --}}
            <div
                class="drop-zone p-8 text-center"
                :class="{ 'drag-over': dragOver }"
                @dragover.prevent="dragOver = true"
                @dragleave.prevent="dragOver = false"
                @drop.prevent="dragOver = false; handleFiles($event.dataTransfer.files)"
                @click="$refs.fileInput.click()"
                role="button"
                tabindex="0"
                aria-label="Arrastra archivos aquí o haz clic para seleccionar"
            >
                <div class="w-14 h-14 rounded-2xl bg-sage-50 text-sage-600 flex items-center justify-center mx-auto mb-4 transition-transform" :class="dragOver ? 'scale-110' : ''">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>
                <p class="font-bold text-slate-700 mb-1" x-text="dragOver ? 'Suelta aquí!' : 'Arrastra tus archivos aquí'"></p>
                <p class="text-sm text-slate-400 font-medium">o <span class="text-sage-600 font-bold cursor-pointer hover:text-sage-700">selecciona desde tu dispositivo</span></p>
                <p class="text-xs text-slate-300 font-medium mt-2">Máx. 10 archivos · 50 MB por archivo</p>
                <input
                    x-ref="fileInput"
                    type="file"
                    multiple
                    accept="image/*,video/*"
                    class="hidden"
                    @change="handleFiles($event.target.files)"
                    aria-label="Selector de archivos"
                >
            </div>

            {{-- Lista de archivos seleccionados --}}
            <template x-if="files.length > 0">
                <div class="space-y-3 max-h-52 overflow-y-auto pr-1">
                    <template x-for="(f, idx) in files" :key="idx">
                        <div class="flex items-center gap-3 bg-slate-50 rounded-2xl p-3.5 border border-slate-100">
                            {{-- Miniatura --}}
                            <div class="w-12 h-12 rounded-xl overflow-hidden bg-slate-200 flex-shrink-0">
                                <template x-if="f.type.startsWith('image')">
                                    <img :src="f.url" :alt="f.name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="f.type.startsWith('video')">
                                    <div class="w-full h-full flex items-center justify-center bg-sage-100">
                                        <svg class="w-5 h-5 text-sage-600" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    </div>
                                </template>
                            </div>
                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-800 truncate" x-text="f.name"></p>
                                <p class="text-xs text-slate-400 font-medium" x-text="f.size"></p>
                                {{-- Barra de progreso --}}
                                <div class="mt-1.5 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-300"
                                         :class="f.done ? 'bg-sage-500' : 'bg-sage-600'"
                                         :style="'width:' + f.progress + '%'">
                                    </div>
                                </div>
                            </div>
                            {{-- Quitar --}}
                            <button @click="removeFile(idx)" :disabled="uploading" class="flex-shrink-0 w-7 h-7 rounded-lg hover:bg-sand-50 text-ink-400 hover:text-sand-600 transition-colors flex items-center justify-center cursor-pointer" aria-label="Eliminar archivo">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Campos de categoría y descripción --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="media-category" class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Categoría</label>
                    <select id="media-category" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-700 font-semibold focus:outline-none focus:border-sage-500 transition-colors cursor-pointer">
                        <option value="instalaciones">Instalaciones</option>
                        <option value="equipo">Equipo</option>
                        <option value="recursos">Recursos</option>
                        <option value="videos">Videos</option>
                    </select>
                </div>
                <div>
                    <label for="media-title" class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Título</label>
                    <input id="media-title" type="text" placeholder="Ej: Sala terapéutica A" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-700 font-semibold focus:outline-none focus:border-sage-500 transition-colors placeholder:text-slate-300">
                </div>
            </div>

            {{-- Acción: Subir --}}
            <button
                @click="simulateUpload()"
                :disabled="files.length === 0 || uploading"
                class="w-full py-3.5 rounded-2xl text-sm font-extrabold transition-all solid-blue-btn disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                id="btn-confirmar-upload"
            >
                <span x-show="!uploading && !uploadDone">
                    Subir {{ 'archivo(s)' }} seleccionado(s)
                </span>
                <span x-show="uploading" class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                    Subiendo…
                </span>
                <span x-show="uploadDone" class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    ¡Subido correctamente!
                </span>
            </button>
        </div>
    </div>
</div>
@endif
@endauth

</body>
</html>
