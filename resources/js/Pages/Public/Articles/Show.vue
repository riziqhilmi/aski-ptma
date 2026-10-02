<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineProps({ article: Object });
</script>

<template>
    <Head :title="article.title" />
    <PublicLayout>
        <article>
            <section class="bg-aski-50">
                <div class="mx-auto max-w-4xl px-4 py-14 sm:px-6 lg:px-8">
                    <Link :href="route('public.articles.index')" class="text-sm font-semibold text-aski-700 hover:text-aski-900">Kembali ke berita</Link>
                    <h1 class="mt-4 text-4xl font-bold leading-tight text-aski-900">{{ article.title }}</h1>
                    <p class="mt-4 text-slate-500">Ditulis oleh {{ article.author?.name }} · {{ article.published_at }}</p>
                </div>
            </section>
            <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
                <img v-if="article.cover_image_url" :src="article.cover_image_url" :alt="article.title" class="mb-8 h-96 w-full rounded-lg object-cover" />
                <p class="text-xl font-semibold leading-8 text-aski-900">{{ article.excerpt }}</p>
                <div class="mt-8 whitespace-pre-line leading-8 text-slate-700">{{ article.body }}</div>
            </div>
        </article>
    </PublicLayout>
</template>
