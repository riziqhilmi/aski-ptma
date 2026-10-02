<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ tickets: Object, filters: Object });

const labels = { open: 'Terbuka', answered: 'Terjawab', closed: 'Ditutup' };
const filter = (event) => router.get(route('admin.support-tickets.index'), { status: event.target.value }, { preserveState: true });
</script>

<template>
    <Head title="Tiket Bantuan" />
    <AdminLayout>
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-aski-700">BANTUAN</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">Tiket Bantuan Member</h1>
            </div>
            <select :value="filters.status" class="rounded-lg border-slate-300 text-sm focus:border-aski-500 focus:ring-aski-500" @change="filter">
                <option value="">Semua status</option>
                <option value="open">Terbuka</option>
                <option value="answered">Terjawab</option>
                <option value="closed">Ditutup</option>
            </select>
        </div>

        <div class="card mt-7 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-aski-50 text-aski-900"><tr><th class="p-4">Member</th><th class="p-4">Subjek</th><th class="p-4">Status</th><th class="p-4"></th></tr></thead>
                <tbody>
                    <tr v-for="ticket in tickets.data" :key="ticket.id" class="border-t border-slate-100">
                        <td class="p-4"><b>{{ ticket.user.name }}</b><p class="text-slate-500">{{ ticket.user.email }}</p></td>
                        <td class="p-4">{{ ticket.subject }}</td>
                        <td class="p-4"><span class="rounded-full bg-gold-100 px-3 py-1 text-xs font-semibold text-gold-700">{{ labels[ticket.status] }}</span></td>
                        <td class="p-4 text-right"><Link :href="route('admin.support-tickets.show', ticket.id)" class="font-semibold text-aski-700">Detail</Link></td>
                    </tr>
                    <tr v-if="tickets.data.length === 0"><td colspan="4" class="p-8 text-center text-slate-500">Belum ada tiket.</td></tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
