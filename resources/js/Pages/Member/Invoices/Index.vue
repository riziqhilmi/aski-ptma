<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import PaginationLinks from '@/Components/PaginationLinks.vue';
import { formatDateOnly } from '@/utils/date.js';

defineProps({ invoices: Object, statusLabels: Object });
const format = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
</script>

<template>
    <Head title="Tagihan Saya" />
    <MemberLayout>
        <header>
            <p class="text-sm font-semibold uppercase tracking-wide text-aski-700">Keuangan</p>
            <h1 class="mt-1 text-3xl font-bold text-aski-900">Tagihan &amp; Pembayaran</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Lihat tagihan, status pembayaran, dan kirim bukti transfer.</p>
        </header>

        <section class="card mt-6 overflow-hidden" aria-labelledby="invoice-list-title">
            <div class="flex flex-col gap-1 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-end sm:justify-between sm:px-6">
                <div><h2 id="invoice-list-title" class="font-semibold text-aski-900">Daftar Tagihan</h2><p class="mt-1 text-sm text-slate-500">{{ invoices.from || 0 }}–{{ invoices.to || 0 }} dari {{ invoices.total }} tagihan</p></div>
            </div>
            <div tabindex="0" role="region" aria-label="Daftar tagihan, gunakan gulir horizontal untuk melihat informasi lainnya" class="overflow-x-auto focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
                <table class="min-w-[660px] w-full divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-aski-50 text-aski-900"><tr><th scope="col" class="px-5 py-3.5 font-semibold sm:px-6">Nomor Invoice</th><th scope="col" class="px-5 py-3.5 font-semibold sm:px-6">Tanggal</th><th scope="col" class="px-5 py-3.5 text-right font-semibold sm:px-6">Nominal</th><th scope="col" class="px-5 py-3.5 font-semibold sm:px-6">Status</th><th scope="col" class="px-5 py-3.5 text-right font-semibold sm:px-6">Aksi</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="invoice in invoices.data" :key="invoice.id" class="transition-colors hover:bg-slate-50/70">
                            <td class="px-5 py-4 sm:px-6"><p class="font-semibold text-slate-900">{{ invoice.number }}</p><p class="mt-0.5 text-xs text-slate-500">Jatuh tempo {{ formatDateOnly(invoice.due_at) }}</p></td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-600 sm:px-6">{{ formatDateOnly(invoice.issued_at) }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-right font-semibold tabular-nums text-slate-900 sm:px-6">{{ format(invoice.amount) }}</td>
                            <td class="px-5 py-4 sm:px-6"><StatusBadge :status="invoice.status" :label="statusLabels[invoice.status] || 'Status tidak dikenal'" /></td>
                            <td class="px-5 py-4 text-right sm:px-6"><Link :href="route('member.invoices.show', invoice.id)" class="inline-flex min-h-9 items-center rounded-lg px-3 font-semibold text-aski-700 hover:bg-aski-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">Lihat Invoice <span aria-hidden="true" class="ml-1">→</span></Link></td>
                        </tr>
                        <tr v-if="invoices.data.length === 0"><td colspan="5" class="px-5 py-12 text-center sm:px-6"><div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-aski-50 text-aski-700"><svg aria-hidden="true" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 3.25A2.25 2.25 0 0 1 6.25 1h7.5A2.25 2.25 0 0 1 16 3.25v13.5A2.25 2.25 0 0 1 13.75 19h-7.5A2.25 2.25 0 0 1 4 16.75V3.25ZM6.25 2.5a.75.75 0 0 0-.75.75v13.5c0 .414.336.75.75.75h7.5a.75.75 0 0 0 .75-.75V3.25a.75.75 0 0 0-.75-.75h-7.5ZM7 6.25A.75.75 0 0 1 7.75 5.5h4.5a.75.75 0 0 1 0 1.5h-4.5A.75.75 0 0 1 7 6.25Zm.75 3.25a.75.75 0 0 0 0 1.5h4.5a.75.75 0 0 0 0-1.5h-4.5Z" clip-rule="evenodd"/></svg></div><p class="mt-3 font-semibold text-slate-900">Belum ada tagihan</p><p class="mt-1 text-sm text-slate-500">Tagihan keanggotaan akan muncul di sini setelah diterbitkan.</p></td></tr>
                    </tbody>
                </table>
            </div>
        </section>
        <PaginationLinks :links="invoices.links" />
    </MemberLayout>
</template>
