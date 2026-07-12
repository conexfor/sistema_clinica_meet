import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    // Host público del túnel que expone el dev server de Vite (puerto 5173).
    // Ej.: VITE_HMR_HOST=xxxx.ngrok-free.dev  (el dominio del 2º túnel de ngrok).
    const hmrHost = env.VITE_HMR_HOST || '';

    return {
        plugins: [   
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
            tailwindcss(),
        ],
        server: {
            host: true,          // escuchar en 0.0.0.0 (accesible por el túnel)
            cors: true,
            allowedHosts: true,  // aceptar peticiones desde cualquier host (incluye ngrok)
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
            // HMR sobre HTTPS (ngrok) solo cuando se define el host del túnel de Vite.
            ...(hmrHost
                ? {
                      hmr: {
                          host: hmrHost,
                          protocol: 'wss',
                          clientPort: 443,
                      },
                  }
                : {}),
        },
    };
});
