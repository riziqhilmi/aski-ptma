<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({ user: Object, profile: Object });
const form = useForm({
    name: props.user.name,
    email: props.user.email,
    account_type: props.profile.account_type || '',
    phone: props.profile.phone || '',
    address: props.profile.address || '',
    institution_name: props.profile.institution_name || '',
    responsible_name: props.profile.responsible_name || '',
    responsible_position: props.profile.responsible_position || '',
});
const submit = () => form.put(route('member.profile.update'));
</script>

<template>
    <Head title="Profil Saya" />
    <MemberLayout>
        <div class="mx-auto max-w-5xl space-y-6">
            <header class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-7">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-aski-700">Pengaturan akun</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-aski-900 sm:text-3xl">Profil Saya</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Pastikan informasi akun dan kontak Anda selalu terbaru.</p>
                </div>
                <StatusBadge :status="profile.completion_status === 'lengkap' ? 'complete' : 'incomplete'" :label="profile.completion_status === 'lengkap' ? 'Profil lengkap' : 'Profil belum lengkap'" />
            </header>

            <form class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" @submit.prevent="submit">
                <section class="border-b border-slate-100 p-5 sm:p-7" aria-labelledby="account-identity-heading">
                    <div class="mb-5">
                        <h2 id="account-identity-heading" class="text-base font-semibold text-aski-900">Informasi akun</h2>
                        <p class="mt-1 text-sm text-slate-500">Nama dan alamat email yang digunakan pada akun Anda.</p>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="profile-name" class="block text-sm font-semibold text-slate-800">Nama <span class="text-red-600" aria-hidden="true">*</span></label>
                            <input id="profile-name" v-model="form.name" required aria-required="true" :aria-invalid="Boolean(form.errors.name)" :aria-describedby="form.errors.name ? 'profile-name-error' : undefined" autocomplete="name" class="mt-1.5 min-h-11 w-full rounded-lg border-slate-300 text-sm focus:border-aski-500 focus:ring-aski-500" />
                            <p v-if="form.errors.name" id="profile-name-error" class="mt-1.5 text-xs text-red-600">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label for="profile-email" class="block text-sm font-semibold text-slate-800">Email <span class="text-red-600" aria-hidden="true">*</span></label>
                            <input id="profile-email" v-model="form.email" type="email" required aria-required="true" :aria-invalid="Boolean(form.errors.email)" :aria-describedby="form.errors.email ? 'profile-email-error' : undefined" autocomplete="email" class="mt-1.5 min-h-11 w-full rounded-lg border-slate-300 text-sm focus:border-aski-500 focus:ring-aski-500" />
                            <p v-if="form.errors.email" id="profile-email-error" class="mt-1.5 text-xs text-red-600">{{ form.errors.email }}</p>
                        </div>
                    </div>
                </section>

                <section class="border-b border-slate-100 p-5 sm:p-7" aria-labelledby="account-type-heading">
                    <div class="mb-4">
                        <h2 id="account-type-heading" class="text-base font-semibold text-aski-900">Tipe akun</h2>
                        <p class="mt-1 text-sm text-slate-500">Pilih jenis akun yang sesuai dengan keanggotaan Anda.</p>
                    </div>
                    <fieldset :aria-invalid="Boolean(form.errors.account_type)" :aria-describedby="form.errors.account_type ? 'account-type-error' : undefined">
                        <legend class="sr-only">Pilih tipe akun</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="flex min-h-14 cursor-pointer items-center gap-3 rounded-xl border p-4 transition focus-within:ring-2 focus-within:ring-aski-500" :class="form.account_type === 'individual' ? 'border-aski-600 bg-aski-50 text-aski-900' : 'border-slate-200 hover:border-slate-300'">
                                <input v-model="form.account_type" value="individual" type="radio" name="account_type" required aria-required="true" class="text-aski-700 focus:ring-aski-500" />
                                <span><span class="block text-sm font-semibold">Individu</span><span class="mt-0.5 block text-xs text-slate-500">Keanggotaan atas nama perorangan</span></span>
                            </label>
                            <label class="flex min-h-14 cursor-pointer items-center gap-3 rounded-xl border p-4 transition focus-within:ring-2 focus-within:ring-aski-500" :class="form.account_type === 'institution' ? 'border-aski-600 bg-aski-50 text-aski-900' : 'border-slate-200 hover:border-slate-300'">
                                <input v-model="form.account_type" value="institution" type="radio" name="account_type" required aria-required="true" class="text-aski-700 focus:ring-aski-500" />
                                <span><span class="block text-sm font-semibold">Institusi</span><span class="mt-0.5 block text-xs text-slate-500">Keanggotaan atas nama lembaga</span></span>
                            </label>
                        </div>
                        <p v-if="form.errors.account_type" id="account-type-error" class="mt-2 text-xs text-red-600">{{ form.errors.account_type }}</p>
                    </fieldset>

                    <div v-if="form.account_type === 'institution'" class="mt-5 grid gap-5 rounded-xl bg-slate-50 p-4 sm:grid-cols-2 sm:p-5">
                        <div>
                            <label for="institution-name" class="block text-sm font-semibold text-slate-800">Nama institusi <span class="text-red-600" aria-hidden="true">*</span></label>
                            <input id="institution-name" v-model="form.institution_name" required aria-required="true" :aria-invalid="Boolean(form.errors.institution_name)" :aria-describedby="form.errors.institution_name ? 'institution-name-error' : undefined" autocomplete="organization" class="mt-1.5 min-h-11 w-full rounded-lg border-slate-300 bg-white text-sm focus:border-aski-500 focus:ring-aski-500" />
                            <p v-if="form.errors.institution_name" id="institution-name-error" class="mt-1.5 text-xs text-red-600">{{ form.errors.institution_name }}</p>
                        </div>
                        <div>
                            <label for="responsible-name" class="block text-sm font-semibold text-slate-800">Nama penanggung jawab <span class="text-red-600" aria-hidden="true">*</span></label>
                            <input id="responsible-name" v-model="form.responsible_name" required aria-required="true" :aria-invalid="Boolean(form.errors.responsible_name)" :aria-describedby="form.errors.responsible_name ? 'responsible-name-error' : undefined" autocomplete="name" class="mt-1.5 min-h-11 w-full rounded-lg border-slate-300 bg-white text-sm focus:border-aski-500 focus:ring-aski-500" />
                            <p v-if="form.errors.responsible_name" id="responsible-name-error" class="mt-1.5 text-xs text-red-600">{{ form.errors.responsible_name }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="responsible-position" class="block text-sm font-semibold text-slate-800">Jabatan penanggung jawab <span class="font-normal text-slate-500">(opsional)</span></label>
                            <input id="responsible-position" v-model="form.responsible_position" :aria-invalid="Boolean(form.errors.responsible_position)" :aria-describedby="form.errors.responsible_position ? 'responsible-position-error' : undefined" autocomplete="organization-title" class="mt-1.5 min-h-11 w-full rounded-lg border-slate-300 bg-white text-sm focus:border-aski-500 focus:ring-aski-500" />
                            <p v-if="form.errors.responsible_position" id="responsible-position-error" class="mt-1.5 text-xs text-red-600">{{ form.errors.responsible_position }}</p>
                        </div>
                    </div>
                </section>

                <section class="p-5 sm:p-7" aria-labelledby="account-contact-heading">
                    <div class="mb-5">
                        <h2 id="account-contact-heading" class="text-base font-semibold text-aski-900">Informasi kontak</h2>
                        <p class="mt-1 text-sm text-slate-500">Informasi ini membantu Admin menghubungi Anda jika diperlukan.</p>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="profile-phone" class="block text-sm font-semibold text-slate-800">Nomor telepon <span class="text-red-600" aria-hidden="true">*</span></label>
                            <input id="profile-phone" v-model="form.phone" type="tel" required aria-required="true" :aria-invalid="Boolean(form.errors.phone)" :aria-describedby="form.errors.phone ? 'profile-phone-error' : undefined" autocomplete="tel" class="mt-1.5 min-h-11 w-full rounded-lg border-slate-300 text-sm focus:border-aski-500 focus:ring-aski-500" />
                            <p v-if="form.errors.phone" id="profile-phone-error" class="mt-1.5 text-xs text-red-600">{{ form.errors.phone }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="profile-address" class="block text-sm font-semibold text-slate-800">Alamat <span class="text-red-600" aria-hidden="true">*</span></label>
                            <textarea id="profile-address" v-model="form.address" required aria-required="true" :aria-invalid="Boolean(form.errors.address)" :aria-describedby="form.errors.address ? 'profile-address-error' : undefined" autocomplete="street-address" rows="4" class="mt-1.5 w-full resize-y rounded-lg border-slate-300 text-sm leading-6 focus:border-aski-500 focus:ring-aski-500"></textarea>
                            <p v-if="form.errors.address" id="profile-address-error" class="mt-1.5 text-xs text-red-600">{{ form.errors.address }}</p>
                        </div>
                    </div>
                </section>

                <footer class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                    <p class="text-xs text-slate-500"><span class="font-bold text-red-600">*</span> Kolom wajib diisi.</p>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <p v-if="form.recentlySuccessful" role="status" class="text-sm font-medium text-aski-800">Profil berhasil disimpan.</p>
                        <button type="submit" class="btn-primary inline-flex min-h-11 items-center justify-center gap-2" :disabled="form.processing">
                            <svg v-if="!form.processing" aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M3.25 3.5A1.25 1.25 0 0 1 4.5 2.25h8.19c.33 0 .65.13.88.37l3.81 3.81c.23.23.37.55.37.88v9.19a1.25 1.25 0 0 1-1.25 1.25h-12a1.25 1.25 0 0 1-1.25-1.25v-13Zm3.5.25v4h6.5v-4h-6.5Zm0 7a.75.75 0 0 0-.75.75v4.5h8v-4.5a.75.75 0 0 0-.75-.75h-6.5Z"/></svg>
                            {{ form.processing ? 'Menyimpan…' : 'Simpan profil' }}
                        </button>
                    </div>
                </footer>
            </form>
        </div>
    </MemberLayout>
</template>
