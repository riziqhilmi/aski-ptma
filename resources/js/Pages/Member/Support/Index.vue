<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';

defineProps({ faqs: Array, tickets: Object });

const labels = { open: 'Terbuka', answered: 'Terjawab', closed: 'Ditutup' };
const form = useForm({ subject: '', message: '' });
const submit = () => form.post(route('member.support.store'), {
    onSuccess: () => form.reset(),
});
</script>

<template>
    <Head title="Bantuan" />
    <MemberLayout>
        <div>
            <p class="text-sm font-semibold text-aski-700">HELP CENTER</p>
            <h1 class="mt-1 text-3xl font-bold text-aski-900">Bantuan Member</h1>
        </div>

        <div class="mt-7 grid gap-6 lg:grid-cols-3">
            <section class="card p-7 lg:col-span-2">
                <h2 class="text-xl font-bold text-aski-900">FAQ</h2>
                <div class="mt-5 space-y-4">
                    <article v-for="faq in faqs" :key="faq.id" class="rounded-xl border border-aski-100 bg-white p-5">
                        <h3 class="font-bold text-aski-900">{{ faq.question }}</h3>
                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ faq.answer }}</p>
                    </article>
                    <p v-if="faqs.length === 0" class="text-sm text-slate-500">Belum ada FAQ yang diterbitkan.</p>
                </div>
            </section>

            <form class="card p-7" @submit.prevent="submit">
                <h2 class="text-xl font-bold text-aski-900">Hubungi Admin</h2>
                <label class="mt-5 block text-sm font-semibold text-slate-700">Subjek
                    <input v-model="form.subject" class="mt-1 w-full rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500" />
                    <span class="text-xs text-red-600">{{ form.errors.subject }}</span>
                </label>
                <label class="mt-4 block text-sm font-semibold text-slate-700">Pesan
                    <textarea v-model="form.message" rows="7" class="mt-1 w-full rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500"></textarea>
                    <span class="text-xs text-red-600">{{ form.errors.message }}</span>
                </label>
                <button class="btn-primary mt-5 w-full">Kirim Tiket</button>
            </form>
        </div>

        <section class="card mt-7 overflow-x-auto">
            <div class="border-b border-slate-100 p-5">
                <h2 class="text-xl font-bold text-aski-900">Riwayat Tiket Saya</h2>
            </div>
            <table class="min-w-full text-left text-sm">
                <thead class="bg-aski-50 text-aski-900"><tr><th class="p-4">Subjek</th><th class="p-4">Status</th><th class="p-4"></th></tr></thead>
                <tbody>
                    <tr v-for="ticket in tickets.data" :key="ticket.id" class="border-t border-slate-100">
                        <td class="p-4 font-semibold">{{ ticket.subject }}</td>
                        <td class="p-4"><span class="rounded-full bg-gold-100 px-3 py-1 text-xs font-semibold text-gold-700">{{ labels[ticket.status] }}</span></td>
                        <td class="p-4 text-right"><Link :href="route('member.support.show', ticket.id)" class="font-semibold text-aski-700">Detail</Link></td>
                    </tr>
                    <tr v-if="tickets.data.length === 0"><td colspan="3" class="p-8 text-center text-slate-500">Belum ada tiket bantuan.</td></tr>
                </tbody>
            </table>
        </section>
    </MemberLayout>
</template>
