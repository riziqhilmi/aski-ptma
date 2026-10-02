<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';

defineProps({ ticket: Object });
const labels = { open: 'Terbuka', answered: 'Terjawab', closed: 'Ditutup' };
</script>

<template>
    <Head title="Detail Tiket Bantuan" />
    <MemberLayout>
        <Link :href="route('member.support.index')" class="text-sm font-semibold text-aski-700">← Kembali ke Bantuan</Link>
        <section class="card mt-6 max-w-3xl p-7">
            <p class="text-sm font-semibold text-aski-700">TIKET #{{ ticket.id }}</p>
            <h1 class="mt-1 text-3xl font-bold text-aski-900">{{ ticket.subject }}</h1>
            <span class="mt-4 inline-block rounded-full bg-gold-100 px-3 py-1 text-xs font-semibold text-gold-700">{{ labels[ticket.status] }}</span>
            <div class="mt-6 rounded-xl bg-slate-50 p-5 text-sm leading-7 text-slate-700 whitespace-pre-line">{{ ticket.message }}</div>
            <div v-if="ticket.admin_response" class="mt-5 rounded-xl border border-aski-100 bg-aski-50 p-5 text-sm leading-7 text-aski-900 whitespace-pre-line">
                <b>Jawaban Admin</b>
                <p class="mt-2">{{ ticket.admin_response }}</p>
            </div>
            <p v-else class="mt-5 text-sm text-slate-500">Admin belum memberikan jawaban.</p>
        </section>
    </MemberLayout>
</template>
