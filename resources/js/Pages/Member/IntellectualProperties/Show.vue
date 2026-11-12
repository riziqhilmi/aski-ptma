<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { formatDateOnly } from '@/utils/date.js';
import ConfirmActionDialog from '@/Components/ConfirmActionDialog.vue';

const props = defineProps({ record: Object });
const confirmingDelete = ref(false);
const deleteProcessing = ref(false);

const dateLabel = computed(() => props.record.ip_type === 'Hak Cipta'
    ? 'Tanggal diumumkan'
    : props.record.ip_type === 'Rahasia Dagang'
        ? 'Tanggal mulai pelindungan'
        : 'Tanggal penerimaan');

const creatorLabel = computed(() => ({
    Paten: 'Inventor',
    'Desain Industri': 'Pendesain',
}[props.record.ip_type] || 'Pencipta'));

const holderLabel = computed(() => ({
    Paten: 'Pemegang paten',
    Merek: 'Pemilik',
    'Rahasia Dagang': 'Pemilik',
}[props.record.ip_type] || 'Pemegang hak'));

const typeDetailLabels = {
    work_type: 'Jenis ciptaan',
    patent_type: 'Jenis paten',
    registration_number: 'Nomor pendaftaran merek',
    goods_services_class: 'Kelas barang/jasa',
    secrecy_status: 'Status kerahasiaan',
};

const typeDetails = computed(() => Object.entries(props.record.type_details || {})
    .filter(([key, value]) => typeDetailLabels[key] && value !== null && value !== '')
    .map(([key, value]) => ({
        label: typeDetailLabels[key],
        value: key === 'secrecy_status'
            ? (value === 'rahasia' ? 'Masih dijaga kerahasiaannya' : 'Tidak lagi rahasia')
            : value,
    })));

const destroyRecord = () => {
    confirmingDelete.value = true;
};
const confirmDelete = () => {
    if (deleteProcessing.value) return;
    deleteProcessing.value = true;
    router.delete(route('member.intellectual-properties.destroy', props.record.id), {
        onSuccess: () => { confirmingDelete.value = false; },
        onFinish: () => { deleteProcessing.value = false; },
    });
};
</script>

<template>
    <Head :title="`Detail HKI — ${record.title}`" />
    <MemberLayout>
        <div class="mx-auto max-w-6xl space-y-6">
            <header class="space-y-4">
                <Link :href="route('member.intellectual-properties.index')" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-aski-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.78 15.53a.75.75 0 0 1-1.06 0l-5-5a.75.75 0 0 1 0-1.06l5-5a.75.75 0 1 1 1.06 1.06L8.31 10l4.47 4.47a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" /></svg>
                    Kembali ke Data HKI Saya
                </Link>

                <div class="flex flex-col gap-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex min-w-0 items-start gap-4">
                        <span class="hidden h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-aski-50 text-aski-700 sm:flex">
                            <svg aria-hidden="true" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M8 4.75h6.5L19 9.25v9A1.75 1.75 0 0 1 17.25 20h-10A1.75 1.75 0 0 1 5.5 18.25v-11A2.5 2.5 0 0 1 8 4.75Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 5v4.5h4.5M8.5 13h7m-7 3h7"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-aski-700">Detail data HKI</p>
                            <h1 class="mt-1 break-words text-xl font-bold tracking-tight text-aski-900 sm:text-2xl">{{ record.title }}</h1>
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded-full border border-aski-100 bg-aski-50 px-3 py-1 text-xs font-semibold text-aski-800">{{ record.ip_type }}</span>
                                <span v-if="record.application_number" class="text-xs text-slate-500">Nomor permohonan: <span class="font-medium text-slate-700">{{ record.application_number }}</span></span>
                            </div>
                        </div>
                    </div>
                    <div class="flex w-full flex-wrap gap-2 border-t border-slate-100 pt-4 lg:w-auto lg:border-0 lg:pt-0">
                        <Link :href="route('member.intellectual-properties.edit', record.id)" class="inline-flex min-h-10 flex-1 items-center justify-center gap-2 rounded-lg bg-aski-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-aski-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700 sm:flex-none">
                            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="m13.7 3.3 3 3a1 1 0 0 1 0 1.4l-8.9 8.9-4.1.8.8-4.1 8.9-8.9a1 1 0 0 1 1.4 0ZM12.6 4.4 15.6 7.4"/></svg>
                            Ubah data
                        </Link>
                        <button type="button" class="inline-flex min-h-10 flex-1 items-center justify-center gap-2 rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-700 sm:flex-none" @click="destroyRecord">
                            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.5 2.75a1 1 0 0 0-1 1v.5h5v-.5a1 1 0 0 0-1-1h-3ZM6 4.25v-.5A2.5 2.5 0 0 1 8.5 1.25h3A2.5 2.5 0 0 1 14 3.75v.5h2.25a.75.75 0 0 1 0 1.5h-.56l-.63 9.4a2.5 2.5 0 0 1-2.5 2.35H7.44a2.5 2.5 0 0 1-2.5-2.35l-.63-9.4h-.56a.75.75 0 0 1 0-1.5H6Zm.44 1.5.62 9.3a1 1 0 0 0 1 .95h5.12a1 1 0 0 0 1-.95l.62-9.3H6.44Z" clip-rule="evenodd"/></svg>
                            Hapus
                        </button>
                    </div>
                </div>
            </header>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.5fr)_minmax(280px,0.8fr)]">
                <div class="space-y-6">
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="registration-heading">
                        <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                            <h2 id="registration-heading" class="text-base font-semibold text-aski-900">Informasi pendaftaran</h2>
                            <p class="mt-1 text-sm text-slate-500">Identitas utama dan tanggal pada dokumen HKI.</p>
                        </div>
                        <dl class="grid gap-px bg-slate-100 sm:grid-cols-2">
                            <div class="bg-white px-5 py-4 sm:px-6">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nomor permohonan</dt>
                                <dd class="mt-1.5 break-words text-sm font-semibold text-slate-900">{{ record.application_number || 'Belum dicantumkan' }}</dd>
                            </div>
                            <div class="bg-white px-5 py-4 sm:px-6">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ dateLabel }}</dt>
                                <dd class="mt-1.5 text-sm font-semibold text-slate-900">{{ formatDateOnly(record.application_date) }}</dd>
                            </div>
                            <div class="bg-white px-5 py-4 sm:px-6">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Jenis HKI</dt>
                                <dd class="mt-1.5 text-sm font-semibold text-slate-900">{{ record.ip_type }}</dd>
                            </div>
                            <div class="bg-white px-5 py-4 sm:px-6">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nomor pencatatan</dt>
                                <dd class="mt-1.5 break-words text-sm font-semibold text-slate-900">{{ record.recordation_number || 'Belum dicantumkan' }}</dd>
                            </div>
                            <div v-if="record.ip_type === 'Hak Cipta'" class="bg-white px-5 py-4 sm:col-span-2 sm:px-6">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tanggal pertama kali diumumkan</dt>
                                <dd class="mt-1.5 text-sm font-semibold text-slate-900">{{ formatDateOnly(record.first_published_at) }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="ownership-heading">
                        <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                            <h2 id="ownership-heading" class="text-base font-semibold text-aski-900">Pencipta dan kepemilikan</h2>
                            <p class="mt-1 text-sm text-slate-500">Pihak yang terlibat sesuai data yang dicatat.</p>
                        </div>
                        <dl class="grid gap-px bg-slate-100 sm:grid-cols-2">
                            <div v-if="record.ip_type !== 'Rahasia Dagang'" class="bg-white px-5 py-4 sm:px-6">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ creatorLabel }}</dt>
                                <dd class="mt-1.5 whitespace-pre-line break-words text-sm font-semibold text-slate-900">{{ record.creator || 'Belum dicantumkan' }}</dd>
                            </div>
                            <div class="bg-white px-5 py-4 sm:px-6" :class="record.ip_type === 'Rahasia Dagang' ? 'sm:col-span-2' : ''">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ holderLabel }}</dt>
                                <dd class="mt-1.5 whitespace-pre-line break-words text-sm font-semibold text-slate-900">{{ record.rights_holder || 'Belum dicantumkan' }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section v-if="typeDetails.length" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="type-detail-heading">
                        <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                            <h2 id="type-detail-heading" class="text-base font-semibold text-aski-900">Rincian jenis HKI</h2>
                            <p class="mt-1 text-sm text-slate-500">Informasi tambahan yang dicatat untuk jenis HKI ini.</p>
                        </div>
                        <dl class="grid gap-px bg-slate-100 sm:grid-cols-2">
                            <div v-for="detail in typeDetails" :key="detail.label" class="bg-white px-5 py-4 sm:px-6">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ detail.label }}</dt>
                                <dd class="mt-1.5 whitespace-pre-line break-words text-sm font-semibold text-slate-900">{{ detail.value }}</dd>
                            </div>
                        </dl>
                    </section>
                </div>

                <aside class="h-fit overflow-hidden rounded-2xl border border-aski-100 bg-white shadow-sm" aria-labelledby="protection-heading">
                    <div class="border-b border-aski-100 bg-aski-50/70 px-5 py-4 sm:px-6">
                        <h2 id="protection-heading" class="text-base font-semibold text-aski-900">Masa pelindungan</h2>
                        <p class="mt-1 text-sm text-slate-600">Mengikuti informasi pada dokumen yang Anda miliki.</p>
                    </div>
                    <div class="p-5 sm:p-6">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Masa pelindungan</p>
                        <p class="mt-2 break-words text-lg font-bold text-aski-900">{{ record.protection_period || 'Belum dicantumkan' }}</p>
                        <p class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs leading-5 text-slate-600">Informasi ini ditampilkan sesuai data yang diinput dan tidak dihitung otomatis.</p>
                    </div>
                </aside>
            </div>
        </div>
        <ConfirmActionDialog :show="confirmingDelete" title="Hapus data HKI?" description="Data HKI ini akan dihapus dari akun Anda dan tidak bisa dipulihkan." :item="record.title" action-label="Hapus data HKI" :processing="deleteProcessing" @close="confirmingDelete = false" @confirm="confirmDelete" />
    </MemberLayout>
</template>
