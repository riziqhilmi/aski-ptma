<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PaginationLinks from '@/Components/PaginationLinks.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { formatDateTime } from '@/utils/date';

const props = defineProps({
    articles: Object,
    filters: Object,
});

const filter = (event) => router.get(route('admin.articles.index'), { status: event.target.value }, {
    preserveState: true,
    preserveScroll: true,
});
</script>

<template>
    <Head title="Berita" />
    <AdminLayout>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-aski-700">BERITA</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">Kelola Artikel Berita</h1>
            </div>
            <Link :href="route('admin.articles.create')" class="btn-primary inline-flex min-h-11 items-center justify-center">Tambah Artikel</Link>
        </div>

        <div class="mt-5 flex justify-end">
            <label class="sr-only" for="article-status-filter">Filter status artikel</label>
            <select id="article-status-filter" :value="filters.status" class="min-h-10 w-full rounded-lg border-slate-200 bg-white text-sm focus:border-aski-500 focus:ring-aski-500 sm:w-44" @change="filter">
                <option value="">Semua status</option>
                <option value="draft">Draft</option>
                <option value="published">Terbit</option>
            </select>
        </div>

        <section class="card mt-5 overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6"><h2 class="font-semibold text-aski-900">Daftar Berita</h2><p class="mt-1 text-sm text-slate-500">{{ articles.from || 0 }}–{{ articles.to || 0 }} dari {{ articles.total }} artikel</p></div>
            <div tabindex="0" role="region" aria-label="Tabel artikel, gunakan gulir horizontal untuk melihat kolom lain" class="overflow-x-auto focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-aski-50 text-aski-900">
                    <tr>
                        <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Judul</th>
                        <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Penulis</th>
                        <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Status</th>
                        <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Publikasi</th>
                        <th scope="col" class="px-4 py-3.5 text-right font-semibold sm:px-6">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="article in articles.data" :key="article.id" class="transition-colors hover:bg-slate-50/70">
                        <td class="px-4 py-3.5 font-semibold text-aski-900 sm:px-6">{{ article.title }}</td>
                        <td class="whitespace-nowrap px-4 py-3.5 text-slate-700 sm:px-6">{{ article.author?.name }}</td>
                        <td class="px-4 py-3.5 sm:px-6"><StatusBadge :status="article.status" :label="article.status_label" /></td>
                        <td class="whitespace-nowrap px-4 py-3.5 text-slate-600 sm:px-6">{{ formatDateTime(article.published_at) }}</td>
                        <td class="px-4 py-3.5 text-right sm:px-6"><Link :href="route('admin.articles.show', article.id)" class="font-semibold text-aski-700 hover:text-aski-900">Detail</Link></td>
                    </tr>
                    <tr v-if="articles.data.length === 0">
                        <td colspan="5" class="px-4 py-10 text-center text-slate-500 sm:px-6">Belum ada artikel.</td>
                    </tr>
                </tbody>
            </table></div>
        </section>

        <PaginationLinks :links="articles.links" />
    </AdminLayout>
</template>
