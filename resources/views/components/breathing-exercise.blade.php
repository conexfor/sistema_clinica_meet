<div id="breathing-overlay" class="fixed inset-0 z-[200] flex flex-col items-center justify-center p-6 text-center transition-all duration-1000">
    <!-- Fondo natural sereno -->
    <div class="absolute inset-0 bg-gradient-to-br from-sage-800 via-sage-900 to-sage-950 opacity-100"></div>
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-sage-500/15 rounded-full blur-[120px] animate-pulse"></div>
    <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-sand-400/10 rounded-full blur-[120px] animate-pulse [animation-delay:2s]"></div>

    <div class="relative z-10 max-w-lg">
        <h2 class="font-serif text-3xl text-white mb-4">Prepara tu espacio</h2>
        <p class="text-cream-100/70 font-medium mb-12">Tómate un momento para centrarte antes de comenzar la sesión.</p>

        <!-- Círculo de respiración -->
        <div class="relative flex items-center justify-center mb-16">
            <div class="absolute w-40 h-40 bg-sage-400/20 rounded-full animate-[ping_4s_linear_infinite]"></div>
            <div class="absolute w-40 h-40 bg-sage-400/30 rounded-full animate-[ping_4s_linear_infinite] [animation-delay:1s]"></div>

            <div id="breathing-circle" class="w-40 h-40 bg-gradient-to-br from-sage-400 to-sand-400 rounded-full shadow-[0_0_60px_rgba(52,128,174,0.5)] flex items-center justify-center border-4 border-white/20 transition-all duration-[4s] ease-in-out scale-100">
                <span id="breathing-text" class="text-white font-bold uppercase tracking-[0.2em] text-sm">Inhala</span>
            </div>
        </div>

        <button onclick="startSession()" class="px-12 py-5 bg-white text-sage-800 rounded-full font-bold text-lg shadow-2xl hover:scale-105 active:scale-95 transition-all">
            Estoy listo
        </button>
        <p class="mt-6 text-cream-100/50 text-xs font-bold uppercase tracking-widest">O espera el inicio automático en 30s</p>
    </div>
</div>

<script>
    const circle = document.getElementById('breathing-circle');
    const text = document.getElementById('breathing-text');
    let isInhaling = true;

    function animateBreathing() {
        if (isInhaling) {
            circle.style.transform = 'scale(1.5)';
            text.innerText = 'Inhala';
        } else {
            circle.style.transform = 'scale(1.0)';
            text.innerText = 'Exhala';
        }
        isInhaling = !isInhaling;
    }

    const breathingInterval = setInterval(animateBreathing, 4000);
    animateBreathing(); // Start immediately

    function startSession() {
        clearInterval(breathingInterval);
        const overlay = document.getElementById('breathing-overlay');
        overlay.style.opacity = '0';
        overlay.style.pointerEvents = 'none';
        setTimeout(() => overlay.remove(), 1000);
    }
</script>

<style>
    @keyframes ping-slow {
        75%, 100% {
            transform: scale(2);
            opacity: 0;
        }
    }
</style>
