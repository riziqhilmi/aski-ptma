<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ members: Object });

const remove = (member) => {
    if (confirm(`Hapus ${member.name} dari struktur organisasi?`)) {
        router.delete(route('admin.organization-members.destroy', member.id));
    }
};
</script>

<template>
    <Head title="Struktur Organisasi" />
    <AdminLayout>
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-aski-700">STRUKTUR ORGANISASI</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">Kelola Pengurus</h1>
            </div>
            <Link :href="route('admin.organization-members.create')" class="btn-primary">Tambah Pengurus</Link>
        </div>

        <div class="card mt-7 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-aski-50 text-aski-900">
                    <tr>
                        <th class="p-4">Urutan</th>
                        <th class="p-4">Nama</th>
                        <th class="p-4">Jabatan</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="member in members.data" :key="member.id" class="border-t border-aski-100">
                        <td class="p-4">{{ member.sort_order }}</td>
                        <td class="p-4 font-semibold text-aski-900">{{ member.name }}</td>
                        <td class="p-4">{{ member.position }}</td>
                        <td class="p-4"><span class="rounded-full px-3 py-1 text-xs font-semibold" :class="member.is_active ? 'bg-aski-50 text-aski-700' : 'bg-slate-100 text-slate-600'">{{ member.status_label }}</span></td>
                        <td class="p-4">
                            <div class="flex gap-3">
                                <Link :href="route('admin.organization-members.edit', member.id)" class="font-semibold text-aski-700 hover:text-aski-900">Ubah</Link>
                                <button class="font-semibold text-red-700 hover:text-red-900" @click="remove(member)">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="members.data.length === 0">
                        <td colspan="5" class="p-8 text-center text-slate-500">Belum ada data struktur organisasi.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
