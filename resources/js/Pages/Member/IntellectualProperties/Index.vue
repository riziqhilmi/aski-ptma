<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { formatDateOnly } from '@/utils/date.js';
import PaginationLinks from '@/Components/PaginationLinks.vue';

defineProps({ records: Object, canCreate: Boolean });
</script>

<template>
    <Head title="Data HKI Saya" />
    <MemberLayout>
        <div class="space-y-6">
            <header class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <Link :href="route('member.dashboard')" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-aski-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
                        <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.78 15.53a.75.75 0 0 1-1.06 0l-5-5a.75.75 0 0 1 0-1.06l5-5a.75.75 0 1 1 1.06 1.06L8.31 10l4.47 4.47a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" /></svg>
                        Dashboard
                    </Link>
                    <p class="mt-5 text-xs font-bold uppercase tracking-[0.16em] text-aski-700">Kekayaan Intelektual</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-aski-900 sm:text-3xl">Data HKI Saya</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Kelola dan tinjau informasi kekayaan intelektual yang terdaftar pada akun Anda.</p>
                </div>
                <Link v-if="canCreate" :href="route('member.intellectual-properties.create')" class="btn-primary inline-flex min-h-11 items-center justify-center gap-2 self-start sm:self-auto">
                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 4.25a.75.75 0 0 1 .75.75v4.25H15a.75.75 0 0 1 0 1.5h-4.25V15a.75.75 0 0 1-1.5 0v-4.25H5a.75.75 0 0 1 0-1.5h4.25V5a.75.75 0 0 1 .75-.75Z" /></svg>
                    Tambah Data HKI
                </Link>
            </header>

            <div v-if="!canCreate" role="status" class="flex gap-3 rounded-xl border border-gold-400 bg-gold-100 p-4 text-sm text-aski-900 sm:items-center">
                <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-aski-700 sm:mt-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0Zm-8-4.75a.75.75 0 0 0-.75.75v4a.75.75 0 0 0 1.5 0V6a.75.75 0 0 0-.75-.75Zm0 8.5a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z" clip-rule="evenodd" /></svg>
                <p>Input data HKI tersedia setelah pembayaran keanggotaan Anda terverifikasi. <Link :href="route('member.invoices.index')" class="font-semibold underline underline-offset-2 hover:text-aski-700">Lihat tagihan</Link></p>
            </div>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="ip-list-heading">
                <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <h2 id="ip-list-heading" class="text-base font-semibold text-aski-900">Daftar kekayaan intelektual</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ records.total }} data terdaftar</p>
                    </div>
                    <span class="inline-flex w-fit items-center rounded-full bg-aski-50 px-3 py-1 text-xs font-semibold text-aski-800">Milik akun Anda</span>
                </div>

                <div v-if="records.data.length" tabindex="0" role="region" aria-label="Tabel data HKI, gunakan gulir horizontal untuk melihat kolom lain" class="overflow-x-auto focus-visible:outline focus-visible:outline-2 focus-visible:outline-inset focus-visible:outline-aski-700">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th scope="col" class="px-6 py-3.5">Karya / Invensi</th>
                                <th scope="col" class="px-5 py-3.5">Jenis HKI</th>
                                <th scope="col" class="px-5 py-3.5">Nomor permohonan</th>
                                <th scope="col" class="px-5 py-3.5">Tanggal permohonan</th>
                                <th scope="col" class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="record in records.data" :key="record.id" class="transition-colors hover:bg-slate-50/70">
                                <td class="max-w-sm px-6 py-4">
                                    <p class="truncate font-semibold text-slate-900" :title="record.title">{{ record.title }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ record.rights_holder || 'Pemegang hak belum dicantumkan' }}</p>
                                </td>
                                <td class="px-5 py-4"><span class="inline-flex rounded-full border border-aski-100 bg-aski-50 px-2.5 py-1 text-xs font-semibold text-aski-800">{{ record.ip_type }}</span></td>
                                <td class="px-5 py-4 font-medium text-slate-700">{{ record.application_number || '—' }}</td>
                                <td class="px-5 py-4 whitespace-nowrap text-slate-600">{{ formatDateOnly(record.application_date) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <Link :href="route('member.intellectual-properties.show', record.id)" class="inline-flex min-h-9 items-center gap-1 rounded-lg px-3 text-sm font-semibold text-aski-800 transition hover:bg-aski-50 hover:text-aski-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
                                        Lihat detail <span aria-hidden="true">→</span>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="px-5 py-14 text-center sm:px-8">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-aski-50 text-aski-700">
                        <svg aria-hidden="true" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M8 4.75h6.5L19 9.25v9A1.75 1.75 0 0 1 17.25 20h-10A1.75 1.75 0 0 1 5.5 18.25v-11A2.5 2.5 0 0 1 8 4.75Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 5v4.5h4.5M8.5 13h7m-7 3h7"/></svg>
                    </span>
                    <h3 class="mt-4 text-base font-semibold text-slate-900">Belum ada data HKI</h3>
                    <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500">Data kekayaan intelektual yang Anda tambahkan akan muncul di halaman ini.</p>
                    <Link v-if="canCreate" :href="route('member.intellectual-properties.create')" class="btn-primary mt-5 inline-flex min-h-10 items-center justify-center">Tambah Data HKI</Link>
                    <p v-else class="mt-4 text-sm text-slate-600">Selesaikan verifikasi pembayaran keanggotaan untuk mulai menginput data.</p>
                </div>
            </section>
            <PaginationLinks v-if="records.links?.length > 3" :links="records.links" />
        </div>
    </MemberLayout>
</template>
