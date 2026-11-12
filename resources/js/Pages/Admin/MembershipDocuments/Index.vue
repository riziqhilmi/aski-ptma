<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PaginationLinks from '@/Components/PaginationLinks.vue';
import { formatDateTime } from '@/utils/date';

const props = defineProps({ documents: Object, filters: Object });

const searchTerm = ref(props.filters.q || '');
const typeFilter = ref(props.filters.type || '');
let searchTimer;

const applyFilters = () => {
    window.clearTimeout(searchTimer);
    router.get(route('admin.membership-documents.index'), {
        q: searchTerm.value,
        type: typeFilter.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

watch(searchTerm, (value) => {
    window.clearTimeout(searchTimer);
    if (value === (props.filters.q || '')) return;
    searchTimer = window.setTimeout(applyFilters, 450);
});

watch(() => props.filters, (filters) => {
    searchTerm.value = filters.q || '';
    typeFilter.value = filters.type || '';
}, { deep: true });

onBeforeUnmount(() => window.clearTimeout(searchTimer));
</script>

<template>
    <Head title="Dokumen Keanggotaan" />
    <AdminLayout>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-aski-700">KEANGGOTAAN</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">Dokumen Keanggotaan</h1>
            </div>

            <form class="grid w-full grid-cols-[minmax(0,1fr)_minmax(8rem,11rem)] items-center gap-2 sm:max-w-2xl" @submit.prevent>
                <div class="flex min-h-11 min-w-0 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 transition-colors focus-within:border-aski-500 focus-within:ring-1 focus-within:ring-aski-500">
                    <label for="membership-document-search" class="sr-only">Cari nama, email, atau nomor dokumen</label>
                    <span id="membership-document-search-help" class="sr-only">Pencarian berjalan otomatis setelah Anda berhenti mengetik.</span>
                    <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.44 9.79l3.63 3.63a.75.75 0 1 0 1.06-1.06L13 11.73A5.5 5.5 0 0 0 8.5 3ZM4.5 8.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" /></svg>
                    <input id="membership-document-search" v-model="searchTerm" name="q" type="search" autocomplete="off" aria-describedby="membership-document-search-help" placeholder="Cari member atau nomor dokumen" class="h-10 min-w-0 flex-1 border-0 bg-transparent p-0 text-sm shadow-none outline-none placeholder:text-slate-400 focus:border-0 focus:outline-none focus:ring-0 focus-visible:outline-none" />
                </div>
                <div class="min-w-0">
                    <label for="membership-document-type" class="sr-only">Filter jenis dokumen</label>
                    <select id="membership-document-type" v-model="typeFilter" name="type" class="min-h-11 w-full min-w-0 rounded-xl border-slate-200 bg-white px-3 text-sm focus:border-aski-500 focus:ring-aski-500" @change="applyFilters">
                        <option value="">Semua jenis</option>
                        <option value="certificate">Sertifikat</option>
                        <option value="card">Kartu Anggota</option>
                    </select>
                </div>
                <Link v-if="filters.q || filters.type" :href="route('admin.membership-documents.index')" class="col-span-2 justify-self-end rounded-lg px-2 py-1 text-xs font-semibold text-aski-700 hover:bg-aski-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">Hapus filter</Link>
            </form>
        </div>

        <section class="card mt-5 overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 class="font-semibold text-aski-900">Daftar Dokumen</h2>
                <p class="mt-1 text-sm text-slate-500">{{ documents.from || 0 }}–{{ documents.to || 0 }} dari {{ documents.total }} dokumen</p>
            </div>
            <div tabindex="0" role="region" aria-label="Tabel dokumen keanggotaan, gunakan gulir horizontal untuk melihat kolom lain" class="overflow-x-auto focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
                <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-aski-50 text-aski-900">
                        <tr>
                            <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Nomor</th>
                            <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Member</th>
                            <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Jenis</th>
                            <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Tanggal Terbit</th>
                            <th scope="col" class="px-4 py-3.5 text-right font-semibold sm:px-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="document in documents.data" :key="document.id" class="transition-colors hover:bg-slate-50/70">
                            <td class="whitespace-nowrap px-4 py-3.5 font-medium text-slate-900 sm:px-6">{{ document.number }}</td>
                            <td class="px-4 py-3.5 sm:px-6">
                                <p class="font-semibold text-slate-900">{{ document.user.name }}</p>
                                <p class="mt-0.5 break-all text-xs text-slate-500 sm:text-sm">{{ document.user.email }}</p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-slate-700 sm:px-6">{{ document.type === 'certificate' ? 'Sertifikat' : 'Kartu Anggota' }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-slate-600 sm:px-6">{{ formatDateTime(document.issued_at) }}</td>
                            <td class="px-4 py-3.5 sm:px-6">
                                <div class="flex justify-end gap-3 whitespace-nowrap">
                                    <a class="font-semibold text-aski-700 hover:text-aski-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700" target="_blank" :href="route('admin.membership-documents.preview', document.id)">Preview</a>
                                    <a class="font-semibold text-aski-700 hover:text-aski-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700" :href="route('admin.membership-documents.download', document.id)">Unduh</a>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="documents.data.length === 0">
                            <td colspan="5" class="px-4 py-10 text-center text-slate-500 sm:px-6">Tidak ada dokumen yang sesuai dengan pencarian atau filter.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
        <PaginationLinks :links="documents.links" />
    </AdminLayout>
</template>
