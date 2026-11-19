<script setup>
import { nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const menuOpen = ref(false);
const page = usePage();
const mainContent = ref(null);
let revealObserver;
let revealRun = 0;

const revealPageContent = async () => {
    const run = ++revealRun;
    revealObserver?.disconnect();
    await nextTick();

    if (run !== revealRun || !mainContent.value) return;

    const targets = mainContent.value.querySelectorAll('[data-motion], section, article');
    const elements = [...targets].filter((element) => {
        if (element.closest('[data-motion]')) return false;
        if (element.matches('article') && !element.closest('.grid')) return false;
        return true;
    });

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        elements.forEach((element) => element.classList.add('motion-visible'));
        return;
    }

    elements.forEach((element, index) => {
        element.classList.add('motion-reveal');
        element.style.setProperty('--motion-delay', `${Math.min(index * 65, 260)}ms`);
    });

    if (!('IntersectionObserver' in window)) {
        elements.forEach((element) => element.classList.add('motion-visible'));
        return;
    }

    revealObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('motion-visible');
                revealObserver?.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -32px 0px' });

    elements.forEach((element) => revealObserver.observe(element));
};

watch(() => page.url.split('?')[0], revealPageContent, { flush: 'post', immediate: true });
onBeforeUnmount(() => {
    revealRun++;
    revealObserver?.disconnect();
});
const links = [
    { label: 'Beranda', route: 'home', match: (url) => url === '/' },
    { label: 'Profil', route: 'public.profile', match: (url) => url.startsWith('/profil') },
    { label: 'Anggota', route: 'public.members.index', match: (url) => url.startsWith('/anggota') },
    { label: 'Data HKI', route: 'public.intellectual-properties.index', match: (url) => url.startsWith('/data-hki') },
    { label: 'Berita', route: 'public.articles.index', match: (url) => url.startsWith('/berita') },
    { label: 'Kontak', route: 'public.contact', match: (url) => url.startsWith('/kontak') },
];
</script>

<template>
    <div class="min-h-screen bg-white text-slate-800">
        <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 shadow-sm backdrop-blur">
            <nav class="mx-auto flex min-h-[76px] max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8" aria-label="Navigasi utama">
                <Link :href="route('home')" class="flex min-w-0 items-center gap-3 rounded-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-aski-700" @click="menuOpen = false">
                    <img src="/images/logo-aski.png" alt="" class="h-11 w-11 shrink-0 object-contain" />
                    <span class="min-w-0">
                        <span class="block text-base font-extrabold tracking-tight text-aski-900 sm:text-lg">ASKI-PTMA</span>
                        <span class="hidden text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500 sm:block">Asosiasi Sentra KI</span>
                    </span>
                </Link>

                <div class="hidden items-center gap-1 lg:flex">
                    <Link v-for="item in links" :key="item.route" :href="route(item.route)" class="rounded-lg px-3 py-2 text-sm font-semibold transition-colors hover:bg-aski-50 hover:text-aski-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-aski-700" :class="item.match($page.url) ? 'bg-aski-50 text-aski-900' : 'text-slate-600'" :aria-current="item.match($page.url) ? 'page' : undefined">{{ item.label }}</Link>
                </div>

                <div class="hidden items-center gap-3 lg:flex">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="btn-primary">Dashboard</Link>
                    <template v-else>
                        <Link :href="route('login')" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-aski-200 px-4 py-2 text-sm font-semibold text-aski-800 transition hover:bg-aski-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">Masuk</Link>
                        <Link :href="route('register')" class="btn-primary">Daftar Member</Link>
                    </template>
                </div>

                <div class="flex items-center gap-2 lg:hidden">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="rounded-lg px-3 py-2 text-sm font-semibold text-aski-800 hover:bg-aski-50">Dashboard</Link>
                    <template v-else>
                        <Link :href="route('login')" class="hidden rounded-lg px-3 py-2 text-sm font-semibold text-aski-800 transition hover:bg-aski-50 sm:inline-flex">Masuk</Link>
                        <Link :href="route('register')" class="inline-flex items-center justify-center rounded-lg bg-aski-800 px-3 py-2 text-sm font-semibold text-white transition hover:bg-aski-900">Daftar<span class="hidden sm:inline"> Member</span></Link>
                    </template>
                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50" :aria-expanded="menuOpen" aria-controls="public-mobile-menu" :aria-label="menuOpen ? 'Tutup menu' : 'Buka menu'" @click="menuOpen = !menuOpen">
                        <svg v-if="!menuOpen" aria-hidden="true" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 5.25A.75.75 0 0 1 3.75 4.5h12.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 5.25Zm0 4.75a.75.75 0 0 1 .75-.75h12.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 10Zm0 4.75a.75.75 0 0 1 .75-.75h12.5a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" /></svg>
                        <svg v-else aria-hidden="true" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.22 4.22a.75.75 0 0 1 1.06 0L10 8.94l4.72-4.72a.75.75 0 1 1 1.06 1.06L11.06 10l4.72 4.72a.75.75 0 1 1-1.06 1.06L10 11.06l-4.72 4.72a.75.75 0 0 1-1.06-1.06L8.94 10 4.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
                    </button>
                </div>
            </nav>
            <div v-if="menuOpen" id="public-mobile-menu" class="border-t border-slate-100 bg-white px-4 py-3 shadow-lg lg:hidden sm:px-6">
                <div class="mx-auto grid max-w-7xl gap-1 sm:grid-cols-2">
                    <Link v-for="item in links" :key="item.route" :href="route(item.route)" class="rounded-lg px-3 py-3 text-sm font-semibold transition-colors hover:bg-aski-50 hover:text-aski-900" :class="item.match($page.url) ? 'bg-aski-50 text-aski-900' : 'text-slate-600'" :aria-current="item.match($page.url) ? 'page' : undefined" @click="menuOpen = false">{{ item.label }}</Link>
                    <div class="mt-2 grid grid-cols-2 gap-2 border-t border-slate-100 pt-3 sm:col-span-2">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="rounded-lg bg-aski-800 px-3 py-3 text-center text-sm font-semibold text-white hover:bg-aski-900" @click="menuOpen = false">Dashboard</Link>
                        <template v-else>
                            <Link :href="route('login')" class="rounded-lg border border-aski-200 px-3 py-3 text-center text-sm font-semibold text-aski-800 hover:bg-aski-50" @click="menuOpen = false">Masuk</Link>
                            <Link :href="route('register')" class="rounded-lg bg-aski-800 px-3 py-3 text-center text-sm font-semibold text-white hover:bg-aski-900" @click="menuOpen = false">Daftar Member</Link>
                        </template>
                    </div>
                </div>
            </div>
        </header>

        <main ref="mainContent" :key="$page.url.split('?')[0]" class="motion-page-enter"><slot /></main>

        <footer class="mt-16 border-t border-aski-900 bg-aski-900 text-white">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 py-10 sm:px-6 md:grid-cols-[1.2fr_1fr_0.7fr] lg:px-8">
                <div>
                    <Link :href="route('home')" class="inline-flex items-center gap-3 rounded-lg focus-visible:outline-white">
                        <img src="/images/logo-aski.png" alt="" class="h-12 w-12 rounded-lg bg-white p-1 object-contain" />
                        <span><span class="block text-lg font-bold">ASKI-PTMA</span><span class="mt-0.5 block text-xs text-aski-100">Asosiasi Sentra Kekayaan Intelektual</span></span>
                    </Link>
                    <p class="mt-4 max-w-lg text-sm leading-6 text-aski-100">Platform informasi dan pengelolaan keanggotaan serta data kekayaan intelektual Perguruan Tinggi Muhammadiyah dan 'Aisyiyah.</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-gold-400">Jelajahi</p>
                    <div class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-sm text-aski-100">
                        <Link v-for="item in links" :key="item.route" :href="route(item.route)" class="rounded hover:text-white focus-visible:outline-white">{{ item.label }}</Link>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-gold-400">Akun</p>
                    <div class="mt-3 grid gap-2 text-sm text-aski-100">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="rounded hover:text-white focus-visible:outline-white">Dashboard</Link>
                        <template v-else>
                            <Link :href="route('login')" class="rounded hover:text-white focus-visible:outline-white">Masuk</Link>
                            <Link :href="route('register')" class="rounded hover:text-white focus-visible:outline-white">Daftar Member</Link>
                        </template>
                    </div>
                </div>
            </div>
            <div class="border-t border-white/10">
                <div class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-4 text-xs text-aski-100 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                    <span>© {{ new Date().getFullYear() }} ASKI-PTMA</span>
                    <span>Asosiasi Sentra Kekayaan Intelektual PTMA</span>
                </div>
            </div>
        </footer>
    </div>
</template>
