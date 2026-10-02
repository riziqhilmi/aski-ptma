<script setup>
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    filters: Object,
    summary: Object,
    memberships: Array,
    payments: Array,
    hkiRecords: Array,
    articles: Array,
});

const form = ref({ ...props.filters });
const money = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
const submit = () => router.get(route('admin.reports.index'), form.value, { preserveState: true });
</script>

<template>
    <Head title="Laporan" />
    <AdminLayout>
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-aski-700">LAPORAN</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">Laporan ASKI-PTMA</h1>
                <p class="mt-2 text-slate-600">Ringkasan laporan anggota, pembayaran, HKI, dan berita terbit.</p>
            </div>
        </div>

        <form class="card mt-7 flex flex-wrap gap-3 p-5" @submit.prevent="submit">
            <input v-model="form.period_start" type="date" class="rounded-lg border-slate-300" />
            <input v-model="form.period_end" type="date" class="rounded-lg border-slate-300" />
            <button class="btn-primary" type="submit">Terapkan Periode</button>
        </form>

        <div class="mt-6 grid gap-4 md:grid-cols-5">
            <div class="card p-5"><p class="text-sm text-slate-500">Anggota Aktif</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.members }}</p></div>
            <div class="card p-5"><p class="text-sm text-slate-500">Pembayaran</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.payments }}</p></div>
            <div class="card p-5"><p class="text-sm text-slate-500">Nominal Verified</p><p class="mt-2 text-2xl font-bold text-aski-900">{{ money(summary.verified_payment_amount) }}</p></div>
            <div class="card p-5"><p class="text-sm text-slate-500">Data HKI</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.hki }}</p></div>
            <div class="card p-5"><p class="text-sm text-slate-500">Berita Terbit</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.published_articles }}</p></div>
        </div>

        <div class="mt-7 grid gap-6 xl:grid-cols-2">
            <div class="card overflow-x-auto">
                <h2 class="p-5 text-lg font-bold text-aski-900">Laporan Anggota</h2>
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-aski-50"><tr><th class="p-4">Nama</th><th class="p-4">Tipe</th><th class="p-4">Periode</th></tr></thead>
                    <tbody>
                        <tr v-for="membership in memberships" :key="membership.id" class="border-t"><td class="p-4 font-semibold">{{ membership.user.member_profile?.institution_name || membership.user.name }}</td><td class="p-4">{{ membership.user.member_profile?.account_type || '-' }}</td><td class="p-4">{{ membership.starts_at }} - {{ membership.ends_at }}</td></tr>
                        <tr v-if="memberships.length === 0"><td colspan="3" class="p-6 text-center text-slate-500">Belum ada data.</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="card overflow-x-auto">
                <h2 class="p-5 text-lg font-bold text-aski-900">Laporan Pembayaran</h2>
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-aski-50"><tr><th class="p-4">Member</th><th class="p-4">Tagihan</th><th class="p-4">Status</th></tr></thead>
                    <tbody>
                        <tr v-for="payment in payments" :key="payment.id" class="border-t"><td class="p-4 font-semibold">{{ payment.user.name }}</td><td class="p-4">{{ payment.invoice.number }}</td><td class="p-4">{{ payment.status }}</td></tr>
                        <tr v-if="payments.length === 0"><td colspan="3" class="p-6 text-center text-slate-500">Belum ada data.</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="card overflow-x-auto">
                <h2 class="p-5 text-lg font-bold text-aski-900">Laporan HKI</h2>
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-aski-50"><tr><th class="p-4">Judul</th><th class="p-4">Jenis</th><th class="p-4">Pemegang Hak</th></tr></thead>
                    <tbody>
                        <tr v-for="record in hkiRecords" :key="record.id" class="border-t"><td class="p-4 font-semibold">{{ record.title }}</td><td class="p-4">{{ record.ip_type }}</td><td class="p-4">{{ record.rights_holder }}</td></tr>
                        <tr v-if="hkiRecords.length === 0"><td colspan="3" class="p-6 text-center text-slate-500">Belum ada data.</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="card overflow-x-auto">
                <h2 class="p-5 text-lg font-bold text-aski-900">Laporan Berita Terbit</h2>
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-aski-50"><tr><th class="p-4">Judul</th><th class="p-4">Penulis</th><th class="p-4">Tanggal</th></tr></thead>
                    <tbody>
                        <tr v-for="article in articles" :key="article.id" class="border-t"><td class="p-4 font-semibold">{{ article.title }}</td><td class="p-4">{{ article.author.name }}</td><td class="p-4">{{ article.published_at }}</td></tr>
                        <tr v-if="articles.length === 0"><td colspan="3" class="p-6 text-center text-slate-500">Belum ada data.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
