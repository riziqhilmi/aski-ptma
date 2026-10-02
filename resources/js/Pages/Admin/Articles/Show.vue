<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ article: Object });
const remove = () => {
    if (confirm('Hapus artikel ini?')) {
        router.delete(route('admin.articles.destroy', props.article.id));
    }
};
</script>

<template>
    <Head :title="article.title" />
    <AdminLayout>
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-aski-700">DETAIL BERITA</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">{{ article.title }}</h1>
            </div>
            <div class="flex flex-wrap gap-3">
                <a v-if="article.status === 'published'" :href="route('public.articles.show', article.slug)" target="_blank" class="btn-primary">Lihat Publik</a>
                <Link :href="route('admin.articles.edit', article.id)" class="rounded-lg border border-aski-200 px-4 py-2 font-semibold text-aski-700">Ubah</Link>
                <button class="rounded-lg border border-red-200 px-4 py-2 font-semibold text-red-700 hover:bg-red-50" @click="remove">Hapus</button>
            </div>
        </div>
        <div class="card mt-7 overflow-hidden">
            <img v-if="article.cover_image_url" :src="article.cover_image_url" :alt="article.title" class="h-72 w-full object-cover" />
            <div class="p-6">
                <div class="flex flex-wrap gap-3 text-sm text-slate-500">
                    <span>{{ article.status_label }}</span>
                    <span>Penulis: {{ article.author?.name }}</span>
                    <span>Slug: {{ article.slug }}</span>
                </div>
                <p class="mt-5 font-semibold text-aski-900">{{ article.excerpt }}</p>
                <div class="mt-5 whitespace-pre-line leading-8 text-slate-700">{{ article.body }}</div>
            </div>
        </div>
    </AdminLayout>
</template>
