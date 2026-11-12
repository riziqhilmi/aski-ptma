<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { formatDateTime } from '@/utils/date';
import ConfirmActionDialog from '@/Components/ConfirmActionDialog.vue';
import { ref } from 'vue';

const props = defineProps({ article: Object });
const confirmingDelete = ref(false);
const deleteProcessing = ref(false);
const remove = () => {
    confirmingDelete.value = true;
};
const confirmDelete = () => {
    if (deleteProcessing.value) return;
    deleteProcessing.value = true;
    router.delete(route('admin.articles.destroy', props.article.id), {
        onSuccess: () => { confirmingDelete.value = false; },
        onFinish: () => { deleteProcessing.value = false; },
    });
};
</script>

<template>
    <Head :title="article.title" />
    <AdminLayout>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-aski-700">Publikasi · Detail Berita</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">{{ article.title }}</h1>
            </div>
            <div class="flex flex-wrap gap-3">
                <a v-if="article.status === 'published'" :href="route('public.articles.show', article.slug)" target="_blank" rel="noopener" class="btn-primary inline-flex min-h-10 items-center justify-center">Lihat Publik</a>
                <Link :href="route('admin.articles.edit', article.id)" class="inline-flex min-h-10 items-center rounded-lg border border-aski-200 px-4 font-semibold text-aski-700">Ubah</Link>
                <button class="inline-flex min-h-10 items-center rounded-lg border border-red-200 px-4 font-semibold text-red-700 hover:bg-red-50" @click="remove">Hapus</button>
            </div>
        </div>
        <article class="card mt-6 overflow-hidden">
            <img v-if="article.cover_image_url" :src="article.cover_image_url" :alt="article.title" class="max-h-[28rem] w-full object-cover" />
            <div class="p-5 sm:p-8">
                <div class="flex flex-wrap items-center gap-3 text-sm text-slate-500">
                    <StatusBadge :status="article.status" :label="article.status_label" />
                    <span>Penulis: {{ article.author?.name }}</span>
                    <span v-if="article.published_at">Terbit {{ formatDateTime(article.published_at) }}</span>
                </div>
                <p class="mt-6 max-w-3xl text-lg font-semibold leading-8 text-aski-900">{{ article.excerpt }}</p>
                <div class="mt-6 max-w-3xl whitespace-pre-line leading-8 text-slate-700">{{ article.body }}</div>
            </div>
        </article>
        <ConfirmActionDialog :show="confirmingDelete" title="Hapus artikel?" description="Artikel beserta gambar sampulnya akan dihapus dan tidak bisa dipulihkan." :item="article.title" action-label="Hapus artikel" :processing="deleteProcessing" @close="confirmingDelete = false" @confirm="confirmDelete" />
    </AdminLayout>
</template>
