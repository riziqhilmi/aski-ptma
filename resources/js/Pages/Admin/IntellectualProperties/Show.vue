<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ record: Object });
</script>

<template>
    <Head :title="record.title" />
    <AdminLayout>
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-aski-700">DETAIL HKI</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">{{ record.title }}</h1>
            </div>
            <Link :href="route('admin.intellectual-properties.index')" class="rounded-lg border border-aski-200 px-4 py-2 font-semibold text-aski-700">Kembali</Link>
        </div>

        <div class="card mt-7 p-6">
            <div class="border-b border-aski-100 pb-5">
                <p class="text-sm text-slate-500">Pemilik Data</p>
                <p class="mt-1 text-xl font-bold text-aski-900">{{ record.user.name }}</p>
                <p class="text-sm text-slate-500">{{ record.user.email }}</p>
                <p class="mt-2 text-sm font-semibold text-gold-600">{{ record.user.member_profile?.institution_name || 'Individu' }}</p>
            </div>
            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <div><p class="text-xs font-semibold uppercase text-slate-500">Nomor Permohonan</p><p class="mt-1 font-semibold">{{ record.application_number }}</p></div>
                <div><p class="text-xs font-semibold uppercase text-slate-500">Tanggal Permohonan</p><p class="mt-1 font-semibold">{{ record.application_date }}</p></div>
                <div><p class="text-xs font-semibold uppercase text-slate-500">Pencipta</p><p class="mt-1 font-semibold">{{ record.creator }}</p></div>
                <div><p class="text-xs font-semibold uppercase text-slate-500">Pemegang Hak</p><p class="mt-1 font-semibold">{{ record.rights_holder }}</p></div>
                <div><p class="text-xs font-semibold uppercase text-slate-500">Jenis HKI</p><p class="mt-1 font-semibold">{{ record.ip_type }}</p></div>
                <div><p class="text-xs font-semibold uppercase text-slate-500">Nomor Pencatatan</p><p class="mt-1 font-semibold">{{ record.recordation_number || '-' }}</p></div>
                <div><p class="text-xs font-semibold uppercase text-slate-500">Tanggal Diumumkan</p><p class="mt-1 font-semibold">{{ record.first_published_at }}</p></div>
                <div><p class="text-xs font-semibold uppercase text-slate-500">Jangka Waktu Pelindungan</p><p class="mt-1 font-semibold">{{ record.protection_period }}</p></div>
            </div>
        </div>
    </AdminLayout>
</template>
