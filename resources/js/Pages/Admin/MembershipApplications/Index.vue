<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import PaginationLinks from '@/Components/PaginationLinks.vue';

const props = defineProps({
    applications: Object,
    filters: Object,
    statusLabels: Object,
});

const searchTerm = ref(props.filters.q || '');
const typeFilter = ref(props.filters.type || '');
const statusFilter = ref(props.filters.status || '');
let searchTimer;

const applyFilters = () => {
    window.clearTimeout(searchTimer);
    router.get(route('admin.applications.index'), {
        q: searchTerm.value,
        type: typeFilter.value,
        status: statusFilter.value,
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
    statusFilter.value = filters.status || '';
}, { deep: true });

onBeforeUnmount(() => window.clearTimeout(searchTimer));

const typeLabel = (type) => ({ individual: 'Individu', institution: 'Institusi' }[type] || 'Belum dipilih');
</script>

<template>
    <Head title="Permohonan Keanggotaan" />
    <AdminLayout>
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-aski-700">KEANGGOTAAN</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">Permohonan Masuk</h1>
            </div>

            <form class="grid w-full grid-cols-[minmax(0,1.1fr)_minmax(5.5rem,.8fr)_minmax(6.5rem,1fr)] items-center gap-2 lg:w-[58%] lg:grid-cols-[minmax(13rem,1fr)_10rem_13rem]" @submit.prevent>
                <div class="flex min-h-10 min-w-0 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 transition-colors focus-within:border-aski-500 sm:gap-2 sm:px-3">
                    <label for="application-search" class="sr-only">Cari nama, email, atau nomor anggota</label>
                    <span id="application-search-help" class="sr-only">Pencarian otomatis setelah Anda berhenti mengetik.</span>
                    <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.44 9.79l3.63 3.63a.75.75 0 1 0 1.06-1.06L13 11.73A5.5 5.5 0 0 0 8.5 3ZM4.5 8.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" /></svg>
                    <input id="application-search" v-model="searchTerm" name="q" type="search" autocomplete="off" aria-describedby="application-search-help" placeholder="Cari pemohon" class="h-9 min-w-0 flex-1 border-0 bg-transparent p-0 text-xs shadow-none outline-none placeholder:text-slate-400 focus:border-0 focus:outline-none focus:ring-0 focus-visible:outline-none sm:text-sm" />
                </div>
                <div class="min-w-0">
                    <label for="application-type-filter" class="sr-only">Filter tipe akun</label>
                    <select id="application-type-filter" v-model="typeFilter" name="type" class="min-h-10 w-full min-w-0 rounded-lg border-slate-200 px-1.5 text-xs focus:border-aski-500 focus:ring-aski-500 sm:px-2.5 sm:text-sm" @change="applyFilters">
                        <option value="">Semua tipe</option>
                        <option value="individual">Individu</option>
                        <option value="institution">Institusi</option>
                        <option value="unselected">Belum dipilih</option>
                    </select>
                </div>
                <div class="min-w-0">
                    <label for="application-status-filter" class="sr-only">Filter status permohonan</label>
                    <select id="application-status-filter" v-model="statusFilter" name="status" class="min-h-10 w-full min-w-0 rounded-lg border-slate-200 px-1.5 text-xs focus:border-aski-500 focus:ring-aski-500 sm:px-2.5 sm:text-sm" @change="applyFilters">
                        <option value="">Semua status</option>
                        <option v-for="(label, status) in statusLabels" :key="status" :value="status">{{ label }}</option>
                    </select>
                </div>
                <Link
                    v-if="filters.q || filters.type || filters.status"
                    :href="route('admin.applications.index')"
                    class="col-span-3 justify-self-end rounded-lg px-2 py-1 text-xs font-semibold text-aski-700 hover:bg-aski-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700"
                >
                    Hapus filter
                </Link>
            </form>
        </div>

        <div class="card mt-5 overflow-hidden">
            <div tabindex="0" role="region" aria-label="Tabel permohonan, gunakan gulir horizontal untuk melihat kolom lain" class="overflow-x-auto focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
                <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-aski-50 text-aski-900">
                        <tr>
                            <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Pemohon</th>
                            <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Tipe</th>
                            <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Status</th>
                            <th scope="col" class="px-4 py-3.5 sm:px-6"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="application in applications.data" :key="application.id" class="transition-colors hover:bg-slate-50/70">
                            <td class="px-4 py-3.5 sm:px-6 sm:py-4">
                                <p class="font-semibold text-slate-900">{{ application.user.name }}</p>
                                <p class="mt-0.5 break-all text-xs text-slate-500 sm:text-sm">{{ application.user.email }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-slate-700 sm:px-6 sm:py-4">{{ typeLabel(application.user.member_profile?.account_type) }}</td>
                            <td class="px-4 py-3.5 sm:px-6 sm:py-4"><StatusBadge :status="application.status" :label="statusLabels[application.status]" /></td>
                            <td class="px-4 py-3.5 text-right sm:px-6 sm:py-4">
                                <Link :href="route('admin.applications.show', application.id)" class="inline-flex min-h-9 items-center rounded-lg px-2 font-semibold text-aski-700 hover:bg-aski-50 hover:text-aski-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">Detail</Link>
                            </td>
                        </tr>
                        <tr v-if="applications.data.length === 0">
                            <td colspan="4" class="px-4 py-10 text-center text-slate-500 sm:px-6">Belum ada permohonan yang sesuai.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <PaginationLinks :links="applications.links" />
    </AdminLayout>
</template>
