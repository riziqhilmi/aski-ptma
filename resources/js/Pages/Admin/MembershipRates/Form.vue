<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ rate: Object });
const dateInputValue = (value) => value ? String(value).slice(0, 10) : '';
const formatRupiahInput = (value) => {
    const digits = String(value ?? '').split('.')[0].replace(/\D/g, '');
    return digits ? digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
};
const form = useForm({
    account_type: props.rate?.account_type || 'individual',
    name: props.rate?.name || '',
    amount: formatRupiahInput(props.rate?.amount ?? ''),
    duration_months: props.rate?.duration_months || 12,
    active_from: dateInputValue(props.rate?.active_from),
    active_until: dateInputValue(props.rate?.active_until),
});

const submit = () => {
    const payload = (data) => ({ ...data, amount: data.amount.replace(/\D/g, '') });

    if (props.rate) {
        form.transform(payload).put(route('admin.rates.update', props.rate.id));
        return;
    }

    form.transform(payload).post(route('admin.rates.store'));
};
</script>

<template>
    <Head :title="rate ? 'Ubah Tarif Anggota' : 'Tambah Tarif Anggota'" />
    <AdminLayout>
        <div class="mx-auto max-w-4xl">
            <Link :href="route('admin.rates.index')" class="inline-flex min-h-9 items-center rounded-lg text-sm font-semibold text-aski-700 hover:text-aski-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
                <svg aria-hidden="true" class="mr-2 h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.56l3.22 3.22a.75.75 0 1 1-1.06 1.06l-4.5-4.5a.75.75 0 0 1 0-1.06l4.5-4.5a.75.75 0 1 1 1.06 1.06l-3.22 3.22h10.69A.75.75 0 0 1 17 10Z" clip-rule="evenodd" /></svg>
                Kembali ke Tarif Anggota
            </Link>

            <header class="mt-5">
                <p class="text-sm font-semibold uppercase tracking-wide text-aski-700">Keuangan · Tarif Anggota</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">{{ rate ? 'Ubah Tarif' : 'Tambah Tarif' }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Atur tipe anggota, nominal, dan masa berlaku tarif. Perubahan hanya memengaruhi tagihan baru.</p>
            </header>

            <form class="card mt-6 overflow-hidden" @submit.prevent="submit">
                <div class="border-b border-slate-100 px-5 py-4 sm:px-7">
                    <h2 class="font-semibold text-aski-900">Informasi Tarif</h2>
                    <p class="mt-1 text-sm text-slate-500">Lengkapi seluruh informasi wajib sebelum menyimpan.</p>
                </div>

                <div class="grid gap-x-6 gap-y-5 px-5 py-6 sm:grid-cols-2 sm:px-7">
                    <div>
                        <label for="rate-account-type" class="text-sm font-semibold text-slate-800">Tipe akun <span class="text-red-600">*</span></label>
                        <select id="rate-account-type" v-model="form.account_type" required aria-required="true" :aria-invalid="Boolean(form.errors.account_type)" :aria-describedby="form.errors.account_type ? 'rate-account-type-error' : undefined" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 bg-white text-sm focus:border-aski-500 focus:ring-aski-500">
                            <option value="individual">Individu</option>
                            <option value="institution">Institusi</option>
                        </select>
                        <p v-if="form.errors.account_type" id="rate-account-type-error" class="mt-1.5 text-sm text-red-600">{{ form.errors.account_type }}</p>
                    </div>

                    <div>
                        <label for="rate-name" class="text-sm font-semibold text-slate-800">Nama tarif <span class="text-red-600">*</span></label>
                        <input id="rate-name" v-model="form.name" type="text" required maxlength="255" aria-required="true" :aria-invalid="Boolean(form.errors.name)" :aria-describedby="form.errors.name ? 'rate-name-error' : undefined" placeholder="Contoh: Keanggotaan Individu" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 text-sm focus:border-aski-500 focus:ring-aski-500" />
                        <p v-if="form.errors.name" id="rate-name-error" class="mt-1.5 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label for="rate-amount" class="text-sm font-semibold text-slate-800">Nominal tarif <span class="text-red-600">*</span></label>
                        <div class="mt-2 flex min-h-11 overflow-hidden rounded-xl border border-slate-300 focus-within:border-aski-500 focus-within:ring-1 focus-within:ring-aski-500">
                            <span class="inline-flex items-center border-r border-slate-200 bg-slate-50 px-3 text-sm font-medium text-slate-600">Rp</span>
                            <input id="rate-amount" :value="form.amount" type="text" inputmode="numeric" autocomplete="off" required aria-required="true" :aria-invalid="Boolean(form.errors.amount)" :aria-describedby="form.errors.amount ? 'rate-amount-error' : undefined" placeholder="0" class="min-w-0 flex-1 border-0 text-sm focus:ring-0" @input="form.amount = formatRupiahInput($event.target.value)" />
                        </div>
                        <p v-if="form.errors.amount" id="rate-amount-error" class="mt-1.5 text-sm text-red-600">{{ form.errors.amount }}</p>
                    </div>

                    <div>
                        <label for="rate-duration" class="text-sm font-semibold text-slate-800">Durasi keanggotaan <span class="text-red-600">*</span></label>
                        <div class="mt-2 flex min-h-11 overflow-hidden rounded-xl border border-slate-300 focus-within:border-aski-500 focus-within:ring-1 focus-within:ring-aski-500">
                            <input id="rate-duration" v-model="form.duration_months" type="number" min="1" max="120" step="1" required aria-required="true" :aria-invalid="Boolean(form.errors.duration_months)" :aria-describedby="form.errors.duration_months ? 'rate-duration-error rate-duration-help' : 'rate-duration-help'" class="min-w-0 flex-1 border-0 text-sm focus:ring-0" />
                            <span class="inline-flex items-center border-l border-slate-200 bg-slate-50 px-3 text-sm text-slate-600">bulan</span>
                        </div>
                        <p id="rate-duration-help" class="mt-1.5 text-xs leading-5 text-slate-500">Masa keanggotaan yang diperoleh setelah pembayaran diverifikasi.</p>
                        <p v-if="form.errors.duration_months" id="rate-duration-error" class="mt-1.5 text-sm text-red-600">{{ form.errors.duration_months }}</p>
                    </div>

                    <div>
                        <label for="rate-active-from" class="text-sm font-semibold text-slate-800">Mulai berlaku <span class="text-red-600">*</span></label>
                        <input id="rate-active-from" v-model="form.active_from" type="date" required aria-required="true" :aria-invalid="Boolean(form.errors.active_from)" :aria-describedby="form.errors.active_from ? 'rate-active-from-error' : undefined" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 text-sm focus:border-aski-500 focus:ring-aski-500" />
                        <p v-if="form.errors.active_from" id="rate-active-from-error" class="mt-1.5 text-sm text-red-600">{{ form.errors.active_from }}</p>
                    </div>

                    <div>
                        <label for="rate-active-until" class="text-sm font-semibold text-slate-800">Akhir berlaku <span class="font-normal text-slate-500">(opsional)</span></label>
                        <input id="rate-active-until" v-model="form.active_until" type="date" :aria-invalid="Boolean(form.errors.active_until)" :aria-describedby="form.errors.active_until ? 'rate-active-until-error' : undefined" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 text-sm focus:border-aski-500 focus:ring-aski-500" />
                        <p v-if="form.errors.active_until" id="rate-active-until-error" class="mt-1.5 text-sm text-red-600">{{ form.errors.active_until }}</p>
                    </div>
                </div>

                <div class="mx-5 mb-5 flex gap-3 rounded-xl border border-aski-100 bg-aski-50 p-4 text-sm leading-6 text-aski-900 sm:mx-7 sm:mb-6">
                    <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-aski-700" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9.25 8a.75.75 0 0 1 1.5 0v5a.75.75 0 0 1-1.5 0V8Zm.75-3a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z" clip-rule="evenodd" /></svg>
                    <p>Periode berlaku menentukan kapan tarif dapat dipilih untuk tagihan baru. Mengubah tarif tidak mengubah nominal pada invoice yang sudah diterbitkan.</p>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50/70 px-5 py-4 sm:flex-row sm:justify-end sm:px-7">
                    <Link :href="route('admin.rates.index')" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">Batal</Link>
                    <button type="submit" class="btn-primary inline-flex min-h-11 items-center justify-center" :disabled="form.processing">{{ form.processing ? 'Menyimpan…' : (rate ? 'Simpan Perubahan' : 'Simpan Tarif') }}</button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
