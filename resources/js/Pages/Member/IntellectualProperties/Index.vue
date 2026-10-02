<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';

defineProps({ records: Object });
</script>

<template>
    <Head title="Data HKI Saya" />
    <MemberLayout>
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-aski-700">DATA HKI</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">Data HKI Saya</h1>
            </div>
            <Link :href="route('member.intellectual-properties.create')" class="btn-primary">Tambah Data HKI</Link>
        </div>

        <div class="card mt-7 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-aski-50 text-aski-900">
                    <tr>
                        <th class="p-4">Judul</th>
                        <th class="p-4">Jenis</th>
                        <th class="p-4">Nomor Permohonan</th>
                        <th class="p-4">Tanggal</th>
                        <th class="p-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="record in records.data" :key="record.id" class="border-t border-aski-100">
                        <td class="p-4 font-semibold text-aski-900">{{ record.title }}</td>
                        <td class="p-4">{{ record.ip_type }}</td>
                        <td class="p-4">{{ record.application_number }}</td>
                        <td class="p-4">{{ record.application_date }}</td>
                        <td class="p-4">
                            <Link :href="route('member.intellectual-properties.show', record.id)" class="font-semibold text-aski-700 hover:text-aski-900">Detail</Link>
                        </td>
                    </tr>
                    <tr v-if="records.data.length === 0">
                        <td colspan="5" class="p-8 text-center text-slate-500">Belum ada data HKI.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="records.links?.length > 3" class="mt-5 flex flex-wrap gap-2">
            <Link v-for="link in records.links" :key="link.label" :href="link.url || '#'" v-html="link.label" class="rounded-lg border px-3 py-2 text-sm" :class="link.active ? 'border-aski-700 bg-aski-700 text-white' : 'border-aski-100 bg-white text-aski-700'" />
        </div>
    </MemberLayout>
</template>
