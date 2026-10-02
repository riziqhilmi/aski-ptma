<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ account: Object });
</script>

<template>
    <Head title="Detail Akun" />
    <AdminLayout><Link :href="route('admin.accounts.index')" class="text-sm font-semibold text-aski-700">← Kembali ke semua akun</Link><div class="mt-5 flex flex-wrap items-center justify-between gap-3"><div><p class="text-sm font-semibold text-aski-700">MANAJEMEN AKUN</p><h1 class="mt-1 text-3xl font-bold text-aski-900">{{ account.name }}</h1></div><span class="rounded-full px-3 py-1 text-sm font-semibold" :class="account.completion_status === 'lengkap' ? 'bg-aski-100 text-aski-800' : 'bg-gold-100 text-gold-600'">{{ account.completion_status }}</span></div><div class="card mt-7 max-w-3xl divide-y divide-slate-100"><div class="grid gap-5 p-6 sm:grid-cols-2"><div><p class="text-xs font-semibold uppercase text-slate-500">Email</p><p class="mt-1">{{ account.email }}</p></div><div><p class="text-xs font-semibold uppercase text-slate-500">Tipe akun</p><p class="mt-1">{{ account.profile?.account_type === 'institution' ? 'Institusi' : account.profile?.account_type === 'individual' ? 'Individu' : 'Belum dipilih' }}</p></div></div><div class="grid gap-5 p-6 sm:grid-cols-2"><div><p class="text-xs font-semibold uppercase text-slate-500">Telepon</p><p class="mt-1">{{ account.profile?.phone || '-' }}</p></div><div><p class="text-xs font-semibold uppercase text-slate-500">Alamat</p><p class="mt-1 whitespace-pre-line">{{ account.profile?.address || '-' }}</p></div></div><div v-if="account.profile?.account_type === 'institution'" class="grid gap-5 p-6 sm:grid-cols-2"><div><p class="text-xs font-semibold uppercase text-slate-500">Institusi</p><p class="mt-1">{{ account.profile.institution_name || '-' }}</p></div><div><p class="text-xs font-semibold uppercase text-slate-500">Penanggung jawab</p><p class="mt-1">{{ account.profile.responsible_name || '-' }}<span v-if="account.profile.responsible_position"> · {{ account.profile.responsible_position }}</span></p></div></div></div></AdminLayout>
</template>
