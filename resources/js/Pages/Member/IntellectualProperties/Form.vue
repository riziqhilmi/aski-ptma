<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({ record: Object });
const isEditing = Boolean(props.record);
const form = useForm({
    application_number: props.record?.application_number ?? '',
    application_date: props.record?.application_date ?? '',
    creator: props.record?.creator ?? '',
    rights_holder: props.record?.rights_holder ?? '',
    ip_type: props.record?.ip_type ?? '',
    recordation_number: props.record?.recordation_number ?? '',
    title: props.record?.title ?? '',
    first_published_at: props.record?.first_published_at ?? '',
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
    <Head :title="isEditing ? 'Ubah Data HKI' : 'Tambah Data HKI'" />
    <MemberLayout>
        <p class="text-sm font-semibold text-aski-700">DATA HKI</p>
        <h1 class="mt-1 text-3xl font-bold text-aski-900">{{ isEditing ? 'Ubah Data HKI' : 'Tambah Data HKI' }}</h1>

        <form class="card mt-7 grid gap-5 p-6 md:grid-cols-2" @submit.prevent="submit">
            <div>
                <label class="text-sm font-semibold text-aski-900">Nomor Permohonan</label>
                <input v-model="form.application_number" class="mt-1 w-full rounded-lg border-slate-300" />
                <InputError :message="form.errors.application_number" class="mt-2" />
            </div>
            <div>
                <label class="text-sm font-semibold text-aski-900">Tanggal Permohonan</label>
                <input v-model="form.application_date" type="date" class="mt-1 w-full rounded-lg border-slate-300" />
                <InputError :message="form.errors.application_date" class="mt-2" />
            </div>
            <div>
                <label class="text-sm font-semibold text-aski-900">Pencipta</label>
                <input v-model="form.creator" class="mt-1 w-full rounded-lg border-slate-300" />
                <InputError :message="form.errors.creator" class="mt-2" />
            </div>
            <div>
                <label class="text-sm font-semibold text-aski-900">Pemegang Hak</label>
                <input v-model="form.rights_holder" class="mt-1 w-full rounded-lg border-slate-300" />
                <InputError :message="form.errors.rights_holder" class="mt-2" />
            </div>
            <div>
                <label class="text-sm font-semibold text-aski-900">Jenis HKI</label>
                <input v-model="form.ip_type" list="ip-types" class="mt-1 w-full rounded-lg border-slate-300" />
                <datalist id="ip-types">
                    <option value="Hak Cipta" />
                    <option value="Paten" />
                    <option value="Merek" />
                    <option value="Desain Industri" />
                    <option value="Rahasia Dagang" />
                </datalist>
                <InputError :message="form.errors.ip_type" class="mt-2" />
            </div>
            <div>
                <label class="text-sm font-semibold text-aski-900">Nomor Pencatatan</label>
                <input v-model="form.recordation_number" class="mt-1 w-full rounded-lg border-slate-300" />
                <InputError :message="form.errors.recordation_number" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-aski-900">Judul Ciptaan</label>
                <input v-model="form.title" class="mt-1 w-full rounded-lg border-slate-300" />
                <InputError :message="form.errors.title" class="mt-2" />
            </div>
            <div>
                <label class="text-sm font-semibold text-aski-900">Tanggal Pertama Kali Diumumkan</label>
                <input v-model="form.first_published_at" type="date" class="mt-1 w-full rounded-lg border-slate-300" />
                <InputError :message="form.errors.first_published_at" class="mt-2" />
            </div>
            <div>
                <label class="text-sm font-semibold text-aski-900">Jangka Waktu Pelindungan</label>
                <input v-model="form.protection_period" class="mt-1 w-full rounded-lg border-slate-300" />
                <InputError :message="form.errors.protection_period" class="mt-2" />
            </div>
            <div class="flex gap-3 md:col-span-2">
                <button class="btn-primary" type="submit" :disabled="form.processing">Simpan</button>
                <Link :href="route('member.intellectual-properties.index')" class="rounded-lg border border-aski-200 px-4 py-2 font-semibold text-aski-700">Batal</Link>
            </div>
        </form>
    </MemberLayout>
</template>
