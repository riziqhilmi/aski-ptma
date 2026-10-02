<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';

defineProps({
    summary: Object,
    latestApplication: Object,
    latestInvoices: Array,
    latestHki: Array,
});

const money = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
</script>

<template>
    <Head title="Dashboard Member" />
    <MemberLayout>
        <div class="border-l-4 border-gold-400 pl-4">
            <p class="text-sm font-semibold text-aski-700">DASHBOARD</p>
            <h1 class="mt-1 text-3xl font-bold text-aski-900">Dashboard Member</h1>
            <p class="mt-2 text-slate-600">Pantau status keanggotaan, tagihan, dan data HKI Anda.</p>
        </div>

        <div class="mt-7 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="card p-5"><p class="text-sm text-slate-500">Status Keanggotaan</p><p class="mt-2 text-2xl font-bold text-aski-900">{{ summary.membership_status }}</p></div>
            <div class="card p-5"><p class="text-sm text-slate-500">Data HKI Saya</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.hki_count }}</p></div>
            <div class="card p-5"><p class="text-sm text-slate-500">Tagihan Belum Dibayar</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.unpaid_invoices }}</p></div>
            <div class="card p-5"><p class="text-sm text-slate-500">Total Tagihan</p><p class="mt-2 text-2xl font-bold text-aski-900">{{ money(summary.unpaid_amount) }}</p></div>
        </div>

        <div class="card mt-6 p-6">
            <p class="text-sm text-slate-500">Periode Aktif</p>
            <p class="mt-1 text-xl font-bold text-aski-900">{{ summary.active_period }}</p>
            <p v-if="latestApplication" class="mt-2 text-sm text-slate-500">Permohonan terakhir: {{ latestApplication.status }}</p>
        </div>

        <div class="mt-7 grid gap-6 lg:grid-cols-2">
            <div class="card p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-aski-900">Tagihan Terbaru</h2>
                    <Link :href="route('member.invoices.index')" class="text-sm font-semibold text-aski-700">Lihat semua</Link>
                </div>
                <div class="mt-4 divide-y divide-aski-100">
                    <div v-for="invoice in latestInvoices" :key="invoice.id" class="py-3">
                        <p class="font-semibold text-aski-900">{{ invoice.number }}</p>
                        <p class="text-sm text-slate-500">{{ money(invoice.amount) }} · {{ invoice.status }}</p>
                    </div>
                    <p v-if="latestInvoices.length === 0" class="py-6 text-center text-sm text-slate-500">Belum ada tagihan.</p>
                </div>
            </div>
            <div class="card p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-aski-900">Data HKI Terbaru</h2>
                    <Link :href="route('member.intellectual-properties.index')" class="text-sm font-semibold text-aski-700">Lihat semua</Link>
                </div>
                <div class="mt-4 divide-y divide-aski-100">
                    <div v-for="record in latestHki" :key="record.id" class="py-3">
                        <p class="font-semibold text-aski-900">{{ record.title }}</p>
                        <p class="text-sm text-slate-500">{{ record.ip_type }} · {{ record.application_date }}</p>
                    </div>
                    <p v-if="latestHki.length === 0" class="py-6 text-center text-sm text-slate-500">Belum ada data HKI.</p>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
