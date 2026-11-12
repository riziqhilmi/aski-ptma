<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { formatDateOnly, formatDateTime } from '@/utils/date.js';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({ invoice: Object, statusLabels: Object, paymentStatusLabels: Object });
const format = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
const verifiedPayment = () => props.invoice.payments?.find((payment) => payment.status === 'verified');
const form = useForm({ proof: null });
const upload = () => form.post(route('member.invoices.payments.store', props.invoice.id));
const payments = () => props.invoice.payments ?? [];
const canUpload = () => props.invoice.status === 'unpaid' && !payments().some((payment) => ['submitted', 'verified'].includes(payment.status));
const hasRejectedPayment = () => payments().some((payment) => payment.status === 'rejected');
</script>

<template>
    <Head :title="`Invoice ${invoice.number}`" />
    <MemberLayout>
        <div class="mx-auto max-w-6xl">
            <Link :href="route('member.invoices.index')" class="inline-flex min-h-9 items-center text-sm font-semibold text-aski-700 hover:text-aski-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">← Kembali ke Tagihan</Link>

            <header class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0"><p class="text-sm font-semibold uppercase tracking-wide text-aski-700">Tagihan &amp; Pembayaran</p><h1 class="mt-1 text-3xl font-bold text-aski-900">Invoice Tagihan</h1><p class="mt-1 break-all text-sm text-slate-500">{{ invoice.number }}</p></div>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <StatusBadge :status="invoice.status" :label="statusLabels[invoice.status] || 'Status tidak dikenal'" />
                    <a :href="route('member.invoices.pdf', invoice.id)" class="btn-primary inline-flex min-h-10 items-center justify-center gap-2 px-4 text-sm"><svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2.75a.75.75 0 0 1 .75.75v7.69l2.22-2.22a.75.75 0 1 1 1.06 1.06l-3.5 3.5a.75.75 0 0 1-1.06 0l-3.5-3.5a.75.75 0 0 1 1.06-1.06l2.22 2.22V3.5a.75.75 0 0 1 .75-.75Z"/><path d="M3.5 13.75a.75.75 0 0 1 .75.75v1A1.75 1.75 0 0 0 6 17.25h8A1.75 1.75 0 0 0 15.75 15.5v-1a.75.75 0 0 1 1.5 0v1A3.25 3.25 0 0 1 14 18.75H6A3.25 3.25 0 0 1 2.75 15.5v-1a.75.75 0 0 1 .75-.75Z"/></svg>Unduh Invoice</a>
                </div>
            </header>

            <div class="mt-6 grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_20rem]">
                <article class="card min-w-0 overflow-hidden">
                    <div class="border-b border-slate-100 px-5 py-5 sm:px-7">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex min-w-0 items-center gap-3"><div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-aski-50 text-sm font-bold text-aski-800">ASKI</div><div class="min-w-0"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">ASKI-PTMA</p><h2 class="mt-0.5 text-lg font-bold text-aski-900">Invoice Keanggotaan</h2></div></div>
                            <StatusBadge class="shrink-0" :status="invoice.status" :label="statusLabels[invoice.status] || 'Status tidak dikenal'" />
                        </div>
                    </div>

                    <div class="px-5 sm:px-7">
                        <div v-if="verifiedPayment()" role="status" class="mt-5 flex gap-3 rounded-xl border border-aski-200 bg-aski-50 p-4 text-sm text-aski-900">
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm3.28 5.78a.75.75 0 0 0-1.06-1.06L9 9.94 7.78 8.72a.75.75 0 1 0-1.06 1.06l1.75 1.75a.75.75 0 0 0 1.06 0l3.75-3.75Z" clip-rule="evenodd"/></svg>
                            <p><span class="font-semibold">Pembayaran terverifikasi.</span><span class="mt-0.5 block">{{ formatDateTime(verifiedPayment().reviewed_at || verifiedPayment().created_at) }}</span></p>
                        </div>

                        <dl class="grid gap-4 border-b border-slate-100 py-5 sm:grid-cols-3">
                            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tanggal Terbit</dt><dd class="mt-1.5 text-sm font-semibold text-slate-900">{{ formatDateOnly(invoice.issued_at) }}</dd></div>
                            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Jatuh Tempo</dt><dd class="mt-1.5 text-sm font-semibold text-slate-900">{{ formatDateOnly(invoice.due_at) }}</dd></div>
                            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Durasi Keanggotaan</dt><dd class="mt-1.5 text-sm font-semibold text-slate-900">{{ invoice.duration_months }} bulan</dd></div>
                        </dl>

                        <div class="grid gap-5 border-b border-slate-100 py-5 sm:grid-cols-2">
                            <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Ditagihkan Kepada</p><p class="mt-1.5 font-semibold text-slate-900">{{ invoice.user.name }}</p><p class="mt-0.5 break-all text-sm text-slate-600">{{ invoice.user.email }}</p></div>
                            <div v-if="invoice.application"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Jenis Permohonan</p><p class="mt-1.5 font-semibold text-slate-900">{{ invoice.application.application_type === 'renewal' ? 'Perpanjangan keanggotaan' : 'Keanggotaan baru' }}</p></div>
                        </div>

                        <div tabindex="0" role="region" aria-label="Rincian invoice, gunakan gulir horizontal untuk melihat seluruh kolom" class="overflow-x-auto focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
                            <table class="my-5 w-full min-w-[520px] text-left text-sm">
                                <thead class="bg-aski-50 text-aski-900"><tr><th scope="col" class="rounded-l-lg px-3 py-3 font-semibold">#</th><th scope="col" class="px-3 py-3 font-semibold">Deskripsi</th><th scope="col" class="px-3 py-3 text-right font-semibold">Jumlah</th><th scope="col" class="rounded-r-lg px-3 py-3 text-right font-semibold">Subtotal</th></tr></thead>
                                <tbody><tr class="border-b border-slate-100"><td class="px-3 py-4 text-slate-500">1</td><td class="px-3 py-4 font-medium text-slate-800">Iuran keanggotaan ASKI-PTMA <span class="block text-xs font-normal text-slate-500">Masa keanggotaan {{ invoice.duration_months }} bulan</span></td><td class="px-3 py-4 text-right tabular-nums text-slate-700">1</td><td class="whitespace-nowrap px-3 py-4 text-right font-semibold tabular-nums text-slate-900">{{ format(invoice.amount) }}</td></tr></tbody>
                                <tfoot><tr><th colspan="3" class="px-3 pt-5 text-right text-sm font-semibold text-slate-700">Total Tagihan</th><td class="whitespace-nowrap px-3 pt-5 text-right text-lg font-bold tabular-nums text-aski-800">{{ format(invoice.amount) }}</td></tr></tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="border-t border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-7"><p class="text-xs leading-5 text-slate-500">Nominal dan status invoice mengikuti data tagihan pada sistem.</p></div>
                </article>

                <aside class="space-y-5">
                    <section class="card overflow-hidden">
                        <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold text-aski-900">Pembayaran</h2><p class="mt-1 text-sm text-slate-500">Instruksi dan status bukti.</p></div>
                        <div class="p-5">
                            <p v-if="invoice.status === 'unpaid'" role="status" class="rounded-xl border border-gold-400 bg-gold-100 p-4 text-sm leading-6 text-aski-900">Silakan bayar mengikuti instruksi Admin. Jika informasi rekening belum tersedia, hubungi Admin sebelum melakukan transfer.</p>
                            <p v-else class="rounded-xl border border-aski-200 bg-aski-50 p-4 text-sm leading-6 text-aski-900">Tagihan sudah dibayar. Instruksi transfer tidak lagi ditampilkan.</p>
                            <div v-if="verifiedPayment()" class="mt-4 space-y-3 border-t border-slate-100 pt-4 text-sm">
                                <div class="flex items-center justify-between gap-3"><span class="text-slate-500">Status bukti</span><StatusBadge :status="verifiedPayment().status" :label="paymentStatusLabels[verifiedPayment().status] || 'Terverifikasi'" /></div>
                                <div><p class="text-xs text-slate-500">Diverifikasi</p><p class="mt-1 font-medium text-slate-800">{{ formatDateTime(verifiedPayment().reviewed_at || verifiedPayment().created_at) }}</p></div>
                                <a :href="route('member.payments.proof', verifiedPayment().id)" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-9 items-center font-semibold text-aski-700 hover:text-aski-900">Lihat bukti pembayaran →</a>
                            </div>
                        </div>
                    </section>

                    <section v-if="invoice.status === 'unpaid' && hasRejectedPayment()" role="status" class="rounded-2xl border border-red-200 bg-red-50 p-5">
                        <h2 class="font-semibold text-red-900">Bukti perlu diperbaiki</h2>
                        <p class="mt-1 text-sm leading-6 text-red-800">Bukti sebelumnya ditolak. Baca catatan Admin pada riwayat pembayaran sebelum mengirim ulang.</p>
                    </section>

                    <form v-if="canUpload()" class="card overflow-hidden" @submit.prevent="upload">
                        <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold text-aski-900">Kirim Bukti Pembayaran</h2><p class="mt-1 text-sm text-slate-500">PDF, JPG, atau PNG; maksimal 5 MB.</p></div>
                        <div class="p-5">
                            <label for="payment-proof" class="text-sm font-semibold text-slate-800">Pilih file bukti <span class="text-red-600">*</span>
                                <input id="payment-proof" type="file" required accept=".pdf,.jpg,.jpeg,.png" aria-required="true" :aria-invalid="Boolean(form.errors.proof)" :aria-describedby="form.errors.proof ? 'payment-proof-error' : undefined" class="mt-2 block min-h-11 w-full rounded-xl border border-slate-300 bg-white p-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-aski-50 file:px-3 file:py-1.5 file:font-semibold file:text-aski-800 focus:border-aski-500 focus:ring-aski-500" @input="form.proof = $event.target.files[0]" />
                            </label>
                            <p v-if="form.errors.proof" id="payment-proof-error" class="mt-1.5 text-sm text-red-600">{{ form.errors.proof }}</p>
                            <button type="submit" class="btn-primary mt-4 inline-flex min-h-11 w-full items-center justify-center" :disabled="form.processing">{{ form.processing ? 'Mengirim…' : 'Kirim untuk Diverifikasi' }}</button>
                        </div>
                    </form>

                    <section class="card overflow-hidden">
                        <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold text-aski-900">Riwayat Bukti</h2></div>
                        <div v-if="payments().length" class="divide-y divide-slate-100">
                            <article v-for="payment in payments()" :key="payment.id" class="p-5">
                                <div class="flex items-start justify-between gap-3"><div><p class="text-sm font-semibold text-slate-900">Bukti pembayaran</p><p class="mt-1 text-xs text-slate-500">Dikirim {{ formatDateTime(payment.created_at) }}</p></div><StatusBadge :status="payment.status" :label="paymentStatusLabels[payment.status] || 'Status tidak dikenal'" /></div>
                                <p v-if="payment.reviewed_at" class="mt-2 text-xs text-slate-500">Diperiksa {{ formatDateTime(payment.reviewed_at) }}</p>
                                <p v-if="payment.admin_note" class="mt-3 rounded-lg bg-slate-50 p-3 text-sm leading-6 text-slate-700"><span class="font-semibold text-slate-900">Catatan Admin:</span> {{ payment.admin_note }}</p>
                                <a :href="route('member.payments.proof', payment.id)" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex min-h-8 items-center text-sm font-semibold text-aski-700 hover:text-aski-900">Lihat file bukti →</a>
                            </article>
                        </div>
                        <p v-else class="p-5 text-sm text-slate-500">Belum ada bukti pembayaran yang dikirim.</p>
                    </section>
                </aside>
            </div>
        </div>
    </MemberLayout>
</template>
