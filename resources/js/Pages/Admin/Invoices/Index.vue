<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatDateOnly } from '@/utils/date.js';
import StatusBadge from '@/Components/StatusBadge.vue';
import PaginationLinks from '@/Components/PaginationLinks.vue';

const props = defineProps({ invoices: Object, filters: Object, statusLabels: Object });
const searchTerm = ref(props.filters.q || '');
let searchTimer;

const applyFilters = () => {
    window.clearTimeout(searchTimer);
    router.get(route('admin.invoices.index'), {
        q: searchTerm.value,
        status: props.filters.status || '',
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const filterStatus = (event) => {
    window.clearTimeout(searchTimer);
    router.get(route('admin.invoices.index'), {
        q: searchTerm.value,
        status: event.target.value,
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
}, { deep: true });

onBeforeUnmount(() => window.clearTimeout(searchTimer));

const format = (value) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(value);
</script>

<template>
    <Head title="Tagihan" />
    <AdminLayout>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-aski-700">TAGIHAN</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">Seluruh Tagihan</h1>
            </div>
        </div>

        <form class="mt-4 flex w-full items-center justify-end gap-2" role="search" @submit.prevent>
            <div class="flex min-h-10 w-full min-w-0 items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 transition-colors focus-within:border-aski-500 focus-within:ring-1 focus-within:ring-aski-500 sm:w-80">
                <label for="invoice-search" class="sr-only">Cari nomor tagihan, nama, atau email member</label>
                <span id="invoice-search-help" class="sr-only">Pencarian otomatis setelah Anda berhenti mengetik.</span>
                <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.44 9.79l3.63 3.63a.75.75 0 1 0 1.06-1.06L13 11.73A5.5 5.5 0 0 0 8.5 3ZM4.5 8.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" /></svg>
                <input id="invoice-search" v-model="searchTerm" name="q" type="search" autocomplete="off" aria-describedby="invoice-search-help" placeholder="Cari tagihan atau member" class="h-9 min-w-0 flex-1 border-0 bg-transparent p-0 text-sm shadow-none outline-none placeholder:text-slate-400 focus:border-0 focus:outline-none focus:ring-0 focus-visible:outline-none" />
            </div>
            <label class="sr-only" for="invoice-status-filter">Filter status tagihan</label>
            <select id="invoice-status-filter" :value="filters.status" class="min-h-10 w-36 shrink-0 rounded-lg border-slate-200 bg-white px-2.5 text-sm focus:border-aski-500 focus:ring-aski-500 sm:w-40" @change="filterStatus">
                <option value="">Semua status</option>
                <option value="unpaid">Belum dibayar</option>
                <option value="paid">Dibayar</option>
            </select>
            <Link v-if="filters.q" :href="route('admin.invoices.index', { status: filters.status || undefined })" class="shrink-0 rounded-lg px-2.5 py-2 text-xs font-semibold text-aski-700 hover:bg-aski-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">Hapus</Link>
        </form>

        <div class="card mt-5 overflow-hidden">
            <div tabindex="0" role="region" aria-label="Tabel tagihan, gunakan gulir horizontal untuk melihat kolom lain" class="overflow-x-auto focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
                <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-aski-50 text-aski-900">
                        <tr>
                            <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Nomor</th>
                            <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Member</th>
                            <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Nominal</th>
                            <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Jatuh Tempo</th>
                            <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="invoice in invoices.data" :key="invoice.id" class="transition-colors hover:bg-slate-50/70">
                            <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-slate-900 sm:px-6">{{ invoice.number }}</td>
                            <td class="px-4 py-3.5 sm:px-6">
                                <p class="font-semibold text-slate-900">{{ invoice.user.name }}</p>
                                <p class="mt-0.5 break-all text-xs text-slate-500 sm:text-sm">{{ invoice.user.email }}</p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-slate-700 sm:px-6">{{ format(invoice.amount) }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-slate-600 sm:px-6">{{ formatDateOnly(invoice.due_at) }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <StatusBadge :status="invoice.status" :label="statusLabels[invoice.status]" />
                                    <Link :href="route('admin.invoices.view', invoice.id)" class="font-semibold text-aski-700 hover:text-aski-900">Lihat Invoice</Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="invoices.data.length === 0">
                            <td colspan="5" class="px-4 py-10 text-center text-slate-500 sm:px-6">Tidak ada tagihan yang cocok dengan pencarian dan filter.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <PaginationLinks :links="invoices.links" />
    </AdminLayout>
</template>
