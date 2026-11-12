<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PaginationLinks from '@/Components/PaginationLinks.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({ accounts: Object, filters: Object });

const searchTerm = ref(props.filters.q || '');
const typeFilter = ref(props.filters.type || '');
const statusFilter = ref(props.filters.status || '');
let searchTimer;

const applyFilters = () => {
    window.clearTimeout(searchTimer);
    router.get(route('admin.accounts.index'), {
        type: typeFilter.value,
        status: statusFilter.value,
        q: searchTerm.value,
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
const completionLabel = (status) => status === 'lengkap' ? 'Lengkap' : 'Belum lengkap';
</script>

<template>
    <Head title="Manajemen Akun" />
    <AdminLayout>
        <div class="mx-auto w-full max-w-7xl">
            <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-aski-700">Keanggotaan</p>
                    <h1 class="mt-1 text-2xl font-bold text-aski-900 sm:text-3xl">Manajemen Akun</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600">Kelola profil dan lihat status kelengkapan data Member.</p>
                </div>
                <div class="inline-flex min-h-10 w-fit max-w-full shrink-0 items-center gap-2.5 self-start rounded-xl border border-aski-100 bg-white px-3 py-2 text-sm text-slate-600 shadow-sm sm:self-auto">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-aski-50 text-aski-700" aria-hidden="true">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3.5 16.2a4.5 4.5 0 0 1 9 0v.3h-9v-.3ZM14.5 6a2.5 2.5 0 1 1-1.15 4.72 4 4 0 0 0 0-4.44c.35-.18.74-.28 1.15-.28ZM15 12a4.5 4.5 0 0 1 2.5 4.05v.45h-3.5v-.3c0-1.28-.4-2.46-1.08-3.43A4.4 4.4 0 0 1 15 12Z" /></svg>
                    </span>
                    <span class="whitespace-nowrap"><strong class="font-bold text-aski-900">{{ accounts.total }}</strong> akun</span>
                </div>
            </header>

            <form class="mt-5 rounded-xl border border-slate-200 bg-white p-2 shadow-sm sm:p-3" @submit.prevent>
                <div class="grid grid-cols-[minmax(0,1.1fr)_minmax(6rem,.9fr)_minmax(7rem,1fr)] items-center gap-2 lg:grid-cols-[minmax(14rem,1fr)_10rem_13rem]">
                    <div class="flex min-h-10 min-w-0 items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 transition-colors focus-within:border-aski-500 sm:px-3">
                        <label for="account-search" class="sr-only">Cari nama, email, atau nomor anggota</label>
                        <span id="account-search-help" class="sr-only">Pencarian otomatis setelah Anda berhenti mengetik.</span>
                        <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.44 9.79l3.63 3.63a.75.75 0 1 0 1.06-1.06L13 11.73A5.5 5.5 0 0 0 8.5 3ZM4.5 8.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" /></svg>
                        <input id="account-search" v-model="searchTerm" name="q" type="search" autocomplete="off" aria-describedby="account-search-help" placeholder="Cari member" class="h-9 min-w-0 flex-1 border-0 bg-transparent p-0 text-sm shadow-none outline-none placeholder:text-slate-400 focus:border-0 focus:outline-none focus:ring-0 focus-visible:outline-none" />
                    </div>
                    <div>
                        <label for="account-type-filter" class="sr-only">Tipe akun</label>
                        <select id="account-type-filter" v-model="typeFilter" name="type" class="min-h-10 w-full min-w-0 rounded-lg border-slate-200 px-1.5 text-xs focus:border-aski-500 focus:ring-aski-500 sm:px-2.5 sm:text-sm" @change="applyFilters">
                            <option value="">Semua tipe</option>
                            <option value="individual">Individu</option>
                            <option value="institution">Institusi</option>
                            <option value="unselected">Belum memilih tipe</option>
                        </select>
                    </div>
                    <div>
                        <label for="account-status-filter" class="sr-only">Kelengkapan profil</label>
                        <select id="account-status-filter" v-model="statusFilter" name="status" class="min-h-10 w-full min-w-0 rounded-lg border-slate-200 px-1.5 text-xs focus:border-aski-500 focus:ring-aski-500 sm:px-2.5 sm:text-sm" @change="applyFilters">
                            <option value="">Semua status</option>
                            <option value="belum_lengkap">Belum lengkap</option>
                            <option value="lengkap">Lengkap</option>
                        </select>
                    </div>
                </div>
                <Link
                    v-if="filters.type || filters.status || filters.q"
                    :href="route('admin.accounts.index')"
                    class="mt-1 inline-flex min-h-8 items-center rounded-lg px-2 text-xs font-semibold text-aski-700 hover:bg-aski-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700"
                >
                    Hapus filter
                </Link>
            </form>

            <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="accounts-list-heading">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-4 sm:px-5">
                    <div>
                        <h2 id="accounts-list-heading" class="font-semibold text-aski-900">Daftar Member</h2>
                        <p class="mt-0.5 text-xs text-slate-500">{{ accounts.from || 0 }}–{{ accounts.to || 0 }} dari {{ accounts.total }} akun</p>
                    </div>
                </div>

                <div v-if="accounts.data.length" tabindex="0" role="region" aria-label="Tabel akun Member, geser horizontal untuk melihat semua kolom" class="overflow-x-auto focus-visible:outline focus-visible:outline-2 focus-visible:outline-aski-700">
                    <table class="w-full min-w-[620px] text-left text-sm">
                            <thead class="border-b border-slate-200 bg-aski-50/70 text-xs uppercase tracking-wide text-aski-800">
                                <tr>
                                    <th scope="col" class="px-4 py-3 font-semibold sm:px-5">Akun Member</th>
                                    <th scope="col" class="px-4 py-3 font-semibold sm:px-5">Tipe</th>
                                    <th scope="col" class="px-4 py-3 font-semibold sm:px-5">Status profil</th>
                                    <th scope="col" class="px-4 py-3 text-right font-semibold sm:px-5">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="account in accounts.data" :key="account.id" class="transition-colors hover:bg-slate-50/80">
                                    <td class="px-4 py-3 sm:px-5 sm:py-3.5">
                                        <div class="min-w-0">
                                            <p class="max-w-[22rem] truncate font-semibold text-slate-900">{{ account.name }}</p>
                                            <p class="mt-0.5 max-w-[22rem] truncate text-xs text-slate-500">{{ account.email }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 sm:px-5 sm:py-3.5">{{ typeLabel(account.account_type) }}</td>
                                    <td class="px-4 py-3 sm:px-5 sm:py-3.5"><StatusBadge :status="account.completion_status === 'lengkap' ? 'complete' : 'incomplete'" :label="completionLabel(account.completion_status)" /></td>
                                    <td class="px-4 py-3 text-right sm:px-5 sm:py-3.5">
                                        <Link :href="route('admin.accounts.show', account.id)" class="inline-flex min-h-9 items-center gap-1 rounded-lg px-2.5 font-semibold text-aski-700 transition hover:bg-aski-50 hover:text-aski-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
                                            Detail <span aria-hidden="true">→</span>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                    </table>
                </div>

                <div v-else class="px-5 py-12 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-aski-50 text-aski-700" aria-hidden="true">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m16 0v-2a4 4 0 0 0-3-3.87M14 3.13a4 4 0 0 1 0 7.75M14 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" /></svg>
                    </div>
                    <h3 class="mt-4 font-semibold text-aski-900">Akun tidak ditemukan</h3>
                    <p class="mt-1 text-sm text-slate-600">Coba ubah kata kunci atau pilihan filter.</p>
                    <Link :href="route('admin.accounts.index')" class="mt-4 inline-flex min-h-10 items-center rounded-lg px-3 text-sm font-semibold text-aski-700 hover:bg-aski-50">Tampilkan semua akun</Link>
                </div>

                <div class="border-t border-slate-100 px-4 py-3 sm:px-5">
                    <PaginationLinks :links="accounts.links" />
                </div>
            </section>
        </div>
    </AdminLayout>
</template>
