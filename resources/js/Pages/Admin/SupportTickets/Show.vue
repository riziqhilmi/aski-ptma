<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { formatDateTime } from '@/utils/date';

const props = defineProps({ ticket: Object, statusLabels: Object });
const form = useForm({
    status: props.ticket.status === 'closed' ? 'open' : 'answered',
    admin_response: props.ticket.admin_response || '',
});
const submit = () => form.put(route('admin.support-tickets.update', props.ticket.id));
const closeTicket = () => {
    form.status = 'closed';
    form.put(route('admin.support-tickets.update', props.ticket.id));
};
</script>

<template>
    <Head title="Detail Tiket Bantuan" />
    <AdminLayout>
        <div class="mx-auto max-w-6xl">
            <Link :href="route('admin.support-tickets.index')" class="inline-flex min-h-9 items-center text-sm font-semibold text-aski-700 hover:text-aski-900">← Kembali ke Tiket Bantuan</Link>

            <header class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-semibold uppercase tracking-wide text-aski-700">Bantuan · Tiket #{{ ticket.id }}</p>
                    <h1 class="mt-1 break-words text-2xl font-bold text-aski-900 sm:text-3xl">{{ ticket.subject }}</h1>
                </div>
                <StatusBadge :status="ticket.status" :label="statusLabels[ticket.status]" />
            </header>

            <div class="mt-6 grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_20rem]">
                <div class="space-y-5">
                    <section class="card overflow-hidden">
                        <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                            <h2 class="font-semibold text-aski-900">Informasi Pemohon</h2>
                        </div>
                        <dl class="grid gap-4 px-5 py-5 sm:grid-cols-2 sm:px-6">
                            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Member</dt><dd class="mt-1 font-semibold text-slate-900">{{ ticket.user.name }}</dd></div>
                            <div class="min-w-0"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</dt><dd class="mt-1 break-all text-sm text-slate-700">{{ ticket.user.email }}</dd></div>
                            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Dikirim</dt><dd class="mt-1 text-sm text-slate-700">{{ formatDateTime(ticket.created_at) }}</dd></div>
                            <div v-if="ticket.responded_at"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Direspons</dt><dd class="mt-1 text-sm text-slate-700">{{ formatDateTime(ticket.responded_at) }}<span v-if="ticket.responder" class="block text-slate-500">oleh {{ ticket.responder.name }}</span></dd></div>
                        </dl>
                    </section>

                    <section class="card overflow-hidden">
                        <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                            <h2 class="font-semibold text-aski-900">Pesan Member</h2>
                        </div>
                        <div class="whitespace-pre-line break-words px-5 py-5 text-sm leading-7 text-slate-700 sm:px-6">{{ ticket.message }}</div>
                    </section>

                    <section v-if="ticket.admin_response" class="overflow-hidden rounded-2xl border border-aski-100 bg-aski-50/70">
                        <div class="border-b border-aski-100 px-5 py-4 sm:px-6"><h2 class="font-semibold text-aski-900">Jawaban Admin</h2></div>
                        <div class="whitespace-pre-line break-words px-5 py-5 text-sm leading-7 text-slate-700 sm:px-6">{{ ticket.admin_response }}</div>
                    </section>
                </div>

                <form v-if="ticket.status !== 'closed'" class="card overflow-hidden lg:sticky lg:top-5" @submit.prevent="submit">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="font-semibold text-aski-900">Tindak Lanjut</h2>
                        <p class="mt-1 text-sm text-slate-500">Tulis jawaban atau tutup tiket.</p>
                    </div>
                    <div class="p-5">
                        <label for="ticket-admin-response" class="text-sm font-semibold text-slate-800">Jawaban Admin <span v-if="form.status === 'answered'" class="text-red-600">*</span></label>
                        <textarea id="ticket-admin-response" v-model="form.admin_response" rows="8" maxlength="5000" :required="form.status === 'answered'" aria-describedby="ticket-response-help" :aria-invalid="Boolean(form.errors.admin_response)" class="mt-2 w-full resize-y rounded-xl border-slate-300 text-sm leading-6 focus:border-aski-500 focus:ring-aski-500"></textarea>
                        <p id="ticket-response-help" class="mt-1 text-xs text-slate-500">Wajib diisi untuk mengirim jawaban.</p>
                        <p v-if="form.errors.admin_response" class="mt-1.5 text-sm text-red-600">{{ form.errors.admin_response }}</p>
                        <div class="mt-5 space-y-2">
                            <button type="submit" class="btn-primary inline-flex min-h-11 w-full items-center justify-center" :disabled="form.processing">{{ form.processing ? 'Memproses…' : 'Kirim Jawaban' }}</button>
                            <button type="button" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700 disabled:opacity-60" :disabled="form.processing" @click="closeTicket">Tutup Tiket</button>
                        </div>
                    </div>
                </form>

                <section v-else class="card overflow-hidden lg:sticky lg:top-5">
                    <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-semibold text-aski-900">Tiket Ditutup</h2></div>
                    <div class="p-5"><p class="text-sm leading-6 text-slate-600">Buka kembali tiket ini jika perlu mengirim jawaban lanjutan.</p><button type="button" class="btn-primary mt-4 inline-flex min-h-11 w-full items-center justify-center" :disabled="form.processing" @click="submit">{{ form.processing ? 'Memproses…' : 'Buka Kembali' }}</button></div>
                </section>
            </div>
        </div>
    </AdminLayout>
</template>
