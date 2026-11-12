<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PaginationLinks from '@/Components/PaginationLinks.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
    documents: Object,
    filters: Object,
    categories: Array,
});

const filter = (event) => {
    const field = event.target.name;
    router.get(route('admin.documents.index'), { ...props.filters, [field]: event.target.value }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Dokumen" />
    <AdminLayout>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-aski-700">DOKUMEN</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">Kelola Dokumen</h1>
            </div>
            <Link :href="route('admin.documents.create')" class="btn-primary inline-flex min-h-11 items-center justify-center">Tambah Dokumen</Link>
        </div>

        <div class="mt-5 flex flex-col gap-2 sm:flex-row sm:justify-end">
            <label class="sr-only" for="document-category-filter">Filter kategori dokumen</label>
            <select id="document-category-filter" name="category" :value="filters.category" class="min-h-10 w-full rounded-lg border-slate-200 bg-white text-sm focus:border-aski-500 focus:ring-aski-500 sm:w-52" @change="filter">
                <option value="">Semua kategori</option>
                <option v-for="category in categories" :key="category.value" :value="category.value">{{ category.label }}</option>
            </select>
            <label class="sr-only" for="document-status-filter">Filter status dokumen</label>
            <select id="document-status-filter" name="status" :value="filters.status" class="min-h-10 w-full rounded-lg border-slate-200 bg-white text-sm focus:border-aski-500 focus:ring-aski-500 sm:w-44" @change="filter">
                <option value="">Semua status</option>
                <option value="draft">Draft</option>
                <option value="published">Terbit</option>
            </select>
        </div>

        <section class="card mt-5 overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6"><h2 class="font-semibold text-aski-900">Daftar Dokumen</h2><p class="mt-1 text-sm text-slate-500">{{ documents.from || 0 }}–{{ documents.to || 0 }} dari {{ documents.total }} dokumen</p></div>
            <div tabindex="0" role="region" aria-label="Tabel dokumen, gunakan gulir horizontal untuk melihat kolom lain" class="overflow-x-auto focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-aski-50 text-aski-900">
                    <tr>
                        <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Judul</th>
                        <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Kategori</th>
                        <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Status</th>
                        <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Uploader</th>
                        <th scope="col" class="px-4 py-3.5 text-right font-semibold sm:px-6">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="document in documents.data" :key="document.id" class="transition-colors hover:bg-slate-50/70">
                        <td class="px-4 py-3.5 font-semibold text-aski-900 sm:px-6">{{ document.title }}</td>
                        <td class="whitespace-nowrap px-4 py-3.5 text-slate-700 sm:px-6">{{ document.category_label }}</td>
                        <td class="px-4 py-3.5 sm:px-6"><StatusBadge :status="document.status" :label="document.status_label" /></td>
                        <td class="px-4 py-3.5 text-slate-600 sm:px-6">{{ document.uploader?.name }}</td>
                        <td class="px-4 py-3.5 text-right sm:px-6">
                            <Link :href="route('admin.documents.show', document.id)" class="font-semibold text-aski-700 hover:text-aski-900">Detail</Link>
                        </td>
                    </tr>
                    <tr v-if="documents.data.length === 0">
                        <td colspan="5" class="px-4 py-10 text-center text-slate-500 sm:px-6">Belum ada dokumen.</td>
                    </tr>
                </tbody>
            </table></div>
        </section>

        <PaginationLinks :links="documents.links" />
    </AdminLayout>
</template>
