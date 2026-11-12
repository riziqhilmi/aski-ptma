import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

document.addEventListener('keydown', (event) => {
    if (event.altKey || event.ctrlKey || event.metaKey || !['ArrowLeft', 'ArrowRight'].includes(event.key)) return;

    const region = event.target;
    if (!(region instanceof HTMLElement) || !region.matches('[role="region"][tabindex="0"]')) return;
    if (region.scrollWidth <= region.clientWidth) return;

    const distance = Math.max(40, Math.floor(region.clientWidth * 0.8));
    const maximumScroll = region.scrollWidth - region.clientWidth;
    const nextScroll = Math.max(0, Math.min(maximumScroll, region.scrollLeft + (event.key === 'ArrowRight' ? distance : -distance)));
    if (nextScroll === region.scrollLeft) return;

    region.scrollLeft = nextScroll;
    event.preventDefault();
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
