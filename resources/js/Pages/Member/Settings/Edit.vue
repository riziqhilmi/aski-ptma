<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';

const props = defineProps({ account: Object });
const form = useForm({ ...props.account.notification_preferences });

const options = [
    ['membership_updates', 'Update keanggotaan', 'Status permohonan, masa aktif, dan riwayat keanggotaan.'],
    ['invoice_updates', 'Update tagihan', 'Informasi tagihan baru dan perubahan status tagihan.'],
    ['payment_updates', 'Update pembayaran', 'Status bukti pembayaran yang dikirim ke Admin.'],
    ['document_updates', 'Update dokumen', 'Dokumen, pedoman, sertifikat, atau kartu yang tersedia.'],
    ['news_updates', 'Berita ASKI-PTMA', 'Informasi publikasi berita terbaru dari ASKI-PTMA.'],
];

const accountType = (type) => type === 'institution' ? 'Institusi' : type === 'individual' ? 'Individu' : 'Belum dipilih';
const submit = () => form.put(route('member.settings.notifications.update'), { preserveScroll: true });
</script>

<template>
    <Head title="Pengaturan Akun" />
    <MemberLayout>
        <div class="mx-auto max-w-5xl space-y-6">
            <header>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-aski-700">Pengaturan Member</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-aski-900 sm:text-3xl">Pengaturan Akun</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Kelola informasi akun, preferensi pemberitahuan, dan keamanan password Anda.</p>
            </header>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="account-summary-heading">
                <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-aski-50 text-sm font-bold text-aski-800" aria-hidden="true">{{ account.name?.slice(0, 1)?.toUpperCase() || 'A' }}</span>
                        <div class="min-w-0">
                            <h2 id="account-summary-heading" class="truncate text-base font-semibold text-aski-900">{{ account.name }}</h2>
                            <p class="truncate text-sm text-slate-500">{{ account.email }}</p>
                        </div>
                    </div>
                    <Link :href="route('member.profile.edit')" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-aski-200 px-4 py-2 text-sm font-semibold text-aski-800 transition hover:bg-aski-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">{{ account.profile?.completion_status === 'lengkap' ? 'Ubah profil Member' : 'Lengkapi profil Member' }}</Link>
                </div>
                <dl class="grid gap-px bg-slate-100 sm:grid-cols-2">
                    <div class="bg-white px-5 py-4 sm:px-6">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tipe akun</dt>
                        <dd class="mt-1.5 text-sm font-semibold text-slate-800">{{ accountType(account.profile?.account_type) }}</dd>
                    </div>
                    <div class="bg-white px-5 py-4 sm:px-6">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status profil</dt>
                        <dd class="mt-1.5 text-sm font-semibold" :class="account.profile?.completion_status === 'lengkap' ? 'text-aski-800' : 'text-amber-700'">{{ account.profile?.completion_status === 'lengkap' ? 'Lengkap' : 'Belum lengkap' }}</dd>
                    </div>
                </dl>
            </section>

            <form class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" @submit.prevent="submit">
                <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <h2 class="text-lg font-bold text-aski-900">Preferensi notifikasi</h2>
                        <p class="mt-1 text-sm text-slate-500">Atur pilihan kategori pemberitahuan yang ingin Anda terima.</p>
                    </div>
                    <button type="submit" class="btn-primary inline-flex min-h-10 items-center justify-center" :disabled="form.processing">{{ form.processing ? 'Menyimpan…' : 'Simpan pilihan' }}</button>
                </div>

                <div class="mx-5 mt-5 flex gap-3 rounded-xl border border-gold-400 bg-gold-100 p-4 text-sm text-aski-900 sm:mx-6" role="note">
                    <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-aski-800" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a.75.75 0 0 1 .75.75v6.5a.75.75 0 0 1-1.5 0v-6.5A.75.75 0 0 1 10 2Zm0 11.5a1 1 0 1 0 0 2 1 1 0 0 0 0-2ZM18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z" clip-rule="evenodd"/></svg>
                    <p><span class="font-semibold">Catatan:</span> pilihan ini tersimpan sebagai preferensi akun. Pengiriman notifikasi otomatis untuk kategori di bawah belum aktif, sehingga mengubah pilihan belum mengubah pesan yang dikirim sistem.</p>
                </div>

                <div class="divide-y divide-slate-100 px-5 sm:px-6">
                    <label v-for="[key, title, description] in options" :key="key" class="flex cursor-pointer items-start justify-between gap-5 py-4">
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-slate-900">{{ title }}</span>
                            <span class="mt-1 block text-sm leading-5 text-slate-500">{{ description }}</span>
                            <span v-if="form.errors[key]" class="mt-1.5 block text-xs text-red-600">{{ form.errors[key] }}</span>
                        </span>
                        <input v-model="form[key]" type="checkbox" :aria-label="title" class="mt-1 h-5 w-5 shrink-0 rounded border-slate-300 text-aski-700 focus:ring-aski-500" />
                    </label>
                </div>
                <p v-if="form.recentlySuccessful" role="status" class="mx-5 mb-5 rounded-lg bg-aski-50 px-4 py-3 text-sm font-semibold text-aski-800 sm:mx-6">Preferensi berhasil disimpan.</p>
                <div class="flex justify-end border-t border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                    <button type="submit" class="btn-primary inline-flex min-h-10 items-center justify-center" :disabled="form.processing">{{ form.processing ? 'Menyimpan…' : 'Simpan pilihan' }}</button>
                </div>
            </form>

            <UpdatePasswordForm />
        </div>
    </MemberLayout>
</template>
