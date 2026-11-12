<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { formatDateOnly, formatDateTime } from '@/utils/date';

const props = defineProps({ account: Object });

const initials = computed(() => props.account.name
    .trim()
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part.charAt(0))
    .join('')
    .toUpperCase());

const applicationStatusLabels = {
    submitted: 'Diajukan',
    revision: 'Perlu revisi',
    approved_waiting_payment: 'Disetujui · menunggu pembayaran',
    rejected: 'Ditolak',
    completed: 'Selesai',
};

const membershipStatusLabels = {
    active: 'Aktif',
    expired: 'Kedaluwarsa',
    upcoming: 'Akan datang',
    inactive: 'Tidak aktif',
};
</script>

<template>
    <Head :title="`Detail Akun · ${account.name}`" />
    <AdminLayout>
        <div class="mx-auto w-full max-w-5xl">
            <Link
                :href="route('admin.accounts.index')"
                class="inline-flex min-h-10 items-center gap-2 rounded-lg px-2 text-sm font-semibold text-aski-700 transition hover:bg-aski-50 hover:text-aski-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700"
            >
                <span aria-hidden="true">←</span>
                Kembali ke Manajemen Akun
            </Link>

            <header class="mt-4 overflow-hidden rounded-2xl border border-aski-100 bg-white shadow-sm">
                <div class="h-1.5 bg-gold-400"></div>
                <div class="flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-7">
                    <div class="flex min-w-0 items-center gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-aski-50 text-lg font-bold text-aski-800 ring-1 ring-inset ring-aski-100 sm:h-16 sm:w-16 sm:text-xl">
                            {{ initials }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-aski-700">Manajemen Akun</p>
                            <h1 class="mt-1 break-words text-2xl font-bold leading-tight text-aski-900 sm:text-3xl">{{ account.name }}</h1>
                            <p class="mt-1 break-all text-sm text-slate-600">{{ account.email }}</p>
                        </div>
                    </div>
                    <StatusBadge
                        class="self-start sm:self-center"
                        :status="account.completion_status === 'lengkap' ? 'complete' : 'incomplete'"
                        :label="account.completion_status === 'lengkap' ? 'Profil lengkap' : 'Profil belum lengkap'"
                    />
                </div>
            </header>

            <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="account-profile-heading">
                <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4 sm:px-6">
                    <h2 id="account-profile-heading" class="text-lg font-bold text-aski-900">Profil akun</h2>
                    <p class="mt-1 text-sm text-slate-600">Informasi kontak dan identitas akun Member.</p>
                </div>
                <dl class="grid sm:grid-cols-2">
                    <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tipe akun</dt>
                        <dd class="mt-1 break-words text-sm font-medium text-slate-900">{{ account.profile?.account_type === 'institution' ? 'Institusi' : account.profile?.account_type === 'individual' ? 'Individu' : 'Belum dipilih' }}</dd>
                    </div>
                    <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nomor telepon</dt>
                        <dd class="mt-1 break-words text-sm font-medium text-slate-900">{{ account.profile?.phone || 'Belum diisi' }}</dd>
                    </div>
                    <div class="border-b border-slate-100 px-5 py-4 sm:col-span-2 sm:px-6">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Alamat</dt>
                        <dd class="mt-1 whitespace-pre-line break-words text-sm font-medium text-slate-900">{{ account.profile?.address || 'Belum diisi' }}</dd>
                    </div>
                    <template v-if="account.profile?.account_type === 'institution'">
                        <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nama institusi</dt>
                            <dd class="mt-1 break-words text-sm font-medium text-slate-900">{{ account.profile.institution_name || 'Belum diisi' }}</dd>
                        </div>
                        <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Penanggung jawab</dt>
                            <dd class="mt-1 break-words text-sm font-medium text-slate-900">{{ account.profile.responsible_name || 'Belum diisi' }}<span v-if="account.profile.responsible_position" class="font-normal text-slate-600"> · {{ account.profile.responsible_position }}</span></dd>
                        </div>
                    </template>
                </dl>
            </section>

            <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="account-memberships-heading">
                <div class="flex flex-col gap-1 border-b border-slate-100 bg-slate-50/80 px-5 py-4 sm:flex-row sm:items-end sm:justify-between sm:px-6">
                    <div>
                        <h2 id="account-memberships-heading" class="text-lg font-bold text-aski-900">Riwayat keanggotaan</h2>
                        <p class="mt-1 text-sm text-slate-600">Nomor dan periode keanggotaan yang tercatat untuk akun ini.</p>
                    </div>
                    <span class="text-sm font-medium text-slate-500">{{ account.memberships.length }} periode</span>
                </div>

                <div v-if="account.memberships.length" class="divide-y divide-slate-100">
                    <article v-for="membership in account.memberships" :key="membership.id" class="p-5 sm:p-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nomor anggota</p>
                                <p class="mt-1 break-all text-base font-bold text-aski-800">{{ membership.member_number || 'Belum tersedia' }}</p>
                            </div>
                            <StatusBadge :status="membership.display_status" :label="membershipStatusLabels[membership.display_status] || 'Perlu ditinjau'" />
                        </div>

                        <dl class="mt-4 grid gap-3 rounded-xl bg-aski-50/70 p-4 sm:grid-cols-2 sm:gap-5 sm:p-5">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Mulai</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-800">{{ formatDateOnly(membership.starts_at) }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Berakhir</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-800">{{ formatDateOnly(membership.ends_at) }}</dd>
                            </div>
                        </dl>

                        <div class="mt-4 flex flex-col gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-sm text-slate-600">
                                Tagihan:
                                <span v-if="membership.invoice" class="font-medium text-slate-800">{{ membership.invoice.number }} · {{ membership.invoice.status === 'paid' ? 'Dibayar' : 'Belum dibayar' }}</span>
                                <span v-else>Belum terhubung</span>
                            </p>
                            <Link v-if="membership.invoice" :href="route('admin.invoices.view', membership.invoice.id)" class="inline-flex min-h-10 items-center font-semibold text-aski-700 hover:text-aski-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
                                Lihat tagihan <span class="ml-1" aria-hidden="true">→</span>
                            </Link>
                        </div>
                    </article>
                </div>
                <p v-else class="px-5 py-8 text-center text-sm text-slate-600 sm:px-6">Belum ada periode keanggotaan yang tercatat.</p>
            </section>

            <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="account-applications-heading">
                <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4 sm:px-6">
                    <h2 id="account-applications-heading" class="text-lg font-bold text-aski-900">Riwayat permohonan</h2>
                    <p class="mt-1 text-sm text-slate-600">Perkembangan pengajuan dan tagihan keanggotaan.</p>
                </div>
                <div v-if="account.applications.length" class="divide-y divide-slate-100">
                    <article v-for="application in account.applications" :key="application.id" class="p-5 sm:p-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="font-semibold text-slate-900">{{ application.application_type === 'renewal' ? 'Perpanjangan keanggotaan' : 'Permohonan keanggotaan baru' }}</p>
                                <p class="mt-1 text-sm text-slate-500">Diajukan {{ formatDateTime(application.submitted_at) }}</p>
                            </div>
                            <StatusBadge :status="application.status" :label="applicationStatusLabels[application.status] || 'Status tidak dikenal'" />
                        </div>
                        <div class="mt-4 flex flex-wrap gap-x-5 gap-y-2 border-t border-slate-100 pt-4">
                            <Link :href="route('admin.applications.show', application.id)" class="inline-flex min-h-10 items-center font-semibold text-aski-700 hover:text-aski-900">Detail permohonan <span class="ml-1" aria-hidden="true">→</span></Link>
                            <Link v-if="application.invoice" :href="route('admin.invoices.view', application.invoice.id)" class="inline-flex min-h-10 items-center font-semibold text-aski-700 hover:text-aski-900">Tagihan {{ application.invoice.number }}</Link>
                        </div>
                        <p v-if="application.needs_status_review" class="mt-3 rounded-xl border border-gold-400/70 bg-gold-100/60 p-4 text-sm leading-relaxed text-aski-900">
                            Status memerlukan peninjauan: tagihan tercatat dibayar dan pembayaran terverifikasi, tetapi permohonan belum berstatus selesai. Data historis tidak diubah otomatis.
                        </p>
                    </article>
                </div>
                <p v-else class="px-5 py-8 text-center text-sm text-slate-600 sm:px-6">Belum ada permohonan keanggotaan.</p>
            </section>
        </div>
    </AdminLayout>
</template>
