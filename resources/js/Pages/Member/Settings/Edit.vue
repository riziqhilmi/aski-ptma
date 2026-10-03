<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';

const props = defineProps({ account: Object });
const form = useForm({ ...props.account.notification_preferences });

const options = [
    ['membership_updates', 'Update keanggotaan', 'Status permohonan, masa aktif, dan riwayat keanggotaan.'],
    ['invoice_updates', 'Update tagihan', 'Informasi tagihan baru dan perubahan status tagihan.'],
    ['payment_updates', 'Update pembayaran', 'Status bukti pembayaran yang dikirim ke Admin.'],
    ['document_updates', 'Update dokumen', 'Dokumen, pedoman, sertifikat, atau kartu yang tersedia.'],
    ['news_updates', 'Berita ASKI-PTMA', 'Informasi publikasi berita terbaru dari ASKI-PTMA.'],
];

const submit = () => form.put(route('member.settings.notifications.update'), { preserveScroll: true });
</script>

<template>
    <Head title="Pengaturan Akun" />
    <MemberLayout>
        <div>
            <p class="text-sm font-semibold text-aski-700">PENGATURAN</p>
            <h1 class="mt-1 text-3xl font-bold text-aski-900">Pengaturan Akun</h1>
        </div>

        <div class="mt-7 grid gap-6 lg:grid-cols-3">
            <section class="card p-7 lg:col-span-1">
                <h2 class="text-xl font-bold text-aski-900">Data Akun</h2>
                <dl class="mt-5 space-y-4 text-sm">
                    <div>
                        <dt class="font-semibold text-slate-500">Nama</dt>
                        <dd class="mt-1 text-aski-900">{{ account.name }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Email</dt>
                        <dd class="mt-1 text-aski-900">{{ account.email }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Tipe akun</dt>
                        <dd class="mt-1 text-aski-900">
                            {{ account.profile?.account_type === 'institution' ? 'Institusi' : account.profile?.account_type === 'individual' ? 'Individu' : 'Belum dipilih' }}
                        </dd>
                    </div>
                </dl>
                <div class="mt-6 flex flex-col gap-3">
                    <Link :href="route('member.profile.edit')" class="btn-primary text-center">Lengkapi Profil Member</Link>
                    <Link :href="route('profile.edit')" class="rounded-lg border border-aski-200 px-4 py-2 text-center text-sm font-semibold text-aski-700 hover:bg-aski-50">Ubah Data Akun & Password</Link>
                </div>
            </section>

            <form class="card p-7 lg:col-span-2" @submit.prevent="submit">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-bold text-aski-900">Pengaturan Notifikasi</h2>
                        <p class="mt-1 text-sm text-slate-500">Pilih informasi yang ingin Anda terima dari sistem.</p>
                    </div>
                    <button class="btn-primary">Simpan</button>
                </div>

                <div class="mt-6 divide-y divide-slate-100">
                    <label v-for="[key, title, description] in options" :key="key" class="flex cursor-pointer items-start justify-between gap-5 py-4">
                        <span>
                            <span class="block font-semibold text-aski-900">{{ title }}</span>
                            <span class="mt-1 block text-sm text-slate-500">{{ description }}</span>
                            <span class="text-xs text-red-600">{{ form.errors[key] }}</span>
                        </span>
                        <input v-model="form[key]" type="checkbox" class="mt-1 rounded border-slate-300 text-aski-700 focus:ring-aski-500" />
                    </label>
                </div>
                <p v-if="form.recentlySuccessful" class="mt-4 rounded-lg bg-aski-50 px-4 py-3 text-sm font-semibold text-aski-800">Pengaturan berhasil disimpan.</p>
            </form>
        </div>
    </MemberLayout>
</template>
