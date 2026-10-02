<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

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
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-aski-700">DOKUMEN</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">Kelola Dokumen</h1>
            </div>
            <Link :href="route('admin.documents.create')" class="btn-primary">Tambah Dokumen</Link>
        </div>

        <div class="card mt-7 flex flex-wrap gap-3 p-5">
            <select name="category" :value="filters.category" class="rounded-lg border-slate-300" @change="filter">
                <option value="">Semua kategori</option>
                <option v-for="category in categories" :key="category.value" :value="category.value">{{ category.label }}</option>
            </select>
            <select name="status" :value="filters.status" class="rounded-lg border-slate-300" @change="filter">
                <option value="">Semua status</option>
                <option value="draft">Draft</option>
                <option value="published">Terbit</option>
            </select>
        </div>

        <div class="card mt-6 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-aski-50 text-aski-900">
                    <tr>
                        <th class="p-4">Judul</th>
                        <th class="p-4">Kategori</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Uploader</th>
                        <th class="p-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="document in documents.data" :key="document.id" class="border-t border-aski-100">
                        <td class="p-4 font-semibold text-aski-900">{{ document.title }}</td>
                        <td class="p-4">{{ document.category_label }}</td>
                        <td class="p-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="document.status === 'published' ? 'bg-aski-50 text-aski-700' : 'bg-gold-100 text-gold-600'">{{ document.status_label }}</span>
                        </td>
                        <td class="p-4">{{ document.uploader?.name }}</td>
                        <td class="p-4">
                            <Link :href="route('admin.documents.show', document.id)" class="font-semibold text-aski-700 hover:text-aski-900">Detail</Link>
                        </td>
                    </tr>
                    <tr v-if="documents.data.length === 0">
                        <td colspan="5" class="p-8 text-center text-slate-500">Belum ada dokumen.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="documents.links?.length > 3" class="mt-5 flex flex-wrap gap-2">
            <Link v-for="link in documents.links" :key="link.label" :href="link.url || '#'" v-html="link.label" class="rounded-lg border px-3 py-2 text-sm" :class="link.active ? 'border-aski-700 bg-aski-700 text-white' : 'border-aski-100 bg-white text-aski-700'" />
        </div>
    </AdminLayout>
</template>
