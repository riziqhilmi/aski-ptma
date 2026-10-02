<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ accounts: Object, filters: Object });
const applyFilters = (event) => router.get(route('admin.accounts.index'), { type: event.target.elements.type.value, status: event.target.elements.status.value }, { preserveState: true, replace: true });
const typeLabel = (type) => ({ individual: 'Individu', institution: 'Institusi' }[type] || 'Belum dipilih');
</script>

<template>
    <Head title="Manajemen Akun" />
    <AdminLayout>
        <div class="flex flex-wrap items-end justify-between gap-4"><div><p class="text-sm font-semibold text-aski-700">MANAJEMEN AKUN</p><h1 class="mt-1 text-3xl font-bold text-aski-900">Semua Akun Member</h1></div><form class="flex flex-wrap gap-3" @change="applyFilters"><select name="type" :value="filters.type" class="rounded-lg border-slate-300 text-sm focus:border-aski-500 focus:ring-aski-500"><option value="">Semua tipe</option><option value="individual">Individu</option><option value="institution">Institusi</option><option value="unselected">Belum memilih tipe</option></select><select name="status" :value="filters.status" class="rounded-lg border-slate-300 text-sm focus:border-aski-500 focus:ring-aski-500"><option value="">Semua status</option><option value="belum_lengkap">Belum lengkap</option></select></form></div>
        <div class="card mt-7 overflow-hidden"><div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-100 text-left text-sm"><thead class="bg-aski-50 text-aski-900"><tr><th class="px-6 py-4 font-semibold">Akun</th><th class="px-6 py-4 font-semibold">Tipe</th><th class="px-6 py-4 font-semibold">Status profil</th><th class="px-6 py-4"></th></tr></thead><tbody class="divide-y divide-slate-100"><tr v-for="account in accounts.data" :key="account.id"><td class="px-6 py-4"><p class="font-semibold text-slate-800">{{ account.name }}</p><p class="text-slate-500">{{ account.email }}</p></td><td class="px-6 py-4">{{ typeLabel(account.account_type) }}</td><td class="px-6 py-4"><span class="rounded-full px-3 py-1 text-xs font-semibold" :class="account.completion_status === 'lengkap' ? 'bg-aski-100 text-aski-800' : 'bg-gold-100 text-gold-600'">{{ account.completion_status }}</span></td><td class="px-6 py-4 text-right"><Link :href="route('admin.accounts.show', account.id)" class="font-semibold text-aski-700 hover:text-aski-900">Detail</Link></td></tr><tr v-if="accounts.data.length === 0"><td colspan="4" class="px-6 py-10 text-center text-slate-500">Tidak ada akun yang sesuai.</td></tr></tbody></table></div><div v-if="accounts.last_page > 1" class="flex gap-2 border-t border-slate-100 px-6 py-4"><Link v-for="link in accounts.links" :key="link.label" :href="link.url || '#'" :class="['rounded px-3 py-1 text-sm', link.active ? 'bg-aski-700 text-white' : 'text-aski-700', !link.url && 'pointer-events-none opacity-40']" v-html="link.label" /></div></div>
    </AdminLayout>
</template>
