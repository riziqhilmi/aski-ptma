<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ faqs: Object });

const remove = (faq) => {
    if (confirm(`Arsipkan FAQ "${faq.question}"?`)) {
        router.delete(route('admin.faqs.destroy', faq.id));
    }
};
</script>

<template>
    <Head title="FAQ" />
    <AdminLayout>
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-aski-700">HELP CENTER</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">FAQ Bantuan</h1>
            </div>
            <Link :href="route('admin.faqs.create')" class="btn-primary">Tambah FAQ</Link>
        </div>

        <div class="card mt-7 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-aski-50 text-aski-900">
                    <tr><th class="p-4">Pertanyaan</th><th class="p-4">Urutan</th><th class="p-4">Status</th><th class="p-4"></th></tr>
                </thead>
                <tbody>
                    <tr v-for="faq in faqs.data" :key="faq.id" class="border-t border-slate-100">
                        <td class="p-4 font-semibold text-aski-900">{{ faq.question }}</td>
                        <td class="p-4">{{ faq.sort_order }}</td>
                        <td class="p-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="faq.is_published ? 'bg-aski-100 text-aski-800' : 'bg-slate-100 text-slate-600'">
                                {{ faq.is_published ? 'Terbit' : 'Draft' }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <Link :href="route('admin.faqs.edit', faq.id)" class="font-semibold text-aski-700">Ubah</Link>
                            <button class="ml-3 text-red-600" @click="remove(faq)">Arsipkan</button>
                        </td>
                    </tr>
                    <tr v-if="faqs.data.length === 0"><td colspan="4" class="p-8 text-center text-slate-500">Belum ada FAQ.</td></tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
