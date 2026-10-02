<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    summary: Object,
    latestApplications: Array,
    latestPayments: Array,
});

const money = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
</script>

<template>
    <Head title="Dashboard Admin" />
    <AdminLayout>
        <div class="border-l-4 border-gold-400 pl-4">
            <p class="text-sm font-semibold text-aski-700">DASHBOARD</p>
            <h1 class="mt-1 text-3xl font-bold text-aski-900">Dashboard Admin</h1>
            <p class="mt-2 text-slate-600">Ringkasan operasional ASKI-PTMA hari ini.</p>
        </div>

        <div class="mt-7 grid gap-4 md:grid-cols-3">
            <div class="card p-5"><p class="text-sm text-slate-500">Anggota Aktif</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.active_members }}</p></div>
            <div class="card p-5"><p class="text-sm text-slate-500">Permohonan Baru</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.new_applications }}</p></div>
            <div class="card p-5"><p class="text-sm text-slate-500">Total Data HKI</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.total_hki }}</p></div>
            <div class="card p-5"><p class="text-sm text-slate-500">Tagihan Belum Dibayar</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.unpaid_invoices }}</p></div>
            <div class="card p-5"><p class="text-sm text-slate-500">Bukti Pembayaran Masuk</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.pending_payments }}</p></div>
            <div class="card p-5"><p class="text-sm text-slate-500">Pembayaran Terverifikasi</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ money(summary.paid_amount) }}</p></div>
        </div>

        <div class="mt-7 grid gap-6 lg:grid-cols-2">
            <div class="card p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-aski-900">Permohonan Terbaru</h2>
                    <Link :href="route('admin.applications.index')" class="text-sm font-semibold text-aski-700">Lihat semua</Link>
                </div>
                <div class="mt-4 divide-y divide-aski-100">
                    <div v-for="application in latestApplications" :key="application.id" class="py-3">
                        <p class="font-semibold text-aski-900">{{ application.user.name }}</p>
                        <p class="text-sm text-slate-500">{{ application.status }} · {{ application.submitted_at }}</p>
                    </div>
                    <p v-if="latestApplications.length === 0" class="py-6 text-center text-sm text-slate-500">Belum ada permohonan.</p>
                </div>
            </div>
            <div class="card p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-aski-900">Pembayaran Terbaru</h2>
                    <Link :href="route('admin.payments.index')" class="text-sm font-semibold text-aski-700">Lihat semua</Link>
                </div>
                <div class="mt-4 divide-y divide-aski-100">
                    <div v-for="payment in latestPayments" :key="payment.id" class="py-3">
                        <p class="font-semibold text-aski-900">{{ payment.user.name }}</p>
                        <p class="text-sm text-slate-500">{{ payment.invoice.number }} · {{ payment.status }}</p>
                    </div>
                    <p v-if="latestPayments.length === 0" class="py-6 text-center text-sm text-slate-500">Belum ada pembayaran.</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
