<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { formatDateTime } from '@/utils/date.js';

defineProps({ ticket: Object, statusLabels: Object });
</script>

<template>
    <Head :title="`Tiket Bantuan — ${ticket.subject}`" />
    <MemberLayout>
        <div class="mx-auto max-w-5xl space-y-6">
            <Link :href="route('member.support.index')" class="inline-flex min-h-9 items-center gap-2 rounded-lg text-sm font-medium text-slate-500 transition hover:text-aski-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
                <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.78 15.53a.75.75 0 0 1-1.06 0l-5-5a.75.75 0 0 1 0-1.06l5-5a.75.75 0 1 1 1.06 1.06L8.31 10l4.47 4.47a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" /></svg>
                Kembali ke Bantuan & FAQ
            </Link>

            <header class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-aski-700">Detail tiket bantuan</p>
                        <h1 class="mt-2 break-words text-xl font-bold tracking-tight text-aski-900 sm:text-2xl">{{ ticket.subject }}</h1>
                        <p class="mt-2 text-sm text-slate-500">Nomor tiket <span class="font-semibold text-slate-700">#{{ ticket.id }}</span></p>
                    </div>
                    <div class="self-start">
                        <StatusBadge :status="ticket.status" :label="statusLabels[ticket.status] || 'Status tidak dikenal'" />
                    </div>
                </div>
            </header>

            <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_300px]">
                <section class="space-y-5" aria-label="Isi tiket dan jawaban">
                    <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-sm font-bold text-slate-700" aria-hidden="true">M</span>
                            <div>
                                <h2 class="text-sm font-semibold text-slate-900">Pesan Anda</h2>
                                <p class="mt-0.5 text-xs text-slate-500">{{ formatDateTime(ticket.created_at) }}</p>
                            </div>
                        </div>
                        <div class="whitespace-pre-line break-words px-5 py-5 text-sm leading-7 text-slate-700 sm:px-6">{{ ticket.message }}</div>
                    </article>

                    <article v-if="ticket.admin_response" class="overflow-hidden rounded-2xl border border-aski-100 bg-white shadow-sm">
                        <div class="flex items-center gap-3 border-b border-aski-100 bg-aski-50/70 px-5 py-4 sm:px-6">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-aski-800 text-sm font-bold text-white" aria-hidden="true">A</span>
                            <div>
                                <h2 class="text-sm font-semibold text-aski-900">Jawaban Admin</h2>
                                <p v-if="ticket.responded_at" class="mt-0.5 text-xs text-slate-600">Dijawab {{ formatDateTime(ticket.responded_at) }}</p>
                            </div>
                        </div>
                        <div class="whitespace-pre-line break-words px-5 py-5 text-sm leading-7 text-slate-700 sm:px-6">{{ ticket.admin_response }}</div>
                    </article>

                    <div v-else class="flex gap-3 rounded-2xl border border-gold-400 bg-gold-100 p-4 sm:p-5" role="status">
                        <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-aski-800" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a.75.75 0 0 1 .75.75v6.5a.75.75 0 0 1-1.5 0v-6.5A.75.75 0 0 1 10 2Zm0 11.5a1 1 0 1 0 0 2 1 1 0 0 0 0-2ZM18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z" clip-rule="evenodd"/></svg>
                        <div>
                            <h2 class="text-sm font-semibold text-aski-900">Belum ada jawaban dari Admin</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-700">Tiket Anda sudah tercatat. Silakan periksa kembali halaman ini untuk melihat jawaban atau perubahan status.</p>
                        </div>
                    </div>
                </section>

                <aside class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="ticket-info-heading">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 id="ticket-info-heading" class="text-sm font-semibold text-aski-900">Informasi tiket</h2>
                    </div>
                    <dl class="divide-y divide-slate-100 px-5">
                        <div class="py-4">
                            <dt class="text-xs font-medium text-slate-500">Status saat ini</dt>
                            <dd class="mt-2"><StatusBadge :status="ticket.status" :label="statusLabels[ticket.status] || 'Status tidak dikenal'" /></dd>
                        </div>
                        <div class="py-4">
                            <dt class="text-xs font-medium text-slate-500">Dikirim</dt>
                            <dd class="mt-1.5 text-sm font-semibold text-slate-800">{{ formatDateTime(ticket.created_at) }}</dd>
                        </div>
                        <div class="py-4">
                            <dt class="text-xs font-medium text-slate-500">Waktu jawaban</dt>
                            <dd class="mt-1.5 text-sm font-semibold text-slate-800">{{ ticket.responded_at ? formatDateTime(ticket.responded_at) : 'Belum dijawab' }}</dd>
                        </div>
                    </dl>
                    <div class="border-t border-slate-100 bg-slate-50 px-5 py-4">
                        <Link :href="route('member.support.index')" class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-aski-800 transition hover:bg-aski-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">Kembali ke daftar bantuan</Link>
                    </div>
                </aside>
            </div>
        </div>
    </MemberLayout>
</template>
