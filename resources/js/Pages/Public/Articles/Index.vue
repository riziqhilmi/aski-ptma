<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineProps({ articles: Object });
</script>

<template>
    <Head title="Berita" />
    <PublicLayout>
        <section class="bg-aski-50">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <p class="text-sm font-semibold text-aski-700">BERITA</p>
                <h1 class="mt-2 text-4xl font-bold text-aski-900">Kabar ASKI-PTMA</h1>
                <p class="mt-3 max-w-2xl text-slate-600">Informasi terbaru seputar organisasi, keanggotaan, dan pengelolaan kekayaan intelektual.</p>
            </div>
        </section>
        <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-5 md:grid-cols-3">
                <Link v-for="article in articles.data" :key="article.id" :href="route('public.articles.show', article.slug)" class="card overflow-hidden transition hover:border-aski-300">
                    <img v-if="article.cover_image_url" :src="article.cover_image_url" :alt="article.title" class="h-48 w-full object-cover" />
                    <div v-else class="grid h-48 place-items-center bg-aski-50 font-bold text-aski-700">ASKI-PTMA</div>
                    <div class="p-5">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gold-600">{{ article.published_at }}</p>
                        <h2 class="mt-2 text-xl font-bold text-aski-900">{{ article.title }}</h2>
                        <p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-500">{{ article.excerpt }}</p>
                    </div>
                </Link>
                <div v-if="articles.data.length === 0" class="card p-8 text-center text-slate-500 md:col-span-3">Belum ada berita terbit.</div>
            </div>
            <div v-if="articles.links?.length > 3" class="mt-8 flex flex-wrap gap-2">
                <Link v-for="link in articles.links" :key="link.label" :href="link.url || '#'" v-html="link.label" class="rounded-lg border px-3 py-2 text-sm" :class="link.active ? 'border-aski-700 bg-aski-700 text-white' : 'border-aski-100 bg-white text-aski-700'" />
            </div>
        </section>
    </PublicLayout>
</template>
