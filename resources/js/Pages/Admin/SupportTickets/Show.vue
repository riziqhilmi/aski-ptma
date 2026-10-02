<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ ticket: Object });
const labels = { open: 'Terbuka', answered: 'Terjawab', closed: 'Ditutup' };
const form = useForm({ status: props.ticket.status === 'closed' ? 'closed' : 'answered', admin_response: props.ticket.admin_response || '' });
const submit = () => form.put(route('admin.support-tickets.update', props.ticket.id));
const closeTicket = () => {
    form.status = 'closed';
    form.put(route('admin.support-tickets.update', props.ticket.id));
};
</script>

<template>
    <Head title="Detail Tiket Bantuan" />
    <AdminLayout>
        <Link :href="route('admin.support-tickets.index')" class="text-sm font-semibold text-aski-700">← Kembali</Link>
        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <section class="card p-7 lg:col-span-2">
                <p class="text-sm font-semibold text-aski-700">TIKET #{{ ticket.id }}</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">{{ ticket.subject }}</h1>
                <p class="mt-2 text-sm text-slate-500">Dikirim oleh {{ ticket.user.name }} · {{ labels[ticket.status] }}</p>
                <div class="mt-6 rounded-xl bg-slate-50 p-5 text-sm leading-7 text-slate-700 whitespace-pre-line">{{ ticket.message }}</div>
                <div v-if="ticket.admin_response" class="mt-5 rounded-xl border border-aski-100 bg-aski-50 p-5 text-sm leading-7 text-aski-900 whitespace-pre-line">
                    <b>Jawaban Admin</b>
                    <p class="mt-2">{{ ticket.admin_response }}</p>
                </div>
            </section>
            <form class="card p-7" @submit.prevent="submit">
                <h2 class="font-bold text-aski-900">Tindak Lanjut</h2>
                <label class="mt-5 block text-sm font-semibold text-slate-700">Jawaban Admin
                    <textarea v-model="form.admin_response" rows="8" class="mt-1 w-full rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500"></textarea>
                    <span class="text-xs text-red-600">{{ form.errors.admin_response }}</span>
                </label>
                <button class="btn-primary mt-4 w-full">Kirim Jawaban</button>
                <button type="button" class="mt-3 w-full rounded-lg border border-aski-200 px-4 py-2 text-sm font-semibold text-aski-700" @click="closeTicket">Tutup Tiket</button>
            </form>
        </div>
    </AdminLayout>
</template>
