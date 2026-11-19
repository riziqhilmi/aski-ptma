<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { formatDateOnly } from '@/utils/date.js';
import PaginationLinks from '@/Components/PaginationLinks.vue';
import Modal from '@/Components/Modal.vue';
import { ref } from 'vue';

defineProps({ records: Object, canCreate: Boolean });

const importForm = useForm({ file: null });
const importInput = ref(null);
const importFileName = ref('');
const showImportGuide = ref(false);

const chooseImportFile = () => importInput.value?.click();
const openImportGuide = () => { showImportGuide.value = true; };
const onImportFileSelected = (event) => {
    importForm.file = event.target.files?.[0] ?? null;
    importFileName.value = importForm.file?.name ?? '';
    importForm.clearErrors('file');
};
const submitImport = () => {
    if (!importForm.file) {
        importForm.setError('file', 'Pilih file Excel atau CSV terlebih dahulu.');
        return;
    }

    importForm.post(route('member.intellectual-properties.import'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            importForm.reset();
            importFileName.value = '';
            showImportGuide.value = false;
            if (importInput.value) importInput.value.value = '';
        },
    });
};
</script>

<template>
    <Head title="Data HKI Saya" />
    <MemberLayout>
        <div class="space-y-6">
            <header class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <Link :href="route('member.dashboard')" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-aski-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
                        <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.78 15.53a.75.75 0 0 1-1.06 0l-5-5a.75.75 0 0 1 0-1.06l5-5a.75.75 0 1 1 1.06 1.06L8.31 10l4.47 4.47a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" /></svg>
                        Dashboard
                    </Link>
                    <p class="mt-5 text-xs font-bold uppercase tracking-[0.16em] text-aski-700">Kekayaan Intelektual</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-aski-900 sm:text-3xl">Data HKI Saya</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Kelola dan tinjau informasi kekayaan intelektual yang terdaftar pada akun Anda.</p>
                </div>
                <div v-if="canCreate" class="flex flex-col gap-2 self-start sm:flex-row sm:self-auto">
                    <button type="button" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg border border-aski-200 bg-white px-4 py-2.5 text-sm font-semibold text-aski-800 transition hover:bg-aski-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700" @click="openImportGuide">
                        <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2.5a.75.75 0 0 1 .75.75v8.19l2.22-2.22a.75.75 0 1 1 1.06 1.06l-3.5 3.5a.75.75 0 0 1-1.06 0l-3.5-3.5a.75.75 0 1 1 1.06-1.06l2.22 2.22V3.25A.75.75 0 0 1 10 2.5ZM3.75 14a.75.75 0 0 1 .75.75v1.5h11v-1.5a.75.75 0 0 1 1.5 0V17a.75.75 0 0 1-.75.75h-12A.75.75 0 0 1 3.5 17v-2.25a.75.75 0 0 1 .25-.75Z" clip-rule="evenodd" /></svg>
                        Import Excel
                    </button>
                    <Link :href="route('member.intellectual-properties.create')" class="btn-primary inline-flex min-h-11 items-center justify-center gap-2">
                        <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 4.25a.75.75 0 0 1 .75.75v4.25H15a.75.75 0 0 1 0 1.5h-4.25V15a.75.75 0 0 1-1.5 0v-4.25H5a.75.75 0 0 1 0-1.5h4.25V5a.75.75 0 0 1 .75-.75Z" /></svg>
                        Tambah Data HKI
                    </Link>
                </div>
            </header>

            <div v-if="!canCreate" role="status" class="flex gap-3 rounded-xl border border-gold-400 bg-gold-100 p-4 text-sm text-aski-900 sm:items-center">
                <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-aski-700 sm:mt-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0Zm-8-4.75a.75.75 0 0 0-.75.75v4a.75.75 0 0 0 1.5 0V6a.75.75 0 0 0-.75-.75Zm0 8.5a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z" clip-rule="evenodd" /></svg>
                <p>Input data HKI tersedia setelah pembayaran keanggotaan Anda terverifikasi. <Link :href="route('member.invoices.index')" class="font-semibold underline underline-offset-2 hover:text-aski-700">Lihat tagihan</Link></p>
            </div>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="ip-list-heading">
                <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <h2 id="ip-list-heading" class="text-base font-semibold text-aski-900">Daftar kekayaan intelektual</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ records.total }} data terdaftar</p>
                    </div>
                    <span class="inline-flex w-fit items-center rounded-full bg-aski-50 px-3 py-1 text-xs font-semibold text-aski-800">Milik akun Anda</span>
                </div>

                <div v-if="records.data.length" tabindex="0" role="region" aria-label="Tabel data HKI, gunakan gulir horizontal untuk melihat kolom lain" class="overflow-x-auto focus-visible:outline focus-visible:outline-2 focus-visible:outline-inset focus-visible:outline-aski-700">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th scope="col" class="px-6 py-3.5">Karya / Invensi</th>
                                <th scope="col" class="px-5 py-3.5">Jenis HKI</th>
                                <th scope="col" class="px-5 py-3.5">Nomor permohonan</th>
                                <th scope="col" class="px-5 py-3.5">Tanggal permohonan</th>
                                <th scope="col" class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="record in records.data" :key="record.id" class="transition-colors hover:bg-slate-50/70">
                                <td class="max-w-sm px-6 py-4">
                                    <p class="truncate font-semibold text-slate-900" :title="record.title">{{ record.title }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ record.rights_holder || 'Pemegang hak belum dicantumkan' }}</p>
                                </td>
                                <td class="px-5 py-4"><span class="inline-flex rounded-full border border-aski-100 bg-aski-50 px-2.5 py-1 text-xs font-semibold text-aski-800">{{ record.ip_type }}</span></td>
                                <td class="px-5 py-4 font-medium text-slate-700">{{ record.application_number || '—' }}</td>
                                <td class="px-5 py-4 whitespace-nowrap text-slate-600">{{ formatDateOnly(record.application_date) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <Link :href="route('member.intellectual-properties.show', record.id)" class="inline-flex min-h-9 items-center gap-1 rounded-lg px-3 text-sm font-semibold text-aski-800 transition hover:bg-aski-50 hover:text-aski-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
                                        Lihat detail <span aria-hidden="true">→</span>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="px-5 py-14 text-center sm:px-8">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-aski-50 text-aski-700">
                        <svg aria-hidden="true" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M8 4.75h6.5L19 9.25v9A1.75 1.75 0 0 1 17.25 20h-10A1.75 1.75 0 0 1 5.5 18.25v-11A2.5 2.5 0 0 1 8 4.75Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 5v4.5h4.5M8.5 13h7m-7 3h7"/></svg>
                    </span>
                    <h3 class="mt-4 text-base font-semibold text-slate-900">Belum ada data HKI</h3>
                    <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500">Data kekayaan intelektual yang Anda tambahkan akan muncul di halaman ini.</p>
                    <Link v-if="canCreate" :href="route('member.intellectual-properties.create')" class="btn-primary mt-5 inline-flex min-h-10 items-center justify-center">Tambah Data HKI</Link>
                    <p v-else class="mt-4 text-sm text-slate-600">Selesaikan verifikasi pembayaran keanggotaan untuk mulai menginput data.</p>
                </div>
            </section>
            <PaginationLinks v-if="records.links?.length > 3" :links="records.links" />
        </div>
        <Modal :show="showImportGuide" max-width="2xl" @close="showImportGuide = false">
            <div class="max-h-[85vh] overflow-y-auto p-5 sm:p-7">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-aski-700">Import Data HKI</p>
                        <h2 class="mt-1 text-xl font-bold text-aski-900">Unggah beberapa data sekaligus</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Siapkan file Excel atau CSV. Satu baris untuk satu karya. Baris pertama dipakai sebagai judul kolom, data dimulai dari baris kedua.</p>
                    </div>
                    <button type="button" class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-800" aria-label="Tutup panduan import" @click="showImportGuide = false">
                        <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.22 4.22a.75.75 0 0 1 1.06 0L10 8.94l4.72-4.72a.75.75 0 1 1 1.06 1.06l-4.72 4.72 4.72 4.72a.75.75 0 0 1-1.06 1.06L10 11.06l-4.72 4.72a.75.75 0 0 1-1.06-1.06l4.72-4.72-4.72-4.72a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
                    </button>
                </div>

                <ol class="mt-5 grid gap-3 sm:grid-cols-3">
                    <li class="rounded-xl border border-slate-200 p-3"><span class="grid h-7 w-7 place-items-center rounded-full bg-aski-50 text-sm font-bold text-aski-800">1</span><p class="mt-2 text-sm font-semibold text-aski-900">Siapkan file</p><p class="mt-1 text-xs leading-5 text-slate-600">Gunakan Excel (.xlsx) atau CSV, maksimal 10 MB.</p></li>
                    <li class="rounded-xl border border-slate-200 p-3"><span class="grid h-7 w-7 place-items-center rounded-full bg-aski-50 text-sm font-bold text-aski-800">2</span><p class="mt-2 text-sm font-semibold text-aski-900">Isi data</p><p class="mt-1 text-xs leading-5 text-slate-600">Pastikan judul kolom dan isinya sesuai panduan di bawah.</p></li>
                    <li class="rounded-xl border border-slate-200 p-3"><span class="grid h-7 w-7 place-items-center rounded-full bg-aski-50 text-sm font-bold text-aski-800">3</span><p class="mt-2 text-sm font-semibold text-aski-900">Unggah file</p><p class="mt-1 text-xs leading-5 text-slate-600">Pilih file, lalu tekan “Mulai Import”.</p></li>
                </ol>

                <div class="mt-4 rounded-xl border border-gold-400 bg-gold-100 px-4 py-3 text-sm leading-5 text-aski-900">
                    <strong>Catatan:</strong> fitur ini menerima data jenis <strong>Hak Cipta</strong>. Tanggal ditulis dengan format <strong>YYYY-MM-DD</strong>, contoh <strong>2026-11-13</strong>.
                </div>

                <details class="group mt-4 rounded-xl border border-slate-200">
                    <summary class="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-aski-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-aski-700">Lihat nama kolom yang perlu disiapkan <span class="float-right transition-transform group-open:rotate-180" aria-hidden="true">⌄</span></summary>
                    <div class="border-t border-slate-100 px-3 pb-3 pt-2 sm:px-4">
                        <p class="mb-2 text-xs leading-5 text-slate-600">Salin nama kolom di sisi kiri ke baris pertama Excel. Jangan mengubah ejaan atau tanda garis bawahnya.</p>
                        <div class="overflow-x-auto rounded-lg border border-slate-100">
                            <table class="w-full min-w-[450px] text-left text-sm">
                                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600"><tr><th class="px-3 py-2.5">Nama kolom di Excel</th><th class="px-3 py-2.5">Data yang diisi</th></tr></thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700">
                                    <tr><td class="px-3 py-2 font-mono text-xs">application_number</td><td class="px-3 py-2">Nomor permohonan HKI</td></tr>
                                    <tr><td class="px-3 py-2 font-mono text-xs">application_date</td><td class="px-3 py-2">Tanggal permohonan</td></tr>
                                    <tr><td class="px-3 py-2 font-mono text-xs">creator</td><td class="px-3 py-2">Nama pencipta</td></tr>
                                    <tr><td class="px-3 py-2 font-mono text-xs">rights_holder</td><td class="px-3 py-2">Nama pemegang hak</td></tr>
                                    <tr><td class="px-3 py-2 font-mono text-xs">ip_type</td><td class="px-3 py-2">Isi “Hak Cipta”</td></tr>
                                    <tr><td class="px-3 py-2 font-mono text-xs">title</td><td class="px-3 py-2">Judul karya/ciptaan</td></tr>
                                    <tr><td class="px-3 py-2 font-mono text-xs">first_published_at</td><td class="px-3 py-2">Tanggal pertama diumumkan</td></tr>
                                    <tr><td class="px-3 py-2 font-mono text-xs">protection_period</td><td class="px-3 py-2">Jangka waktu pelindungan sesuai dokumen</td></tr>
                                    <tr><td class="px-3 py-2 font-mono text-xs">work_type</td><td class="px-3 py-2">Jenis ciptaan, misalnya “Program Komputer”</td></tr>
                                    <tr><td class="px-3 py-2 font-mono text-xs">recordation_number</td><td class="px-3 py-2">Nomor pencatatan (boleh kosong)</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </details>

                <p class="mt-3 text-xs leading-5 text-slate-500">Jika ada data yang tidak sesuai, seluruh file ditolak dan tidak ada data yang diubah.</p>
                <form class="mt-5 border-t border-slate-100 pt-4" @submit.prevent="submitImport">
                    <input ref="importInput" type="file" accept=".xlsx,.csv" class="sr-only" aria-label="Pilih file Excel atau CSV data HKI" @change="onImportFileSelected" />
                    <p v-if="importFileName" class="mb-2 truncate text-sm font-semibold text-aski-900">File dipilih: {{ importFileName }}</p>
                    <p v-if="importForm.errors.file" role="alert" class="mb-2 text-sm font-medium text-red-700">{{ importForm.errors.file }}</p>
                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <button type="button" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" @click="showImportGuide = false">Tutup</button>
                        <button type="button" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-aski-200 bg-white px-4 py-2 text-sm font-semibold text-aski-700 transition hover:bg-aski-50" @click="chooseImportFile">Pilih File</button>
                        <button type="submit" class="btn-primary min-h-10" :disabled="importForm.processing || !importForm.file">{{ importForm.processing ? 'Mengimpor…' : 'Mulai Import' }}</button>
                    </div>
                </form>
            </div>
        </Modal>
    </MemberLayout>
</template>
