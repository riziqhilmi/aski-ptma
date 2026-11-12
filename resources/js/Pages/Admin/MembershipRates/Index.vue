<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PaginationLinks from '@/Components/PaginationLinks.vue';
import ConfirmActionDialog from '@/Components/ConfirmActionDialog.vue';
import { formatDateOnly } from '@/utils/date.js';

const props = defineProps({ rates: Object, filters: Object });
const pendingRate = ref(null);
const confirmingArchive = ref(false);
const archiveProcessing = ref(false);

const searchTerm = ref(props.filters.q || '');
let searchTimer;

const applySearch = () => {
    window.clearTimeout(searchTimer);
    router.get(route('admin.rates.index'), { q: searchTerm.value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

watch(searchTerm, (value) => {
    window.clearTimeout(searchTimer);
    if (value === (props.filters.q || '')) return;
    searchTimer = window.setTimeout(applySearch, 450);
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

const archive = (rate) => {
    pendingRate.value = rate;
    confirmingArchive.value = true;
};

const confirmArchive = () => {
    if (!pendingRate.value || archiveProcessing.value) return;
    archiveProcessing.value = true;
    router.delete(route('admin.rates.destroy', pendingRate.value.id), {
        onSuccess: () => { confirmingArchive.value = false; pendingRate.value = null; },
        onFinish: () => { archiveProcessing.value = false; },
    });
};
</script>

<template>
    <Head title="Tarif Anggota" />
    <AdminLayout>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-aski-700">KEUANGAN</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">Tarif Anggota</h1>
            </div>
            <Link :href="route('admin.rates.create')" class="btn-primary inline-flex min-h-11 items-center justify-center">Tambah Tarif</Link>
        </div>

        <form class="card mt-5 flex items-center gap-3 p-3 sm:p-4" role="search" @submit.prevent>
            <div class="flex min-h-11 min-w-0 flex-1 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 transition-colors focus-within:border-aski-500 focus-within:ring-1 focus-within:ring-aski-500">
                <label for="membership-rate-search" class="sr-only">Cari nama tarif anggota</label>
                <span id="membership-rate-search-help" class="sr-only">Pencarian berjalan otomatis setelah Anda berhenti mengetik.</span>
                <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.44 9.79l3.63 3.63a.75.75 0 1 0 1.06-1.06L13 11.73A5.5 5.5 0 0 0 8.5 3ZM4.5 8.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" /></svg>
                <input id="membership-rate-search" v-model="searchTerm" name="q" type="search" autocomplete="off" aria-describedby="membership-rate-search-help" placeholder="Cari nama tarif" class="h-10 min-w-0 flex-1 border-0 bg-transparent p-0 text-sm shadow-none outline-none placeholder:text-slate-400 focus:border-0 focus:outline-none focus:ring-0 focus-visible:outline-none" />
            </div>
            <Link v-if="filters.q" :href="route('admin.rates.index')" class="shrink-0 rounded-lg px-3 py-2 text-sm font-semibold text-aski-700 hover:bg-aski-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">Hapus</Link>
        </form>

        <section class="card mt-5 overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 class="font-semibold text-aski-900">Daftar Tarif</h2>
                <p class="mt-1 text-sm text-slate-500">{{ rates.from || 0 }}–{{ rates.to || 0 }} dari {{ rates.total }} tarif</p>
            </div>
            <div tabindex="0" role="region" aria-label="Tabel tarif anggota, gunakan gulir horizontal untuk melihat kolom lain" class="overflow-x-auto focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
                <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-aski-50 text-aski-900">
                        <tr>
                            <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Nama Tarif</th>
                            <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Tipe Akun</th>
                            <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Nominal</th>
                            <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Periode Tarif</th>
                            <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Durasi</th>
                            <th scope="col" class="px-4 py-3.5 text-right font-semibold sm:px-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="rate in rates.data" :key="rate.id" class="transition-colors hover:bg-slate-50/70">
                            <td class="px-4 py-3.5 font-semibold text-slate-900 sm:px-6">{{ rate.name }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-slate-700 sm:px-6">{{ rate.account_type === 'institution' ? 'Institusi' : 'Individu' }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 font-medium text-slate-900 sm:px-6">{{ format(rate.amount) }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-slate-600 sm:px-6">{{ formatDateOnly(rate.active_from) }} – {{ rate.active_until ? formatDateOnly(rate.active_until) : 'Tanpa batas' }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-slate-700 sm:px-6">{{ rate.duration_months }} bulan</td>
                            <td class="px-4 py-3.5 text-right sm:px-6">
                                <div class="flex items-center justify-end gap-1 whitespace-nowrap">
                                    <Link :href="route('admin.rates.edit', rate.id)" class="inline-flex min-h-9 items-center rounded-lg px-3 font-semibold text-aski-700 hover:bg-aski-50 hover:text-aski-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">Ubah</Link>
                                    <button type="button" class="inline-flex min-h-9 items-center rounded-lg px-3 font-semibold text-red-600 hover:bg-red-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600" @click="archive(rate)">Arsipkan</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="rates.data.length === 0">
                            <td colspan="6" class="px-4 py-10 text-center text-slate-500 sm:px-6">{{ filters.q ? 'Tidak ada tarif yang cocok dengan pencarian.' : 'Belum ada tarif keanggotaan.' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
        <PaginationLinks :links="rates.links" />
        <ConfirmActionDialog
            :show="confirmingArchive"
            title="Arsipkan tarif anggota?"
            description="Tarif ini tidak dapat dipilih untuk tagihan baru. Invoice yang sudah diterbitkan tidak berubah."
            :item="pendingRate?.name"
            action-label="Arsipkan tarif"
            phrase="ARSIPKAN"
            :processing="archiveProcessing"
            @close="confirmingArchive = false"
            @confirm="confirmArchive"
        />
    </AdminLayout>
</template>
