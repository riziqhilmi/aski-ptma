<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { formatDateTime, formatDateOnly } from '@/utils/date.js';
import StatusBadge from '@/Components/StatusBadge.vue';
import PaginationLinks from '@/Components/PaginationLinks.vue';

const props = defineProps({ profileComplete: Boolean, canSubmitApplication: Boolean, hasPendingApplication: Boolean, resubmittingRevision: Boolean, renewalEligible: Boolean, renewalOpensAt: String, membershipEndsAt: String, applications: Object, statusLabels: Object, revisionApplication: Object, revisionProfile: Object });
const submitting = ref(false);
const showSubmitConfirmation = ref(false);
const applicationActionLabel = computed(() => props.resubmittingRevision
    ? 'Kirim Ulang Permohonan'
    : props.membershipEndsAt
        ? 'Ajukan Perpanjangan'
        : 'Ajukan Keanggotaan');
const revisionFields = ['name', 'email', 'account_type', 'phone', 'address', 'institution_name', 'responsible_name', 'responsible_position'];
const labels = { name: 'Nama', email: 'Email', account_type: 'Jenis akun', phone: 'Nomor telepon', address: 'Alamat', institution_name: 'Nama institusi', responsible_name: 'Nama penanggung jawab', responsible_position: 'Jabatan penanggung jawab' };
const revisionForm = useForm({});
revisionFields.forEach((field) => { revisionForm[field] = props.revisionProfile?.[field] ?? ''; });
const requestedFields = computed(() => {
    const requested = props.revisionApplication?.histories?.find((history) => history.status === 'revision')?.revision_data?.requested_fields ?? revisionFields;
    const changingToInstitution = requested.includes('account_type')
        && revisionForm.account_type === 'institution'
        && props.revisionProfile?.account_type !== 'institution';
    return changingToInstitution ? [...new Set([...requested, 'institution_name', 'responsible_name'])] : requested;
});
const submitRevision = () => revisionForm.put(route('member.applications.revision.update', props.revisionApplication.id));
const submit = () => {
    if (submitting.value) return;
    submitting.value = true;
    router.post(route('member.applications.store'), {}, { onFinish: () => { submitting.value = false; showSubmitConfirmation.value = false; } });
};
const timelineHistories = (histories) => [...histories].reverse();
</script>

<template>
    <Head title="Permohonan & Perpanjangan" />
    <MemberLayout>
        <header class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-aski-700">Keanggotaan</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">Permohonan &amp; Perpanjangan</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Ajukan keanggotaan, perpanjang masa aktif, atau ikuti perkembangan permohonan Anda.</p>
            </div>
            <div v-if="canSubmitApplication" class="w-full rounded-2xl border border-aski-100 bg-white p-4 shadow-sm sm:w-72">
                <p class="text-xs leading-5 text-slate-600">{{ resubmittingRevision ? 'Data revisi siap dikirim kembali ke Admin.' : membershipEndsAt ? 'Masa keanggotaan Anda memenuhi jadwal perpanjangan.' : 'Mulai proses keanggotaan ASKI-PTMA.' }}</p>
                <button type="button" class="btn-primary mt-3 inline-flex min-h-11 w-full items-center justify-center gap-2" @click="showSubmitConfirmation = true">
                    <span>{{ applicationActionLabel }}</span>
                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69l-3.22-3.22a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 1 1-1.06-1.06l3.22-3.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                </button>
            </div>
        </header>

        <div v-if="!profileComplete" role="status" class="mt-6 flex gap-3 rounded-2xl border border-gold-400 bg-gold-100 p-4 sm:p-5">
            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-aski-800" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9.25 8a.75.75 0 0 1 1.5 0v5a.75.75 0 0 1-1.5 0V8Zm.75-3a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z" clip-rule="evenodd"/></svg>
            <div><p class="font-semibold text-aski-900">Lengkapi profil terlebih dahulu</p><p class="mt-1 text-sm leading-6 text-aski-900">Profil Anda perlu lengkap sebelum permohonan dapat diajukan.</p><Link :href="route('member.profile.edit')" class="mt-2 inline-flex min-h-9 items-center font-semibold text-aski-800 underline underline-offset-2">Lengkapi Profil</Link></div>
        </div>
        <div v-else-if="hasPendingApplication" role="status" class="mt-6 flex gap-3 rounded-2xl border border-aski-200 bg-aski-50 p-4 sm:p-5">
            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-aski-700" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm.75 4a.75.75 0 0 0-1.5 0v4.5c0 .414.336.75.75.75h3a.75.75 0 0 0 0-1.5h-2.25V6Z" clip-rule="evenodd"/></svg>
            <div><p class="font-semibold text-aski-900">Permohonan sedang diproses</p><p class="mt-1 text-sm leading-6 text-slate-700">Permohonan Anda masih diproses atau menunggu pembayaran. Pengajuan berikutnya tersedia setelah tahap ini selesai.</p></div>
        </div>
        <div v-else-if="membershipEndsAt && !renewalEligible" role="status" class="mt-6 flex gap-3 rounded-2xl border border-aski-200 bg-aski-50 p-4 sm:p-5">
            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-aski-700" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm.75 4a.75.75 0 0 0-1.5 0v4.5c0 .414.336.75.75.75h3a.75.75 0 0 0 0-1.5h-2.25V6Z" clip-rule="evenodd"/></svg>
            <div><p class="font-semibold text-aski-900">Jadwal perpanjangan belum dibuka</p><p class="mt-1 text-sm leading-6 text-slate-700">Perpanjangan dapat diajukan mulai <strong>{{ formatDateOnly(renewalOpensAt) }}</strong>, yaitu 30 hari kalender sebelum masa keanggotaan berakhir pada <strong>{{ formatDateOnly(membershipEndsAt) }}</strong>.</p></div>
        </div>

        <section v-if="revisionApplication" class="card mt-6 max-w-5xl overflow-hidden" aria-labelledby="revision-title">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-7">
                <p class="text-xs font-semibold uppercase tracking-wide text-aski-700">Tindak Lanjut Permohonan</p>
                <h2 id="revision-title" class="mt-1 text-xl font-bold text-aski-900">Perbaiki Data yang Diminta</h2>
                <p class="mt-1 text-sm text-slate-600">Perbarui kolom berikut, kemudian kirim kembali untuk ditinjau Admin.</p>
            </div>
            <form class="p-5 sm:p-7" @submit.prevent="submitRevision">
                <div v-if="revisionApplication.admin_note" class="mb-5 rounded-xl border border-gold-300 bg-gold-50 p-4 text-sm leading-6 text-slate-700"><p class="font-semibold text-aski-900">Catatan Admin</p><p class="mt-1 whitespace-pre-line">{{ revisionApplication.admin_note }}</p></div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div v-for="field in requestedFields" :key="field" class="min-w-0">
                        <label :for="`revision-${field}`" class="text-sm font-semibold text-slate-800">{{ labels[field] }}<span v-if="field === 'institution_name' || field === 'responsible_name'" class="ml-1 text-xs font-normal text-slate-500">(wajib untuk akun institusi)</span></label>
                        <select v-if="field === 'account_type'" :id="`revision-${field}`" v-model="revisionForm[field]" :aria-invalid="Boolean(revisionForm.errors[field])" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500"><option value="individual">Individu</option><option value="institution">Institusi</option></select>
                        <textarea v-else-if="field === 'address'" :id="`revision-${field}`" v-model="revisionForm[field]" rows="3" :aria-invalid="Boolean(revisionForm.errors[field])" class="mt-2 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500"></textarea>
                        <input v-else :id="`revision-${field}`" v-model="revisionForm[field]" :type="field === 'email' ? 'email' : 'text'" :aria-invalid="Boolean(revisionForm.errors[field])" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500" />
                        <p v-if="revisionForm.errors[field]" class="mt-1.5 text-sm text-red-600">{{ revisionForm.errors[field] }}</p>
                    </div>
                </div>
                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="submit" class="btn-primary inline-flex min-h-11 items-center justify-center" :disabled="revisionForm.processing">{{ revisionForm.processing ? 'Mengirim…' : 'Simpan dan Ajukan Ulang' }}</button>
                </div>
            </form>
        </section>

        <section class="mt-7" aria-labelledby="application-history-title">
            <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div><h2 id="application-history-title" class="text-xl font-bold text-aski-900">Riwayat Permohonan</h2><p class="mt-1 text-sm text-slate-600">Pantau status dan catatan pada setiap tahap.</p></div>
                <p class="text-xs text-slate-500">{{ applications.total }} permohonan</p>
            </div>
            <div class="space-y-4">
                <article v-for="application in applications.data" :key="application.id" class="card overflow-hidden">
                    <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div><h3 class="font-semibold text-slate-900">Permohonan #{{ application.id }}</h3><p class="mt-1 text-xs text-slate-500">Diajukan {{ formatDateTime(application.submitted_at) }}</p></div>
                        <StatusBadge :status="application.status" :label="statusLabels[application.status] || 'Status tidak dikenal'" />
                    </div>
                    <div class="p-5 sm:p-6">
                        <div v-if="application.admin_note" class="mb-5 rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-700"><span class="font-semibold text-slate-900">Catatan Admin:</span> {{ application.admin_note }}</div>
                        <div v-if="application.needs_status_review" role="status" class="mb-5 rounded-xl border border-gold-400 bg-gold-100 p-4 text-sm leading-6 text-aski-900">
                            <p>Pembayaran tagihan sudah terverifikasi, tetapi status permohonan belum diperbarui. Masa keanggotaan Anda tetap mengikuti status keanggotaan yang tercantum.</p>
                            <Link v-if="application.invoice" :href="route('member.invoices.show', application.invoice.id)" class="mt-2 inline-flex font-semibold underline underline-offset-2">Lihat tagihan terkait</Link>
                        </div>
                        <div>
                            <p class="mb-4 text-xs font-semibold uppercase tracking-wide text-slate-500">Tahapan Status</p>
                            <ol v-if="application.histories.length" class="ml-1">
                                <li v-for="(history, index) in timelineHistories(application.histories)" :key="history.id" class="relative flex gap-4 pb-5 last:pb-0">
                                    <span v-if="index < application.histories.length - 1" aria-hidden="true" class="absolute left-[7px] top-4 h-[calc(100%-0.75rem)] w-px bg-slate-200"></span>
                                    <span aria-hidden="true" class="relative z-10 mt-1 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2 border-aski-600 bg-white"><span class="h-1.5 w-1.5 rounded-full bg-aski-600"></span></span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between sm:gap-4"><p class="text-sm font-semibold text-slate-900">{{ statusLabels[history.status] ?? 'Status tidak dikenal' }}</p><time class="text-xs text-slate-500">{{ formatDateTime(history.created_at) }}</time></div>
                                        <p v-if="history.note" class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-600">{{ history.note }}</p>
                                    </div>
                                </li>
                            </ol>
                            <p v-else class="text-sm text-slate-500">Belum ada pembaruan status.</p>
                        </div>
                    </div>
                </article>

                <div v-if="applications.data.length === 0" class="card px-5 py-10 text-center sm:py-12">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-aski-50 text-aski-700"><svg aria-hidden="true" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16ZM6.5 9.25a.75.75 0 0 1 .75.75v.5a2.75 2.75 0 0 0 5.5 0V10a.75.75 0 0 1 1.5 0v.5a4.25 4.25 0 0 1-8.5 0V10a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/></svg></div>
                    <h3 class="mt-3 font-semibold text-slate-900">Belum ada riwayat permohonan</h3>
                    <p class="mt-1 text-sm text-slate-500">Permohonan yang Anda ajukan akan tercatat di sini.</p>
                </div>
            </div>
            <PaginationLinks :links="applications.links" />
        </section>

        <Teleport to="body">
            <div v-if="showSubmitConfirmation" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4" role="presentation" @click.self="showSubmitConfirmation = false">
                <section role="dialog" aria-modal="true" aria-labelledby="submit-confirm-title" aria-describedby="submit-confirm-description" class="w-full max-w-md rounded-2xl bg-white p-5 shadow-xl sm:p-6" @keydown.esc.prevent="showSubmitConfirmation = false">
                    <p class="text-xs font-semibold uppercase tracking-wide text-aski-700">Konfirmasi</p>
                    <h2 id="submit-confirm-title" class="mt-1 text-xl font-bold text-aski-900">{{ applicationActionLabel }}?</h2>
                    <p id="submit-confirm-description" class="mt-2 text-sm leading-6 text-slate-600">Permohonan akan dikirim ke Admin untuk ditinjau. Pastikan informasi profil Anda sudah benar.</p>
                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-5 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="submitting" @click="showSubmitConfirmation = false">Batal</button>
                        <button type="button" class="btn-primary inline-flex min-h-11 items-center justify-center" :disabled="submitting" @click="submit">{{ submitting ? 'Mengirim…' : 'Ya, Kirim Permohonan' }}</button>
                    </div>
                </section>
            </div>
        </Teleport>
    </MemberLayout>
</template>
