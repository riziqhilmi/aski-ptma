<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ setting: Object });
const form = useForm({
    bank_name: props.setting.bank_name || '',
    account_number: props.setting.account_number || '',
    account_holder: props.setting.account_holder || '',
});
</script>

<template>
    <Head title="Rekening Pembayaran" />
    <AdminLayout>
        <div class="mx-auto max-w-4xl">
            <header>
                <p class="text-sm font-semibold uppercase tracking-wide text-aski-700">Keuangan · Pengaturan</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">Rekening Pembayaran</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Atur informasi rekening yang digunakan anggota saat membayar tagihan. Pastikan data sesuai dengan rekening penerima.</p>
            </header>

            <form class="card mt-6 overflow-hidden" @submit.prevent="form.put(route('admin.payment-settings.update'))">
                <div class="border-b border-slate-100 px-5 py-4 sm:px-7">
                    <h2 class="font-semibold text-aski-900">Informasi Rekening</h2>
                    <p class="mt-1 text-sm text-slate-500">Semua kolom wajib diisi.</p>
                </div>

                <div class="space-y-5 px-5 py-6 sm:px-7">
                    <div>
                        <label for="payment-bank-name" class="text-sm font-semibold text-slate-800">Nama bank <span class="text-red-600">*</span></label>
                        <input id="payment-bank-name" v-model="form.bank_name" type="text" autocomplete="organization" maxlength="100" required aria-required="true" :aria-invalid="Boolean(form.errors.bank_name)" :aria-describedby="form.errors.bank_name ? 'payment-bank-name-error' : undefined" placeholder="Contoh: Bank Syariah Indonesia" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 text-sm focus:border-aski-500 focus:ring-aski-500" />
                        <p v-if="form.errors.bank_name" id="payment-bank-name-error" class="mt-1.5 text-sm text-red-600">{{ form.errors.bank_name }}</p>
                    </div>

                    <div>
                        <label for="payment-account-number" class="text-sm font-semibold text-slate-800">Nomor rekening <span class="text-red-600">*</span></label>
                        <input id="payment-account-number" v-model="form.account_number" type="text" inputmode="numeric" autocomplete="off" maxlength="50" required aria-required="true" :aria-invalid="Boolean(form.errors.account_number)" :aria-describedby="form.errors.account_number ? 'payment-account-number-error payment-account-number-help' : 'payment-account-number-help'" placeholder="Masukkan nomor rekening tujuan" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 text-sm focus:border-aski-500 focus:ring-aski-500" />
                        <p id="payment-account-number-help" class="mt-1.5 text-xs text-slate-500">Periksa kembali setiap digit sebelum menyimpan.</p>
                        <p v-if="form.errors.account_number" id="payment-account-number-error" class="mt-1.5 text-sm text-red-600">{{ form.errors.account_number }}</p>
                    </div>

                    <div>
                        <label for="payment-account-holder" class="text-sm font-semibold text-slate-800">Nama pemilik rekening <span class="text-red-600">*</span></label>
                        <input id="payment-account-holder" v-model="form.account_holder" type="text" autocomplete="name" maxlength="150" required aria-required="true" :aria-invalid="Boolean(form.errors.account_holder)" :aria-describedby="form.errors.account_holder ? 'payment-account-holder-error' : undefined" placeholder="Nama sesuai buku rekening" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 text-sm focus:border-aski-500 focus:ring-aski-500" />
                        <p v-if="form.errors.account_holder" id="payment-account-holder-error" class="mt-1.5 text-sm text-red-600">{{ form.errors.account_holder }}</p>
                    </div>
                </div>

                <div class="mx-5 mb-5 flex gap-3 rounded-xl border border-aski-100 bg-aski-50 p-4 text-sm leading-6 text-aski-900 sm:mx-7 sm:mb-6">
                    <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-aski-700" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9.25 8a.75.75 0 0 1 1.5 0v5a.75.75 0 0 1-1.5 0V8Zm.75-3a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z" clip-rule="evenodd" /></svg>
                    <p>Informasi rekening ini ditampilkan pada detail tagihan anggota. Pastikan rekening aktif dan dapat menerima transfer.</p>
                </div>

                <div class="flex justify-end border-t border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-7">
                    <button type="submit" class="btn-primary inline-flex min-h-11 w-full items-center justify-center sm:w-auto" :disabled="form.processing">{{ form.processing ? 'Menyimpan…' : 'Simpan Rekening' }}</button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
