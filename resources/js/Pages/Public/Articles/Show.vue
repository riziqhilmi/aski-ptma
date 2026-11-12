<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatDateTime } from '@/utils/date';

defineProps({ article: Object });
</script>

<template>
    <Head :title="article.title" />
    <PublicLayout>
        <article>
            <header class="bg-aski-900 text-white">
                <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
                    <Link :href="route('public.articles.index')" class="inline-flex items-center gap-2 rounded text-sm font-semibold text-aski-100 transition hover:text-white focus-visible:outline-white"><span aria-hidden="true">←</span> Semua berita</Link>
                    <p class="mt-7 text-xs font-bold uppercase tracking-[0.16em] text-gold-400">Berita ASKI-PTMA</p>
                    <h1 class="mt-3 text-3xl font-bold leading-tight tracking-tight sm:text-4xl lg:text-5xl">{{ article.title }}</h1>
                    <div class="mt-5 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-aski-100">
                        <span>{{ article.author?.name }}</span><span aria-hidden="true" class="text-gold-400">•</span><time :datetime="article.published_at">{{ formatDateTime(article.published_at) }}</time>
                    </div>
                </div>
            </header>
            <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
                <img v-if="article.cover_image_url" :src="article.cover_image_url" :alt="article.title" class="mb-8 aspect-[16/9] w-full rounded-2xl object-cover shadow-md" />
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8 lg:p-10">
                    <p class="border-l-4 border-gold-400 pl-4 text-lg font-semibold leading-8 text-aski-900 sm:text-xl">{{ article.excerpt }}</p>
                    <div class="mt-7 whitespace-pre-line text-base leading-8 text-slate-700">{{ article.body }}</div>
                    <div class="mt-9 border-t border-slate-200 pt-6"><Link :href="route('public.articles.index')" class="inline-flex min-h-10 items-center gap-2 rounded-lg px-3 text-sm font-semibold text-aski-700 transition hover:bg-aski-50">← Kembali ke daftar berita</Link></div>
                </div>
            </div>
        </article>
    </PublicLayout>
</template>
