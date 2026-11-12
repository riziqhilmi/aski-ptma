<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatDateTime } from '@/utils/date';

defineProps({ articles: Object });
</script>

<template>
    <Head title="Berita ASKI-PTMA" />
    <PublicLayout>
        <section class="bg-aski-900 text-white">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-gold-400">Informasi organisasi</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Kabar ASKI-PTMA</h1>
                <p class="mt-4 max-w-2xl text-base leading-7 text-aski-100">Informasi terbaru seputar organisasi, keanggotaan, dan pengelolaan kekayaan intelektual.</p>
            </div>
        </section>
        <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
            <div class="mb-6 flex items-end justify-between border-b border-slate-200 pb-4">
                <div><p class="text-xs font-bold uppercase tracking-[0.14em] text-aski-700">Pembaruan terbaru</p><h2 class="mt-1 text-xl font-bold text-aski-900">Berita dan artikel</h2></div>
                <span class="text-sm text-slate-500">{{ articles.total }} publikasi</span>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <Link v-for="article in articles.data" :key="article.id" :href="route('public.articles.show', article.slug)" class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-aski-200 hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
                    <img v-if="article.cover_image_url" :src="article.cover_image_url" :alt="article.title" class="aspect-[16/10] w-full object-cover" />
                    <div v-else class="grid aspect-[16/10] place-items-center bg-aski-50"><img src="/images/logo-aski.png" alt="" class="h-20 w-20 object-contain" /></div>
                    <div class="p-5 sm:p-6">
                        <p class="text-xs font-semibold uppercase tracking-wider text-aski-700">{{ formatDateTime(article.published_at) }}</p>
                        <h3 class="mt-2 line-clamp-2 text-lg font-bold leading-7 text-aski-900">{{ article.title }}</h3>
                        <p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-600">{{ article.excerpt }}</p>
                        <span class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-aski-700">Baca selengkapnya <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span></span>
                    </div>
                </Link>
                <div v-if="articles.data.length === 0" class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center sm:col-span-2 lg:col-span-3"><p class="font-semibold text-aski-900">Belum ada berita terbit</p><p class="mt-1 text-sm text-slate-500">Publikasi akan muncul di halaman ini.</p></div>
            </div>
            <nav v-if="articles.links?.length > 3" aria-label="Navigasi halaman berita" class="mt-8 flex flex-wrap justify-center gap-2">
                <Link v-for="link in articles.links" :key="link.label" :href="link.url || '#berita'" v-html="link.label" :aria-current="link.active ? 'page' : undefined" class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-lg border px-3 py-2 text-sm font-semibold transition" :class="link.active ? 'border-aski-700 bg-aski-700 text-white' : 'border-slate-200 bg-white text-aski-700 hover:bg-aski-50'" />
            </nav>
        </section>
    </PublicLayout>
</template>
