<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({ payment: Object, statusLabels: Object });
const form = useForm({ status: '', admin_note: '' });
const decide = (status) => {
    form.status = status;
    form.put(route('admin.payments.update', props.payment.id));
};
const formatCurrency = (value) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(value);
const formatDate = (value) => value
    ? new Intl.DateTimeFormat('id-ID', { dateStyle: 'long', timeZone: 'Asia/Jakarta' }).format(new Date(value))
    : '—';
</script>

<template>
    <Head title="Periksa Pembayaran" />
    <AdminLayout>
        <div class="mx-auto max-w-5xl">
            <Link :href="route('admin.payments.index')" class="inline-flex min-h-9 items-center rounded-lg text-sm font-semibold text-aski-700 hover:text-aski-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
                <svg aria-hidden="true" class="mr-2 h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.56l3.22 3.22a.75.75 0 1 1-1.06 1.06l-4.5-4.5a.75.75 0 0 1 0-1.06l4.5-4.5a.75.75 0 1 1 1.06 1.06l-3.22 3.22h10.69A.75.75 0 0 1 17 10Z" clip-rule="evenodd" /></svg>
                Kembali ke Pembayaran
            </Link>

            <header class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-aski-700">Keuangan · Pembayaran</p>
                    <h1 class="mt-1 text-3xl font-bold text-aski-900">Periksa Pembayaran</h1>
                    <p class="mt-2 text-sm text-slate-600">Tinjau detail tagihan dan bukti pembayaran anggota.</p>
                </div>
                <StatusBadge :status="payment.status" :label="statusLabels[payment.status]" />
            </header>

            <div class="mt-6 grid gap-5 lg:grid-cols-[minmax(0,1.2fr)_minmax(18rem,.8fr)]">
                <section class="card overflow-hidden">
                    <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                        <h2 class="font-semibold text-aski-900">Informasi Pembayaran</h2>
                        <p class="mt-1 text-sm text-slate-500">Referensi pembayaran #{{ payment.id }}</p>
                    </div>
                    <dl class="divide-y divide-slate-100 px-5 sm:px-6">
                        <div class="grid gap-1 py-4 sm:grid-cols-[9rem_1fr] sm:gap-4">
                            <dt class="text-sm text-slate-500">Nama member</dt>
                            <dd class="break-words text-sm font-semibold text-slate-900">{{ payment.user.name }}<span class="mt-0.5 block break-all font-normal text-slate-500">{{ payment.user.email }}</span></dd>
                        </div>
                        <div class="grid gap-1 py-4 sm:grid-cols-[9rem_1fr] sm:gap-4">
                            <dt class="text-sm text-slate-500">Nomor tagihan</dt>
                            <dd class="break-all text-sm font-semibold text-aski-800">{{ payment.invoice.number }}</dd>
                        </div>
                        <div class="grid gap-1 py-4 sm:grid-cols-[9rem_1fr] sm:gap-4">
                            <dt class="text-sm text-slate-500">Nominal tagihan</dt>
                            <dd class="text-sm font-semibold text-slate-900">{{ formatCurrency(payment.invoice.amount) }}</dd>
                        </div>
                        <div class="grid gap-1 py-4 sm:grid-cols-[9rem_1fr] sm:gap-4">
                            <dt class="text-sm text-slate-500">Jatuh tempo</dt>
                            <dd class="text-sm text-slate-800">{{ formatDate(payment.invoice.due_at) }}</dd>
                        </div>
                        <div v-if="payment.reviewed_at" class="grid gap-1 py-4 sm:grid-cols-[9rem_1fr] sm:gap-4">
                            <dt class="text-sm text-slate-500">Tanggal diperiksa</dt>
                            <dd class="text-sm text-slate-800">{{ formatDate(payment.reviewed_at) }}</dd>
                        </div>
                        <div v-if="payment.admin_note" class="grid gap-1 py-4 sm:grid-cols-[9rem_1fr] sm:gap-4">
                            <dt class="text-sm text-slate-500">Catatan Admin</dt>
                            <dd class="whitespace-pre-line text-sm text-slate-800">{{ payment.admin_note }}</dd>
                        </div>
                    </dl>
                    <div class="border-t border-slate-100 bg-slate-50/70 p-5 sm:px-6">
                        <a :href="route('admin.payments.proof', payment.id)" target="_blank" rel="noopener" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-aski-200 bg-white px-4 text-sm font-semibold text-aski-700 hover:bg-aski-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
                            <svg aria-hidden="true" class="mr-2 h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3.5a6.5 6.5 0 0 0-6.4 5.4.75.75 0 0 0 1.48.24 5 5 0 0 1 9.84 0 .75.75 0 1 0 1.48-.24A6.5 6.5 0 0 0 10 3.5Z"/><path fill-rule="evenodd" d="M10 7a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm-1.5 3a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Z" clip-rule="evenodd"/></svg>
                            Buka Bukti Pembayaran
                        </a>
                    </div>
                </section>

                <section class="card h-fit overflow-hidden">
                    <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                        <h2 class="font-semibold text-aski-900">Keputusan Verifikasi</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ payment.status === 'submitted' ? 'Pilih hasil setelah bukti diperiksa.' : 'Pembayaran ini sudah diproses.' }}</p>
                    </div>

                    <div v-if="payment.status === 'submitted'" class="p-5 sm:p-6">
                        <label for="admin-note" class="text-sm font-semibold text-slate-800">Catatan Admin <span class="font-normal text-slate-500">(wajib jika ditolak)</span>
                            <textarea id="admin-note" v-model="form.admin_note" rows="5" :aria-invalid="Boolean(form.errors.admin_note)" :aria-describedby="form.errors.admin_note ? 'admin-note-error' : 'admin-note-help'" placeholder="Tuliskan alasan jika bukti tidak dapat diterima" class="mt-2 w-full resize-y rounded-xl border-slate-300 text-sm focus:border-aski-500 focus:ring-aski-500"></textarea>
                        </label>
                        <p id="admin-note-help" class="mt-1 text-xs text-slate-500">Catatan ini akan membantu member memahami keputusan penolakan.</p>
                        <p v-if="form.errors.admin_note" id="admin-note-error" class="mt-1.5 text-sm text-red-600">{{ form.errors.admin_note }}</p>
                        <div class="mt-5 grid gap-2 sm:grid-cols-2">
                            <button type="button" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-aski-700 px-4 text-sm font-semibold text-white hover:bg-aski-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700 disabled:cursor-not-allowed disabled:opacity-60" :disabled="form.processing" @click="decide('verified')">{{ form.processing ? 'Memproses…' : 'Terima Pembayaran' }}</button>
                            <button type="button" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-red-200 bg-white px-4 text-sm font-semibold text-red-700 hover:bg-red-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-700 disabled:cursor-not-allowed disabled:opacity-60" :disabled="form.processing" @click="decide('rejected')">Tolak Bukti</button>
                        </div>
                    </div>

                    <div v-else class="p-5 sm:p-6">
                        <div class="rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">Keputusan pembayaran sudah tercatat. Tidak ada tindakan verifikasi lanjutan yang tersedia.</div>
                    </div>
                </section>
            </div>
        </div>
    </AdminLayout>
</template>
