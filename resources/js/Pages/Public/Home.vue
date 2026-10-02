<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineProps({
    latestArticles: Array,
});
</script>

<template>
    <Head title="Beranda" />
    <PublicLayout>
        <section class="bg-gradient-to-br from-aski-50 via-white to-gold-100">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <p class="font-semibold uppercase tracking-[0.18em] text-aski-700">Asosiasi Sentra Kekayaan Intelektual</p>
                <h1 class="mt-5 max-w-3xl text-4xl font-bold tracking-tight text-aski-900 sm:text-6xl">Sistem Informasi ASKI-PTMA</h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">Platform pengelolaan keanggotaan dan data HKI Perguruan Tinggi Muhammadiyah dan 'Aisyiyah.</p>
                <Link :href="$page.props.auth.user ? route('dashboard') : route('login')" class="btn-primary mt-8 px-5 py-3">{{ $page.props.auth.user ? 'Buka Dashboard' : 'Masuk ke Sistem' }}</Link>
            </div>
        </section>
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-aski-700">BERITA TERBARU</p>
                    <h2 class="mt-1 text-3xl font-bold text-aski-900">Kabar ASKI-PTMA</h2>
                </div>
                <Link :href="route('public.articles.index')" class="font-semibold text-aski-700 hover:text-aski-900">Lihat semua berita</Link>
            </div>
            <div class="mt-7 grid gap-5 md:grid-cols-3">
                <Link v-for="article in latestArticles" :key="article.id" :href="route('public.articles.show', article.slug)" class="card overflow-hidden transition hover:border-aski-300">
                    <img v-if="article.cover_image_url" :src="article.cover_image_url" :alt="article.title" class="h-44 w-full object-cover" />
                    <div v-else class="grid h-44 place-items-center bg-aski-50 text-aski-700 font-bold">ASKI-PTMA</div>
                    <div class="p-5">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gold-600">{{ article.published_at }}</p>
                        <h3 class="mt-2 text-lg font-bold text-aski-900">{{ article.title }}</h3>
                        <p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-500">{{ article.excerpt }}</p>
                    </div>
                </Link>
                <div v-if="latestArticles.length === 0" class="card p-8 text-center text-slate-500 md:col-span-3">Belum ada berita terbit.</div>
            </div>
        </section>
    </PublicLayout>
</template>
