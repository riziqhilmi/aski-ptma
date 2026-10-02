<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ document: Object });
const remove = () => {
    if (confirm('Hapus dokumen ini?')) {
        router.delete(route('admin.documents.destroy', props.document.id));
    }
};
</script>

<template>
    <Head :title="document.title" />
    <AdminLayout>
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-aski-700">DETAIL DOKUMEN</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">{{ document.title }}</h1>
            </div>
            <div class="flex flex-wrap gap-3">
                <a :href="route('admin.documents.download', document.id)" class="btn-primary">Download</a>
                <Link :href="route('admin.documents.edit', document.id)" class="rounded-lg border border-aski-200 px-4 py-2 font-semibold text-aski-700">Ubah</Link>
                <button class="rounded-lg border border-red-200 px-4 py-2 font-semibold text-red-700 hover:bg-red-50" @click="remove">Hapus</button>
            </div>
        </div>

        <div class="card mt-7 grid gap-5 p-6 md:grid-cols-2">
            <div><p class="text-xs font-semibold uppercase text-slate-500">Kategori</p><p class="mt-1 font-semibold">{{ document.category_label }}</p></div>
            <div><p class="text-xs font-semibold uppercase text-slate-500">Status</p><p class="mt-1 font-semibold">{{ document.status_label }}</p></div>
            <div><p class="text-xs font-semibold uppercase text-slate-500">Nama File</p><p class="mt-1 font-semibold">{{ document.original_filename }}</p></div>
            <div><p class="text-xs font-semibold uppercase text-slate-500">Uploader</p><p class="mt-1 font-semibold">{{ document.uploader?.name }}</p></div>
            <div class="md:col-span-2"><p class="text-xs font-semibold uppercase text-slate-500">Deskripsi</p><p class="mt-1 text-slate-700">{{ document.description || '-' }}</p></div>
        </div>
    </AdminLayout>
</template>
