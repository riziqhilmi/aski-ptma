<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';

const props = defineProps({ record: Object });
const destroyRecord = () => {
    if (confirm('Hapus data HKI ini?')) {
        router.delete(route('member.intellectual-properties.destroy', props.record.id));
    }
};
</script>

<template>
    <Head :title="record.title" />
    <MemberLayout>
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-aski-700">DETAIL HKI</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">{{ record.title }}</h1>
            </div>
            <div class="flex gap-3">
                <Link :href="route('member.intellectual-properties.edit', record.id)" class="btn-primary">Ubah</Link>
                <button class="rounded-lg border border-red-200 px-4 py-2 font-semibold text-red-700 hover:bg-red-50" @click="destroyRecord">Hapus</button>
            </div>
        </div>
        <div class="card mt-7 grid gap-5 p-6 md:grid-cols-2">
            <div><p class="text-xs font-semibold uppercase text-slate-500">Nomor Permohonan</p><p class="mt-1 font-semibold">{{ record.application_number }}</p></div>
            <div><p class="text-xs font-semibold uppercase text-slate-500">Tanggal Permohonan</p><p class="mt-1 font-semibold">{{ record.application_date }}</p></div>
            <div><p class="text-xs font-semibold uppercase text-slate-500">Pencipta</p><p class="mt-1 font-semibold">{{ record.creator }}</p></div>
            <div><p class="text-xs font-semibold uppercase text-slate-500">Pemegang Hak</p><p class="mt-1 font-semibold">{{ record.rights_holder }}</p></div>
            <div><p class="text-xs font-semibold uppercase text-slate-500">Jenis HKI</p><p class="mt-1 font-semibold">{{ record.ip_type }}</p></div>
            <div><p class="text-xs font-semibold uppercase text-slate-500">Nomor Pencatatan</p><p class="mt-1 font-semibold">{{ record.recordation_number || '-' }}</p></div>
            <div><p class="text-xs font-semibold uppercase text-slate-500">Tanggal Diumumkan</p><p class="mt-1 font-semibold">{{ record.first_published_at }}</p></div>
            <div><p class="text-xs font-semibold uppercase text-slate-500">Jangka Waktu Pelindungan</p><p class="mt-1 font-semibold">{{ record.protection_period }}</p></div>
        </div>
    </MemberLayout>
</template>
