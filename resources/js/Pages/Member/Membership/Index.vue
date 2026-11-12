<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { formatDateOnly, formatDateTime } from '@/utils/date.js';
import PaginationLinks from '@/Components/PaginationLinks.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

defineProps({ activeMembership: Object, memberships: Object, applications: Array, statusLabels: Object });
const membershipStatusLabels = { active: 'Aktif', upcoming: 'Akan dimulai', expired: 'Berakhir', inactive: 'Tidak aktif' };
const timelineHistories = (histories) => [...histories].reverse();
</script>

<template>
    <Head title="Ringkasan Keanggotaan" />
    <MemberLayout>
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-aski-700">Keanggotaan</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">Ringkasan Keanggotaan</h1>
                <p class="mt-2 text-sm text-slate-600">Lihat status dan riwayat keanggotaan Anda.</p>
            </div>
        </header>

        <section class="card mt-6 overflow-hidden" aria-labelledby="membership-summary-title">
            <div class="flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6 lg:p-7">
                <div class="flex min-w-0 items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-aski-50 text-aski-700">
                        <svg aria-hidden="true" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m16 0v-2a4 4 0 0 0-3-3.87M14 3.13a4 4 0 0 1 0 7.75M14 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status Keanggotaan</p>
                        <h2 id="membership-summary-title" class="mt-1 text-2xl font-bold text-aski-900">{{ activeMembership ? 'Keanggotaan Aktif' : 'Belum Aktif' }}</h2>
                        <p v-if="activeMembership" class="mt-2 text-sm text-slate-600">Berlaku {{ formatDateOnly(activeMembership.starts_at) }} – {{ formatDateOnly(activeMembership.ends_at) }}</p>
                        <p v-else class="mt-2 max-w-xl text-sm leading-6 text-slate-600">Ajukan permohonan keanggotaan untuk memulai proses. Jika Anda pernah menjadi anggota, informasi perpanjangan akan tersedia sesuai masa berlaku.</p>
                    </div>
                </div>
                <div class="flex shrink-0 flex-col gap-2 sm:items-end">
                    <Link :href="route('member.applications.index')" class="btn-primary inline-flex min-h-11 items-center justify-center gap-2 px-5 text-sm">
                        Kelola Permohonan
                        <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69l-3.22-3.22a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 1 1-1.06-1.06l3.22-3.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                    </Link>
                    <p class="text-xs text-slate-500">Pengajuan baru dan perpanjangan</p>
                </div>
            </div>
            <div class="grid gap-3 border-t border-slate-100 bg-slate-50/70 px-5 py-4 sm:grid-cols-2 sm:px-6 lg:px-7">
                <div class="flex items-center justify-between gap-3 text-sm"><span class="text-slate-500">Keanggotaan aktif</span><span class="font-semibold tabular-nums text-slate-900">{{ activeMembership ? 'Ya' : 'Tidak' }}</span></div>
                <div class="flex items-center justify-between gap-3 text-sm"><span class="text-slate-500">Jumlah riwayat</span><span class="font-semibold tabular-nums text-slate-900">{{ memberships.total }}</span></div>
            </div>
        </section>

        <section class="card mt-6 overflow-hidden" aria-labelledby="membership-history-title">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 id="membership-history-title" class="font-semibold text-aski-900">Riwayat Keanggotaan</h2>
                <p class="mt-1 text-sm text-slate-500">{{ memberships.from || 0 }}–{{ memberships.to || 0 }} dari {{ memberships.total }} periode</p>
            </div>
            <div tabindex="0" role="region" aria-label="Tabel riwayat keanggotaan, gunakan gulir horizontal untuk melihat kolom lain" class="overflow-x-auto focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
                <table class="w-full min-w-[34rem] table-fixed divide-y divide-slate-100 text-left text-sm">
                    <colgroup><col class="w-[48%]" /><col class="w-[22%]" /><col class="w-[30%]" /></colgroup>
                    <thead class="bg-aski-50 text-aski-900"><tr><th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Periode Keanggotaan</th><th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Status</th><th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Tagihan</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="membership in memberships.data" :key="membership.id" class="hover:bg-slate-50/70">
                            <td class="px-4 py-4 sm:px-6"><span class="block font-medium text-slate-900">{{ formatDateOnly(membership.starts_at) }}</span><span class="mt-0.5 block text-xs text-slate-500">s.d. {{ formatDateOnly(membership.ends_at) }}</span></td>
                            <td class="px-4 py-4 sm:px-6"><StatusBadge :status="membership.display_status" :label="membershipStatusLabels[membership.display_status] || 'Perlu ditinjau'" /></td>
                            <td class="break-all px-4 py-4 sm:px-6"><Link v-if="membership.invoice" :href="route('member.invoices.show', membership.invoice.id)" class="inline-flex min-h-9 items-center rounded-lg font-semibold text-aski-700 hover:text-aski-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">{{ membership.invoice.number }}<svg aria-hidden="true" class="ml-1.5 h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.22 14.78a.75.75 0 0 0 1.06 0l7.19-7.19v4.66a.75.75 0 0 0 1.5 0V5.75a.75.75 0 0 0-.75-.75H9.56a.75.75 0 0 0 0 1.5h4.66l-7.19 7.19a.75.75 0 0 0 0 1.06Z" clip-rule="evenodd"/></svg></Link><span v-else class="text-slate-400">—</span></td>
                        </tr>
                        <tr v-if="memberships.data.length === 0"><td colspan="3" class="px-5 py-10 text-center text-slate-500 sm:px-6">Belum ada riwayat keanggotaan.</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
        <PaginationLinks :links="memberships.links" />

        <section class="card mt-6 overflow-hidden" aria-labelledby="application-history-title">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 id="application-history-title" class="font-semibold text-aski-900">Riwayat Permohonan</h2>
                <p class="mt-1 text-sm text-slate-500">Pembaruan status dan catatan untuk permohonan Anda.</p>
            </div>
            <div class="divide-y divide-slate-100 px-5 sm:px-6">
                <article v-for="application in applications" :key="application.id" class="py-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div><h3 class="font-semibold text-slate-900">Permohonan #{{ application.id }}</h3><p class="mt-1 text-xs text-slate-500">Diajukan {{ formatDateTime(application.submitted_at) }}</p></div>
                        <StatusBadge :status="application.status" :label="statusLabels[application.status] || 'Status tidak dikenal'" />
                    </div>
                    <div v-if="application.admin_note" class="mt-4 rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-700"><span class="font-semibold text-slate-900">Catatan Admin:</span> {{ application.admin_note }}</div>
                    <div v-if="application.needs_status_review" role="status" class="mt-4 rounded-xl border border-gold-400 bg-gold-100 p-4 text-sm leading-6 text-aski-900">
                        <p>Pembayaran tagihan sudah terverifikasi, tetapi status permohonan belum diperbarui. Masa keanggotaan Anda tetap mengikuti status keanggotaan yang tercantum di atas.</p>
                        <Link v-if="application.invoice" :href="route('member.invoices.show', application.invoice.id)" class="mt-2 inline-block font-semibold underline">Lihat tagihan terkait</Link>
                    </div>
                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Riwayat Status</p>
                        <ol v-if="application.histories.length" class="ml-1">
                            <li v-for="(history, index) in timelineHistories(application.histories)" :key="history.id" class="relative flex gap-4 pb-5 last:pb-0">
                                <span v-if="index < application.histories.length - 1" aria-hidden="true" class="absolute left-[7px] top-4 h-[calc(100%-0.75rem)] w-px bg-slate-200"></span>
                                <span aria-hidden="true" class="relative z-10 mt-1 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2 border-aski-600 bg-white"><span class="h-1.5 w-1.5 rounded-full bg-aski-600"></span></span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between sm:gap-4">
                                        <p class="text-sm font-semibold text-slate-900">{{ statusLabels[history.status] ?? 'Status tidak dikenal' }}</p>
                                        <time class="text-xs text-slate-500">{{ formatDateTime(history.created_at) }}</time>
                                    </div>
                                    <p v-if="history.note" class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-600">{{ history.note }}</p>
                                </div>
                            </li>
                        </ol>
                        <p v-else class="text-sm text-slate-500">Belum ada riwayat perubahan status.</p>
                    </div>
                </article>
                <p v-if="applications.length === 0" class="py-10 text-center text-sm text-slate-500">Belum ada riwayat permohonan.</p>
            </div>
        </section>
    </MemberLayout>
</template>
