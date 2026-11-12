<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({ record: Object });
const isEditing = Boolean(props.record);
const workTypeOptions = [
    'Hak Cipta', 'Paten', 'Paten Sederhana', 'Merek', 'Desain Industri',
    'Indikasi Geografis', 'Rahasia Dagang', 'Desain Tata Letak Sirkuit Terpadu (DTLST)',
    'Perlindungan Varietas Tanaman (PVT)',
];
const dateInputValue = (value) => value ? String(value).slice(0, 10) : '';
const form = useForm({
    application_number: props.record?.application_number ?? '',
    application_date: dateInputValue(props.record?.application_date),
    creator: props.record?.creator ?? '',
    rights_holder: props.record?.rights_holder ?? '',
    ip_type: props.record?.ip_type ?? 'Hak Cipta',
    recordation_number: props.record?.recordation_number ?? '',
    title: props.record?.title ?? '',
    type_details: { work_type: props.record?.type_details?.work_type ?? '' },
    first_published_at: dateInputValue(props.record?.first_published_at),
    protection_period: props.record?.protection_period ?? '',
});

const submit = () => {
    if (isEditing) {
        form.put(route('member.intellectual-properties.update', props.record.id));
    } else {
        form.post(route('member.intellectual-properties.store'));
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Ubah Data HKI' : 'Formulir Isian Data HKI'" />
    <MemberLayout>
        <div class="mx-auto max-w-5xl">
        <Link :href="isEditing ? route('member.intellectual-properties.show', props.record.id) : route('member.intellectual-properties.index')" class="mb-4 inline-flex min-h-9 items-center gap-2 rounded-lg text-sm font-medium text-slate-500 transition hover:text-aski-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.78 15.53a.75.75 0 0 1-1.06 0l-5-5a.75.75 0 0 1 0-1.06l5-5a.75.75 0 1 1 1.06 1.06L8.31 10l4.47 4.47a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" /></svg>
            {{ isEditing ? 'Kembali ke Detail HKI' : 'Kembali ke Data HKI Saya' }}
        </Link>
        <section class="rounded-2xl bg-white px-5 py-6 sm:px-9 sm:py-8">
        <form class="[&_input]:h-12 [&_input]:rounded-xl [&_input]:border-aski-100 [&_input]:bg-slate-50/40 [&_input]:px-4 [&_select]:h-12 [&_select]:rounded-xl [&_select]:border-aski-100 [&_select]:bg-slate-50/40 [&_select]:px-4" @submit.prevent="submit">
            <div class="mb-6 flex items-center gap-3">
                <span aria-hidden="true" class="h-7 w-1.5 rounded-full bg-gold-400"></span>
                <h1 class="text-lg font-extrabold text-aski-900">Data Hak Kekayaan Intelektual</h1>
            </div>
            <div class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
            <div>
                <label for="application_number" class="text-sm font-semibold text-aski-900">Nomor Permohonan <span class="text-red-600" aria-hidden="true">*</span></label>
                <input id="application_number" v-model="form.application_number" required aria-required="true" placeholder="Masukkan nomor permohonan" :aria-invalid="Boolean(form.errors.application_number)" class="mt-1.5 w-full placeholder:text-slate-400 focus:border-aski-500 focus:ring-aski-500" />
                <InputError :message="form.errors.application_number" class="mt-2" />
            </div>
            <div>
                <label for="application_date" class="text-sm font-semibold text-aski-900">Tanggal Permohonan <span class="text-red-600" aria-hidden="true">*</span></label>
                <input id="application_date" v-model="form.application_date" type="date" required aria-required="true" :aria-invalid="Boolean(form.errors.application_date)" class="mt-1 w-full rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500" />
                <InputError :message="form.errors.application_date" class="mt-2" />
            </div>
            <div>
                <label for="creator" class="text-sm font-semibold text-aski-900">Pencipta <span class="text-red-600" aria-hidden="true">*</span></label>
                <input id="creator" v-model="form.creator" required aria-required="true" placeholder="Contoh: Anton Rusli; Ria Sagara; dst" :aria-invalid="Boolean(form.errors.creator)" class="mt-1 w-full placeholder:text-slate-400 focus:border-aski-500 focus:ring-aski-500" />
                <InputError :message="form.errors.creator" class="mt-2" />
            </div>
            <div>
                <label for="rights_holder" class="text-sm font-semibold text-aski-900">Pemegang Hak Cipta <span class="text-red-600" aria-hidden="true">*</span></label>
                <input id="rights_holder" v-model="form.rights_holder" required aria-required="true" placeholder="Contoh: Anton Rusli; Ria Sagara; dst" :aria-invalid="Boolean(form.errors.rights_holder)" class="mt-1 w-full placeholder:text-slate-400 focus:border-aski-500 focus:ring-aski-500" />
                <InputError :message="form.errors.rights_holder" class="mt-2" />
            </div>
            <div>
                <label for="type_details_work_type" class="text-sm font-semibold text-aski-900">Jenis Ciptaan <span class="text-red-600" aria-hidden="true">*</span></label>
                <select id="type_details_work_type" v-model="form.type_details.work_type" required aria-required="true" :aria-invalid="Boolean(form.errors['type_details.work_type'])" class="mt-1 w-full focus:border-aski-500 focus:ring-aski-500"><option value="">-- Pilih Jenis Ciptaan --</option><option v-for="option in workTypeOptions" :key="option" :value="option">{{ option }}</option></select>
                <InputError :message="form.errors['type_details.work_type']" class="mt-2" />
            </div>
            <div>
                <label for="recordation_number" class="text-sm font-semibold text-aski-900">Nomor Pencatatan</label>
                <input id="recordation_number" v-model="form.recordation_number" placeholder="Masukkan nomor pencatatan" :aria-invalid="Boolean(form.errors.recordation_number)" class="mt-1 w-full focus:border-aski-500 focus:ring-aski-500" />
                <InputError :message="form.errors.recordation_number" class="mt-2" />
            </div>
            <div class="sm:col-span-2">
                <label for="title" class="text-sm font-semibold text-aski-900">Judul Ciptaan <span class="text-red-600" aria-hidden="true">*</span></label>
                <textarea id="title" v-model="form.title" required aria-required="true" rows="3" placeholder="Masukkan judul ciptaan" :aria-invalid="Boolean(form.errors.title)" class="mt-1 w-full rounded-xl border-aski-100 bg-slate-50/40 px-4 py-3 placeholder:text-slate-400 focus:border-aski-500 focus:ring-aski-500"></textarea>
                <InputError :message="form.errors.title" class="mt-2" />
            </div>
            <div>
                <label for="first_published_at" class="text-sm font-semibold text-aski-900">Tanggal Diumumkan untuk Pertama Kali <span class="text-red-600" aria-hidden="true">*</span></label>
                <input id="first_published_at" v-model="form.first_published_at" type="date" required aria-required="true" :aria-invalid="Boolean(form.errors.first_published_at)" class="mt-1 w-full focus:border-aski-500 focus:ring-aski-500" />
                <InputError :message="form.errors.first_published_at" class="mt-2" />
            </div>
            <div>
                <label for="protection_period" class="text-sm font-semibold text-aski-900">Jangka Waktu Pelindungan <span class="text-red-600" aria-hidden="true">*</span></label>
                <input id="protection_period" v-model="form.protection_period" required aria-required="true" placeholder="Contoh: 70 tahun" :aria-invalid="Boolean(form.errors.protection_period)" class="mt-1 w-full rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500" />
                <InputError :message="form.errors.protection_period" class="mt-2" />
            </div>
            </div>
            <div class="mt-7 flex flex-wrap items-center gap-3 border-t border-aski-100 pt-6">
                <button class="btn-primary min-w-20" type="submit" :disabled="form.processing">{{ form.processing ? 'Menyimpan…' : 'Simpan' }}</button>
                <Link :href="route('member.intellectual-properties.index')" class="inline-flex min-h-11 items-center rounded-lg border border-aski-200 px-4 py-2 text-sm font-semibold text-aski-700 transition hover:bg-aski-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">Batal</Link>
                <p class="w-full text-xs text-slate-500"><span class="font-bold text-red-600">*</span> Kolom bertanda bintang wajib diisi.</p>
            </div>
        </form>
        </section>
        </div>
    </MemberLayout>
</template>
