import '../css/app.css';

import axios from 'axios';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme } from './composables/useAppearance';

// Reload the page when session expires (419 CSRF / 401 Unauthenticated) for direct axios calls
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 419 || error.response?.status === 401) {
            window.location.reload();
        }
        return Promise.reject(error);
    },
);

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// Automatically reload the page when the session expires (419 CSRF token mismatch)
router.on('invalid', (event) => {
    const status = event.detail?.response?.status;
    if (status === 419) {
        window.location.reload();
    }
});

// This will set light / dark mode on page load...
initializeTheme();
