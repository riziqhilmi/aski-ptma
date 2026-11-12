<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import ConfirmActionDialog from '@/Components/ConfirmActionDialog.vue';
import { ref } from 'vue';

const props = defineProps({ document: Object });
const confirmingDelete = ref(false);
const deleteProcessing = ref(false);
const remove = () => {
    confirmingDelete.value = true;
};
const confirmDelete = () => {
    if (deleteProcessing.value) return;
    deleteProcessing.value = true;
    router.delete(route('admin.documents.destroy', props.document.id), {
        onSuccess: () => { confirmingDelete.value = false; },
        onFinish: () => { deleteProcessing.value = false; },
    });
};
</script>

<template>
    <Head :title="document.title" />
    <AdminLayout>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-aski-700">Publikasi · Detail Dokumen</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">{{ document.title }}</h1>
            </div>
            <div class="flex flex-wrap gap-3">
                <a :href="route('admin.documents.download', document.id)" class="btn-primary inline-flex min-h-10 items-center justify-center">Unduh PDF</a>
                <Link :href="route('admin.documents.edit', document.id)" class="inline-flex min-h-10 items-center rounded-lg border border-aski-200 px-4 font-semibold text-aski-700">Ubah</Link>
                <button class="inline-flex min-h-10 items-center rounded-lg border border-red-200 px-4 font-semibold text-red-700 hover:bg-red-50" @click="remove">Hapus</button>
            </div>
        </div>

        <div class="card mt-6 grid gap-0 overflow-hidden sm:grid-cols-2">
            <div class="border-b border-slate-100 p-5 sm:px-6"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kategori</p><p class="mt-2 font-semibold text-slate-900">{{ document.category_label }}</p></div>
            <div class="border-b border-slate-100 p-5 sm:px-6"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</p><p class="mt-2"><StatusBadge :status="document.status" :label="document.status_label" /></p></div>
            <div class="border-b border-slate-100 p-5 sm:px-6"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nama File</p><p class="mt-2 break-all font-semibold text-slate-900">{{ document.original_filename }}</p></div>
            <div class="border-b border-slate-100 p-5 sm:px-6"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Uploader</p><p class="mt-2 font-semibold text-slate-900">{{ document.uploader?.name }}</p></div>
            <div class="p-5 sm:col-span-2 sm:px-6"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Deskripsi</p><p class="mt-2 whitespace-pre-line leading-7 text-slate-700">{{ document.description || 'Tidak ada deskripsi.' }}</p></div>
        </div>
        <ConfirmActionDialog :show="confirmingDelete" title="Hapus dokumen?" description="Dokumen dan file yang tersimpan akan dihapus dan tidak bisa dipulihkan." :item="document.title" action-label="Hapus dokumen" :processing="deleteProcessing" @close="confirmingDelete = false" @confirm="confirmDelete" />
    </AdminLayout>
</template>
