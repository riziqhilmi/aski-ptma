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
        <div class="mx-auto max-w-4xl">
        <Link :href="route('admin.organization-members.index')" class="text-sm font-semibold text-aski-700 hover:text-aski-900">← Kembali ke Struktur Organisasi</Link>
        <p class="mt-5 text-sm font-semibold uppercase tracking-wide text-aski-700">Publikasi · Struktur Organisasi</p>
        <h1 class="mt-1 text-3xl font-bold text-aski-900">{{ isEditing ? 'Ubah Pengurus' : 'Tambah Pengurus' }}</h1>
        <p class="mt-2 text-sm text-slate-600">Atur identitas, jabatan, urutan, dan status pengurus yang ditampilkan di website.</p>

        <form class="card mt-6 grid gap-5 p-5 sm:p-7 md:grid-cols-2" @submit.prevent="submit">
            <div>
                <label class="block text-sm font-semibold text-aski-900">Nama <span aria-hidden="true">(wajib)</span>
                    <input v-model="form.name" required aria-required="true" :aria-invalid="Boolean(form.errors.name)" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500" />
                </label>
                <InputError :message="form.errors.name" class="mt-2" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-aski-900">Jabatan <span aria-hidden="true">(wajib)</span>
                    <input v-model="form.position" required aria-required="true" :aria-invalid="Boolean(form.errors.position)" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500" />
                </label>
                <InputError :message="form.errors.position" class="mt-2" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-aski-900">Urutan Tampil <span aria-hidden="true">(wajib)</span>
                    <input v-model="form.sort_order" type="number" min="0" required aria-required="true" :aria-invalid="Boolean(form.errors.sort_order)" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500" />
                </label>
                <InputError :message="form.errors.sort_order" class="mt-2" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-aski-900">Status <span aria-hidden="true">(wajib)</span>
                <select v-model="form.is_active" required aria-required="true" :aria-invalid="Boolean(form.errors.is_active)" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500">
                    <option :value="true">Aktif</option>
                    <option :value="false">Nonaktif</option>
                </select>
                </label>
                <InputError :message="form.errors.is_active" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-aski-900">Foto (opsional)
                <input type="file" accept="image/jpeg,image/png,image/webp" :aria-invalid="Boolean(form.errors.photo)" class="mt-2 min-h-11 w-full rounded-xl border border-slate-300 bg-white p-2 text-sm focus:border-aski-500 focus:ring-aski-500" @input="form.photo = $event.target.files[0]" />
                </label>
                <p v-if="isEditing" class="mt-2 text-xs text-slate-500">Kosongkan jika tidak ingin mengganti foto.</p>
                <InputError :message="form.errors.photo" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-aski-900">Deskripsi (opsional)
                <textarea v-model="form.description" :aria-invalid="Boolean(form.errors.description)" rows="4" class="mt-2 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500"></textarea>
                </label>
                <InputError :message="form.errors.description" class="mt-2" />
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end md:col-span-2">
                <Link :href="route('admin.organization-members.index')" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-5 font-semibold text-slate-700">Batal</Link>
                <button class="btn-primary inline-flex min-h-11 items-center justify-center" type="submit" :disabled="form.processing">{{ form.processing ? 'Menyimpan…' : 'Simpan Pengurus' }}</button>
            </div>
        </form>
        </div>
    </AdminLayout>
</template>
