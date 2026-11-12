<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PaginationLinks from '@/Components/PaginationLinks.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import ConfirmActionDialog from '@/Components/ConfirmActionDialog.vue';
import { ref } from 'vue';

defineProps({ members: Object });
const pendingMember = ref(null);
const confirmingDelete = ref(false);
const deleteProcessing = ref(false);

const remove = (member) => {
    pendingMember.value = member;
    confirmingDelete.value = true;
};

const confirmDelete = () => {
    if (!pendingMember.value || deleteProcessing.value) return;
    deleteProcessing.value = true;
    router.delete(route('admin.organization-members.destroy', pendingMember.value.id), {
        onSuccess: () => { confirmingDelete.value = false; pendingMember.value = null; },
        onFinish: () => { deleteProcessing.value = false; },
    });
};
</script>

<template>
    <Head title="Struktur Organisasi" />
    <AdminLayout>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-aski-700">STRUKTUR ORGANISASI</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">Kelola Pengurus</h1>
            </div>
            <Link :href="route('admin.organization-members.create')" class="btn-primary inline-flex min-h-11 items-center justify-center">Tambah Pengurus</Link>
        </div>

        <section class="card mt-6 overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6"><h2 class="font-semibold text-aski-900">Daftar Pengurus</h2><p class="mt-1 text-sm text-slate-500">{{ members.from || 0 }}–{{ members.to || 0 }} dari {{ members.total }} pengurus</p></div>
            <div tabindex="0" role="region" aria-label="Tabel struktur organisasi, gunakan gulir horizontal untuk melihat kolom lain" class="overflow-x-auto focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-aski-50 text-aski-900">
                    <tr>
                        <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Urutan</th>
                        <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Nama</th>
                        <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Jabatan</th>
                        <th scope="col" class="px-4 py-3.5 font-semibold sm:px-6">Status</th>
                        <th scope="col" class="px-4 py-3.5 text-right font-semibold sm:px-6">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="member in members.data" :key="member.id" class="transition-colors hover:bg-slate-50/70">
                        <td class="px-4 py-3.5 text-slate-600 sm:px-6">{{ member.sort_order }}</td>
                        <td class="px-4 py-3.5 font-semibold text-aski-900 sm:px-6">{{ member.name }}</td>
                        <td class="whitespace-nowrap px-4 py-3.5 text-slate-700 sm:px-6">{{ member.position }}</td>
                        <td class="px-4 py-3.5 sm:px-6"><StatusBadge :status="member.is_active ? 'active' : 'inactive'" :label="member.status_label" /></td>
                        <td class="px-4 py-3.5 text-right sm:px-6">
                            <div class="flex justify-end gap-2">
                                <Link :href="route('admin.organization-members.edit', member.id)" class="inline-flex min-h-9 items-center rounded-lg px-3 font-semibold text-aski-700 hover:bg-aski-50">Ubah</Link>
                                <button class="inline-flex min-h-9 items-center rounded-lg px-3 font-semibold text-red-700 hover:bg-red-50" @click="remove(member)">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="members.data.length === 0">
                        <td colspan="5" class="px-4 py-10 text-center text-slate-500 sm:px-6">Belum ada data struktur organisasi.</td>
                    </tr>
                </tbody>
            </table></div>
        </section>
        <PaginationLinks :links="members.links" />
        <ConfirmActionDialog :show="confirmingDelete" title="Hapus pengurus?" description="Data pengurus ini akan dihapus dari struktur organisasi dan tidak bisa dipulihkan." :item="pendingMember?.name" action-label="Hapus pengurus" :processing="deleteProcessing" @close="confirmingDelete = false" @confirm="confirmDelete" />
    </AdminLayout>
</template>
