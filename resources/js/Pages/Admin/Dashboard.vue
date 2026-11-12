<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatDateTime } from '@/utils/date.js';

const props = defineProps({
    summary: Object,
    latestApplications: Array,
    latestPayments: Array,
    statusBreakdown: Object,
    statusLabels: Object,
});

const money = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
const maxCount = (items) => Math.max(1, ...items.map((item) => item.count));
const applicationMax = maxCount(props.statusBreakdown.applications);
const paymentMax = maxCount(props.statusBreakdown.payments);
const barWidth = (count, max) => `${count === 0 ? 0 : Math.max(5, (count / max) * 100)}%`;
const applicationColor = (status) => ({
    submitted: 'bg-amber-500',
    revision: 'bg-orange-500',
    approved_waiting_payment: 'bg-sky-600',
    rejected: 'bg-rose-500',
    completed: 'bg-aski-700',
}[status] || 'bg-slate-400');
const paymentColor = (status) => ({
    submitted: 'bg-amber-500',
    verified: 'bg-aski-700',
    rejected: 'bg-rose-500',
}[status] || 'bg-slate-400');
</script>

<template>
    <Head title="Dashboard Admin" />
    <AdminLayout>
        <div class="border-l-4 border-gold-400 pl-4">
            <p class="text-sm font-semibold text-aski-700">DASHBOARD</p>
            <h1 class="mt-1 text-3xl font-bold text-aski-900">Dashboard Admin</h1>
            <p class="mt-2 text-slate-600">Ringkasan operasional ASKI-PTMA hari ini.</p>
        </div>

        <div class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <div class="card p-5"><p class="text-sm text-slate-500">Anggota Aktif</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.active_members }}</p><p class="mt-1 text-xs text-slate-500">Periode keanggotaan masih berlaku</p></div>
            <div class="card p-5"><p class="text-sm text-slate-500">Permohonan Menunggu</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.new_applications }}</p><p class="mt-1 text-xs text-slate-500">Perlu ditinjau Admin</p></div>
            <div class="card p-5"><p class="text-sm text-slate-500">Total Data HKI</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.total_hki }}</p><p class="mt-1 text-xs text-slate-500">Seluruh data tercatat</p></div>
            <div class="card p-5"><p class="text-sm text-slate-500">Tagihan Belum Dibayar</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.unpaid_invoices }}</p><p class="mt-1 text-xs text-slate-500">Status invoice belum lunas</p></div>
            <div class="card p-5"><p class="text-sm text-slate-500">Bukti Pembayaran Menunggu</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.pending_payments }}</p><p class="mt-1 text-xs text-slate-500">Menunggu verifikasi Admin</p></div>
            <div class="card p-5"><p class="text-sm text-slate-500">Nilai Tagihan Lunas</p><p class="mt-2 text-2xl font-bold text-aski-900">{{ money(summary.paid_amount) }}</p><p class="mt-1 text-xs text-slate-500">Akumulasi sepanjang waktu</p></div>
        </div>

        <section class="card mt-6 p-5" aria-labelledby="admin-next-actions">
            <h2 id="admin-next-actions" class="font-bold text-aski-900">Tindakan yang menunggu</h2>
            <div class="mt-4 flex flex-wrap gap-3">
                <Link v-if="summary.new_applications > 0" :href="route('admin.applications.index', { status: 'submitted' })" class="rounded-lg border border-aski-200 px-4 py-2 text-sm font-semibold text-aski-700 hover:bg-aski-50">Periksa {{ summary.new_applications }} permohonan</Link>
                <Link v-if="summary.pending_payments > 0" :href="route('admin.payments.index', { status: 'submitted' })" class="rounded-lg border border-aski-200 px-4 py-2 text-sm font-semibold text-aski-700 hover:bg-aski-50">Periksa {{ summary.pending_payments }} bukti pembayaran</Link>
                <p v-if="summary.new_applications === 0 && summary.pending_payments === 0" class="text-sm text-slate-600">Tidak ada permohonan atau bukti pembayaran yang menunggu.</p>
            </div>
        </section>

        <section class="mt-7" aria-labelledby="dashboard-process-insights">
            <div class="mb-4">
                <h2 id="dashboard-process-insights" class="text-xl font-bold text-aski-900">Progres Proses</h2>
                <p class="mt-1 text-sm text-slate-600">Jumlah data pada setiap status saat ini.</p>
            </div>
            <div class="grid gap-5 lg:grid-cols-2">
                <div class="card p-5 sm:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div><h3 class="font-bold text-aski-900">Permohonan Keanggotaan</h3><p class="mt-1 text-xs text-slate-500">{{ statusBreakdown.applications.reduce((sum, item) => sum + item.count, 0) }} permohonan tercatat</p></div>
                        <Link :href="route('admin.applications.index')" class="shrink-0 text-sm font-semibold text-aski-700 hover:text-aski-900">Buka daftar</Link>
                    </div>
                    <div class="mt-5 space-y-4">
                        <div v-for="item in statusBreakdown.applications" :key="item.status">
                            <div class="mb-1.5 flex items-center justify-between gap-3 text-sm"><span class="text-slate-700">{{ item.label }}</span><span class="font-semibold tabular-nums text-slate-900">{{ item.count }}</span></div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100" role="img" :aria-label="`${item.label}: ${item.count}`"><div class="h-full rounded-full" :class="applicationColor(item.status)" :style="{ width: barWidth(item.count, applicationMax) }"></div></div>
                        </div>
                    </div>
                </div>
                <div class="card p-5 sm:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div><h3 class="font-bold text-aski-900">Bukti Pembayaran</h3><p class="mt-1 text-xs text-slate-500">{{ statusBreakdown.payments.reduce((sum, item) => sum + item.count, 0) }} bukti tercatat</p></div>
                        <Link :href="route('admin.payments.index')" class="shrink-0 text-sm font-semibold text-aski-700 hover:text-aski-900">Buka daftar</Link>
                    </div>
                    <div class="mt-5 space-y-4">
                        <div v-for="item in statusBreakdown.payments" :key="item.status">
                            <div class="mb-1.5 flex items-center justify-between gap-3 text-sm"><span class="text-slate-700">{{ item.label }}</span><span class="font-semibold tabular-nums text-slate-900">{{ item.count }}</span></div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100" role="img" :aria-label="`${item.label}: ${item.count}`"><div class="h-full rounded-full" :class="paymentColor(item.status)" :style="{ width: barWidth(item.count, paymentMax) }"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="mt-7 grid gap-5 lg:grid-cols-2">
            <div class="card p-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-aski-900">Permohonan Terbaru</h2>
                    <Link :href="route('admin.applications.index')" class="text-sm font-semibold text-aski-700">Lihat semua</Link>
                </div>
                <div class="mt-4 divide-y divide-aski-100">
                    <div v-for="application in latestApplications" :key="application.id" class="py-3">
                        <p class="font-semibold text-aski-900">{{ application.user.name }}</p>
                        <p class="text-sm text-slate-500">{{ statusLabels.applications[application.status] ?? 'Status tidak dikenal' }} · {{ formatDateTime(application.submitted_at) }}</p>
                    </div>
                    <p v-if="latestApplications.length === 0" class="py-6 text-center text-sm text-slate-500">Belum ada permohonan.</p>
                </div>
            </div>
            <div class="card p-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-aski-900">Pembayaran Terbaru</h2>
                    <Link :href="route('admin.payments.index')" class="text-sm font-semibold text-aski-700">Lihat semua</Link>
                </div>
                <div class="mt-4 divide-y divide-aski-100">
                    <div v-for="payment in latestPayments" :key="payment.id" class="py-3">
                        <p class="font-semibold text-aski-900">{{ payment.user.name }}</p>
                        <p class="text-sm text-slate-500">{{ payment.invoice.number }} · {{ statusLabels.payments[payment.status] ?? 'Status tidak dikenal' }}</p>
                    </div>
                    <p v-if="latestPayments.length === 0" class="py-6 text-center text-sm text-slate-500">Belum ada pembayaran.</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
