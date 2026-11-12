<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import PaginationLinks from '@/Components/PaginationLinks.vue';
import { formatDateTime } from '@/utils/date.js';

defineProps({ faqs: Array, tickets: Object, statusLabels: Object });
const form = useForm({ subject: '', message: '' });
const submit = () => form.post(route('member.support.store'), {
    onSuccess: () => form.reset(),
});
</script>

<template>
    <Head title="Bantuan & FAQ" />
    <MemberLayout>
        <div class="space-y-7">
            <header class="rounded-2xl border border-aski-100 bg-white p-5 shadow-sm sm:p-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-aski-700">Pusat Bantuan Member</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-aski-900 sm:text-3xl">Bantuan & FAQ</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Temukan jawaban dari pertanyaan umum atau kirim tiket jika Anda membutuhkan bantuan lebih lanjut.</p>
                <div class="mt-5 flex flex-wrap gap-2 text-xs font-medium text-slate-600">
                    <span class="inline-flex items-center gap-2 rounded-full bg-aski-50 px-3 py-1.5 text-aski-800"><span class="h-1.5 w-1.5 rounded-full bg-aski-700"></span>{{ faqs.length }} pertanyaan umum</span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5">{{ tickets.total }} tiket bantuan Anda</span>
                </div>
            </header>

            <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.55fr)_minmax(320px,0.85fr)]">
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="faq-heading">
                    <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-aski-50 text-aski-800" aria-hidden="true">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9.5 9a2.5 2.5 0 1 1 4.38 1.66c-.83.91-1.88 1.34-1.88 2.84M12 17.25h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            </span>
                            <div>
                                <h2 id="faq-heading" class="text-lg font-bold text-aski-900">Pertanyaan yang sering diajukan</h2>
                                <p class="mt-1 text-sm text-slate-500">Pilih pertanyaan untuk membaca jawabannya.</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="faqs.length" class="divide-y divide-slate-100">
                        <details v-for="(faq, index) in faqs" :key="faq.id" class="group px-5 first-of-type:pt-1 last-of-type:pb-1 sm:px-6">
                            <summary class="flex cursor-pointer list-none items-center gap-3 py-4 text-left focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700 [&::-webkit-details-marker]:hidden">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600">{{ String(index + 1).padStart(2, '0') }}</span>
                                <span class="flex-1 text-sm font-semibold leading-6 text-slate-900">{{ faq.question }}</span>
                                <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-slate-500 transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                            </summary>
                            <p class="whitespace-pre-line pb-5 pl-10 text-sm leading-6 text-slate-600">{{ faq.answer }}</p>
                        </details>
                    </div>
                    <div v-else class="px-5 py-12 text-center sm:px-8">
                        <p class="text-sm font-semibold text-slate-800">Belum ada FAQ yang diterbitkan</p>
                        <p class="mt-1 text-sm text-slate-500">Anda tetap dapat menghubungi Admin melalui formulir bantuan.</p>
                    </div>
                </section>

                <form class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" @submit.prevent="submit">
                    <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-aski-800 text-white" aria-hidden="true">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="m4 5 8 7 8-7M5.5 19h13A1.5 1.5 0 0 0 20 17.5v-11A1.5 1.5 0 0 0 18.5 5h-13A1.5 1.5 0 0 0 4 6.5v11A1.5 1.5 0 0 0 5.5 19Z"/></svg>
                            </span>
                            <div>
                                <h2 class="text-lg font-bold text-aski-900">Hubungi Admin</h2>
                                <p class="mt-1 text-sm text-slate-500">Ceritakan kendala Anda melalui tiket bantuan.</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4 p-5 sm:p-6">
                        <div>
                            <label for="ticket-subject" class="block text-sm font-semibold text-slate-800">Subjek <span class="text-red-600" aria-hidden="true">*</span></label>
                            <input id="ticket-subject" v-model="form.subject" required aria-required="true" :aria-invalid="Boolean(form.errors.subject)" :aria-describedby="form.errors.subject ? 'ticket-subject-error' : undefined" maxlength="255" placeholder="Contoh: Kendala pembayaran tagihan" class="mt-1.5 min-h-11 w-full rounded-lg border-slate-300 text-sm placeholder:text-slate-400 focus:border-aski-500 focus:ring-aski-500" />
                            <p v-if="form.errors.subject" id="ticket-subject-error" class="mt-1.5 text-xs text-red-600">{{ form.errors.subject }}</p>
                        </div>
                        <div>
                            <label for="ticket-message" class="block text-sm font-semibold text-slate-800">Pesan <span class="text-red-600" aria-hidden="true">*</span></label>
                            <textarea id="ticket-message" v-model="form.message" required aria-required="true" :aria-invalid="Boolean(form.errors.message)" :aria-describedby="form.errors.message ? 'ticket-message-error' : undefined" rows="6" placeholder="Jelaskan kendala atau pertanyaan Anda secara rinci." class="mt-1.5 w-full resize-y rounded-lg border-slate-300 text-sm leading-6 placeholder:text-slate-400 focus:border-aski-500 focus:ring-aski-500"></textarea>
                            <p v-if="form.errors.message" id="ticket-message-error" class="mt-1.5 text-xs text-red-600">{{ form.errors.message }}</p>
                        </div>
                        <p class="text-xs leading-5 text-slate-500"><span class="font-bold text-red-600">*</span> Wajib diisi. Tiket yang dikirim dapat dipantau pada riwayat tiket Anda.</p>
                        <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg bg-aski-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-aski-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700 disabled:cursor-not-allowed disabled:opacity-60" :disabled="form.processing">
                            <svg v-if="!form.processing" aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M3.11 2.27a.75.75 0 0 1 .78-.09l13 6a2 2 0 0 1 0 3.64l-13 6A.75.75 0 0 1 2.84 17l1.24-5.16a.75.75 0 0 1 .57-.55l5.14-1.14-5.14-1.14a.75.75 0 0 1-.57-.55L2.84 3.3a.75.75 0 0 1 .27-1.03Z"/></svg>
                            {{ form.processing ? 'Mengirim tiket…' : 'Kirim tiket bantuan' }}
                        </button>
                    </div>
                </form>
            </div>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="ticket-history-heading">
                <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <h2 id="ticket-history-heading" class="text-lg font-bold text-aski-900">Riwayat tiket bantuan</h2>
                        <p class="mt-1 text-sm text-slate-500">Lihat perkembangan pertanyaan yang sudah Anda kirim.</p>
                    </div>
                    <span class="inline-flex w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ tickets.total }} tiket</span>
                </div>

                <div v-if="tickets.data.length" class="divide-y divide-slate-100">
                    <article v-for="ticket in tickets.data" :key="ticket.id" class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div class="min-w-0">
                            <h3 class="break-words text-sm font-semibold text-slate-900">{{ ticket.subject }}</h3>
                            <p class="mt-1 text-xs text-slate-500">Dikirim {{ formatDateTime(ticket.created_at) }}</p>
                        </div>
                        <div class="flex items-center justify-between gap-4 sm:justify-end">
                            <StatusBadge :status="ticket.status" :label="statusLabels[ticket.status] || 'Status tidak dikenal'" />
                            <Link :href="route('member.support.show', ticket.id)" class="inline-flex min-h-9 items-center gap-1 rounded-lg px-3 text-sm font-semibold text-aski-800 transition hover:bg-aski-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">Lihat tiket <span aria-hidden="true">→</span></Link>
                        </div>
                    </article>
                </div>
                <div v-else class="px-5 py-10 text-center sm:px-8">
                    <p class="text-sm font-semibold text-slate-800">Belum ada riwayat tiket</p>
                    <p class="mt-1 text-sm text-slate-500">Tiket yang Anda kirim akan tampil di bagian ini.</p>
                </div>
            </section>
            <PaginationLinks :links="tickets.links" />
        </div>
    </MemberLayout>
</template>
