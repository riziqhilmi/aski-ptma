<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatDateOnly, formatDateTime } from '@/utils/date.js';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({ invoice: Object, paymentSetting: Object, statusLabels: Object, paymentStatusLabels: Object });
const format = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
const verifiedPayment = () => props.invoice.payments?.find((payment) => payment.status === 'verified');
</script>

<template>
    <Head :title="`Tagihan ${invoice.number}`" />
    <AdminLayout>
        <div class="mx-auto max-w-5xl">
            <Link :href="route('admin.invoices.index')" class="text-sm font-semibold text-aski-700">Kembali ke tagihan</Link>
            <h1 class="mt-4 text-2xl font-bold text-aski-900">Detail Tagihan: {{ invoice.number }}</h1>

            <section class="card mt-6 p-5 sm:p-8 lg:p-10" aria-labelledby="invoice-title">
                <header class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 pb-6">
                    <div>
                        <p class="text-sm font-semibold tracking-wide text-aski-700">INVOICE</p>
                        <h2 id="invoice-title" class="mt-1 text-2xl font-bold text-aski-900">Tagihan Keanggotaan ASKI-PTMA</h2>
                    </div>
                    <StatusBadge :status="invoice.status" :label="statusLabels[invoice.status]" />
                </header>

                <p v-if="verifiedPayment()" class="mt-5 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                    Pembayaran telah diverifikasi pada {{ formatDateTime(verifiedPayment().reviewed_at || verifiedPayment().created_at) }}.
                </p>

                <div class="grid gap-5 border-b border-slate-200 py-6 sm:grid-cols-3">
                    <div><p class="text-xs text-slate-500">Nomor Invoice</p><p class="mt-1 font-semibold text-slate-900">{{ invoice.number }}</p></div>
                    <div><p class="text-xs text-slate-500">Dibuat pada</p><p class="mt-1 font-semibold text-slate-900">{{ formatDateOnly(invoice.issued_at) }}</p></div>
                    <div><p class="text-xs text-slate-500">Jatuh tempo</p><p class="mt-1 font-semibold text-slate-900">{{ formatDateOnly(invoice.due_at) }}</p></div>
                </div>

                <div class="grid gap-6 border-b border-slate-200 py-6 sm:grid-cols-2">
                    <div><p class="text-xs text-slate-500">Ditagihkan kepada</p><p class="mt-1 font-semibold text-aski-800">{{ invoice.user.name }}</p><p class="text-sm text-slate-600">{{ invoice.user.email }}</p></div>
                    <div v-if="invoice.application"><p class="text-xs text-slate-500">Jenis permohonan</p><p class="mt-1 font-semibold text-slate-900">{{ invoice.application.application_type === 'renewal' ? 'Perpanjangan keanggotaan' : 'Keanggotaan baru' }}</p><p class="text-sm text-slate-600">Masa keanggotaan: {{ invoice.duration_months }} bulan</p></div>
                </div>

                <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Rincian tagihan">
                    <table class="mt-5 w-full min-w-[560px] text-left text-sm">
                        <thead><tr class="border-b border-slate-200 text-xs uppercase text-slate-500"><th class="py-3 pr-4">#</th><th class="py-3 pr-4">Deskripsi</th><th class="py-3 pr-4 text-right">Jumlah</th><th class="py-3 text-right">Subtotal</th></tr></thead>
                        <tbody><tr class="border-b border-slate-100"><td class="py-4 pr-4">1</td><td class="py-4 pr-4 font-medium text-slate-900">Iuran keanggotaan ASKI-PTMA ({{ invoice.duration_months }} bulan)</td><td class="py-4 pr-4 text-right">1</td><td class="py-4 text-right font-semibold">{{ format(invoice.amount) }}</td></tr></tbody>
                        <tfoot><tr><th colspan="3" class="py-5 pr-4 text-right text-base">Total Tagihan</th><td class="py-5 text-right text-lg font-bold text-aski-800">{{ format(invoice.amount) }}</td></tr></tfoot>
                    </table>
                </div>

                <div class="grid gap-8 border-t border-slate-200 pt-6 sm:grid-cols-2">
                    <section aria-labelledby="payment-detail-heading">
                        <h3 id="payment-detail-heading" class="font-semibold text-slate-900">Detail Pembayaran</h3>
                        <dl v-if="verifiedPayment()" class="mt-3 divide-y divide-slate-100 text-sm">
                            <div class="flex justify-between gap-4 py-2"><dt class="text-slate-500">Status bukti</dt><dd><StatusBadge :status="verifiedPayment().status" :label="paymentStatusLabels[verifiedPayment().status]" /></dd></div>
                            <div class="flex justify-between gap-4 py-2"><dt class="text-slate-500">Diverifikasi</dt><dd class="text-right">{{ formatDateTime(verifiedPayment().reviewed_at || verifiedPayment().created_at) }}</dd></div>
                            <div class="flex justify-between gap-4 py-2"><dt class="text-slate-500">Bukti pembayaran</dt><dd class="text-right">Tersimpan pada sistem</dd></div>
                        </dl>
                        <p v-else class="mt-3 text-sm leading-6 text-slate-600">Belum ada pembayaran yang diverifikasi. {{ invoice.status === 'unpaid' ? 'Pembayaran akan tercatat setelah bukti diperiksa Admin.' : 'Belum ada rincian bukti pembayaran.' }}</p>
                    </section>
                    <section aria-labelledby="bank-detail-heading">
                        <h3 id="bank-detail-heading" class="font-semibold text-slate-900">Informasi Pembayaran</h3>
                        <dl v-if="invoice.status === 'unpaid' && paymentSetting.is_configured" class="mt-3 divide-y divide-slate-100 text-sm">
                            <div class="flex justify-between gap-4 py-2"><dt class="text-slate-500">Bank</dt><dd class="text-right font-medium">{{ paymentSetting.bank_name }}</dd></div>
                            <div class="flex justify-between gap-4 py-2"><dt class="text-slate-500">Nomor rekening</dt><dd class="text-right font-medium">{{ paymentSetting.account_number }}</dd></div>
                            <div class="flex justify-between gap-4 py-2"><dt class="text-slate-500">Atas nama</dt><dd class="text-right font-medium">{{ paymentSetting.account_holder }}</dd></div>
                        </dl>
                        <p v-else-if="invoice.status === 'unpaid'" class="mt-3 rounded-lg border border-gold-400 bg-gold-100 p-4 text-sm text-aski-900">Rekening pembayaran belum dikonfigurasi. Lengkapi pengaturan rekening sebelum mengirim instruksi pembayaran kepada Member.</p>
                        <p v-else class="mt-3 text-sm text-slate-600">Tagihan telah dibayar; instruksi transfer tidak lagi ditampilkan.</p>
                    </section>
                </div>

                <div class="mt-8 flex flex-wrap items-center justify-between gap-4 border-t border-slate-200 pt-6">
                    <a :href="route('admin.invoices.pdf', invoice.id)" class="btn-primary">Unduh Invoice</a>
                    <Link v-if="invoice.payments?.length" :href="route('admin.payments.show', invoice.payments[0].id)" class="text-sm font-semibold text-aski-700">Periksa pembayaran</Link>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>
