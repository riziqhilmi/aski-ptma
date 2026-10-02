<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({ member: Object });
const isEditing = Boolean(props.member);
const form = useForm({
    _method: isEditing ? 'put' : undefined,
    name: props.member?.name ?? '',
    position: props.member?.position ?? '',
    description: props.member?.description ?? '',
    sort_order: props.member?.sort_order ?? 0,
    is_active: props.member?.is_active ?? true,
    photo: null,
});

const submit = () => {
    if (isEditing) {
        form.post(route('admin.organization-members.update', props.member.id), { forceFormData: true });
    } else {
        form.post(route('admin.organization-members.store'), { forceFormData: true });
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Ubah Pengurus' : 'Tambah Pengurus'" />
    <AdminLayout>
        <p class="text-sm font-semibold text-aski-700">STRUKTUR ORGANISASI</p>
        <h1 class="mt-1 text-3xl font-bold text-aski-900">{{ isEditing ? 'Ubah Pengurus' : 'Tambah Pengurus' }}</h1>

        <form class="card mt-7 grid gap-5 p-6 md:grid-cols-2" @submit.prevent="submit">
            <div>
                <label class="text-sm font-semibold text-aski-900">Nama</label>
                <input v-model="form.name" class="mt-1 w-full rounded-lg border-slate-300" />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>
            <div>
                <label class="text-sm font-semibold text-aski-900">Jabatan</label>
                <input v-model="form.position" class="mt-1 w-full rounded-lg border-slate-300" />
                <InputError :message="form.errors.position" class="mt-2" />
            </div>
            <div>
                <label class="text-sm font-semibold text-aski-900">Urutan Tampil</label>
                <input v-model="form.sort_order" type="number" min="0" class="mt-1 w-full rounded-lg border-slate-300" />
                <InputError :message="form.errors.sort_order" class="mt-2" />
            </div>
            <div>
                <label class="text-sm font-semibold text-aski-900">Status</label>
                <select v-model="form.is_active" class="mt-1 w-full rounded-lg border-slate-300">
                    <option :value="true">Aktif</option>
                    <option :value="false">Nonaktif</option>
                </select>
                <InputError :message="form.errors.is_active" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-aski-900">Foto</label>
                <input type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 w-full rounded-lg border border-slate-300 bg-white p-2" @input="form.photo = $event.target.files[0]" />
                <p v-if="isEditing" class="mt-2 text-xs text-slate-500">Kosongkan jika tidak ingin mengganti foto.</p>
                <InputError :message="form.errors.photo" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-aski-900">Deskripsi</label>
                <textarea v-model="form.description" rows="4" class="mt-1 w-full rounded-lg border-slate-300"></textarea>
                <InputError :message="form.errors.description" class="mt-2" />
            </div>
            <div class="flex gap-3 md:col-span-2">
                <button class="btn-primary" type="submit" :disabled="form.processing">Simpan</button>
                <Link :href="route('admin.organization-members.index')" class="rounded-lg border border-aski-200 px-4 py-2 font-semibold text-aski-700">Batal</Link>
            </div>
        </form>
    </AdminLayout>
</template>
