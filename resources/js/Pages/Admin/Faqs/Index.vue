<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PaginationLinks from '@/Components/PaginationLinks.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import ConfirmActionDialog from '@/Components/ConfirmActionDialog.vue';
import { ref } from 'vue';

defineProps({ faqs: Object });
const pendingFaq = ref(null);
const confirmingArchive = ref(false);
const archiveProcessing = ref(false);

const remove = (faq) => {
    pendingFaq.value = faq;
    confirmingArchive.value = true;
};

const confirmArchive = () => {
    if (!pendingFaq.value || archiveProcessing.value) return;
    archiveProcessing.value = true;
    router.delete(route('admin.faqs.destroy', pendingFaq.value.id), {
        onSuccess: () => { confirmingArchive.value = false; pendingFaq.value = null; },
        onFinish: () => { archiveProcessing.value = false; },
    });
};
</script>

<template>
    <Head title="FAQ" />
    <AdminLayout>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-aski-700">BANTUAN · FAQ</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">FAQ Bantuan</h1>
            </div>
            <Link :href="route('admin.faqs.create')" class="btn-primary inline-flex min-h-11 items-center justify-center">Tambah FAQ</Link>
        </div>

        <section class="card mt-6 overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6"><h2 class="font-semibold text-aski-900">Daftar Pertanyaan</h2><p class="mt-1 text-sm text-slate-500">{{ faqs.from || 0 }}–{{ faqs.to || 0 }} dari {{ faqs.total }} FAQ</p></div>
            <div tabindex="0" role="region" aria-label="Tabel FAQ, gunakan gulir horizontal untuk melihat kolom lain" class="overflow-x-auto focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-aski-50 text-aski-900">
                    <tr><th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Pertanyaan</th><th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Urutan</th><th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Status</th><th scope="col" class="px-4 py-3.5 text-right font-semibold sm:px-6">Aksi</th></tr>
                </thead>
                <tbody>
                    <tr v-for="faq in faqs.data" :key="faq.id" class="transition-colors hover:bg-slate-50/70">
                        <td class="max-w-2xl px-4 py-3.5 font-semibold text-aski-900 sm:px-6">{{ faq.question }}</td>
                        <td class="px-4 py-3.5 text-slate-600 sm:px-6">{{ faq.sort_order }}</td>
                        <td class="px-4 py-3.5 sm:px-6">
                            <StatusBadge :status="faq.is_published ? 'published' : 'draft'" :label="faq.is_published ? 'Terbit' : 'Draft'" />
                        </td>
                        <td class="px-4 py-3.5 text-right sm:px-6">
                            <div class="flex justify-end gap-1"><Link :href="route('admin.faqs.edit', faq.id)" class="inline-flex min-h-9 items-center rounded-lg px-3 font-semibold text-aski-700 hover:bg-aski-50">Ubah</Link>
                            <button class="inline-flex min-h-9 items-center rounded-lg px-3 font-semibold text-red-600 hover:bg-red-50" @click="remove(faq)">Arsipkan</button></div>
                        </td>
                    </tr>
                    <tr v-if="faqs.data.length === 0"><td colspan="4" class="px-4 py-10 text-center text-slate-500 sm:px-6">Belum ada FAQ.</td></tr>
                </tbody>
            </table></div>
        </section>
        <PaginationLinks :links="faqs.links" />
        <ConfirmActionDialog :show="confirmingArchive" title="Arsipkan FAQ?" description="FAQ ini tidak lagi tersedia pada halaman bantuan publik." :item="pendingFaq?.question" action-label="Arsipkan FAQ" phrase="ARSIPKAN" :processing="archiveProcessing" @close="confirmingArchive = false" @confirm="confirmArchive" />
    </AdminLayout>
</template>
