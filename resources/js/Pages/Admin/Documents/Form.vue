<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    document: Object,
    categories: Array,
});

const isEditing = Boolean(props.document);
const form = useForm({
    _method: isEditing ? 'put' : undefined,
    title: props.document?.title ?? '',
    category: props.document?.category ?? 'guide',
    status: props.document?.status ?? 'draft',
    description: props.document?.description ?? '',
    file: null,
});

const submit = () => {
    if (isEditing) {
        form.post(route('admin.documents.update', props.document.id), {
            forceFormData: true,
        });
    } else {
        form.post(route('admin.documents.store'), { forceFormData: true });
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Ubah Dokumen' : 'Tambah Dokumen'" />
    <AdminLayout>
        <p class="text-sm font-semibold text-aski-700">DOKUMEN</p>
        <h1 class="mt-1 text-3xl font-bold text-aski-900">{{ isEditing ? 'Ubah Dokumen' : 'Tambah Dokumen' }}</h1>

        <form class="card mt-7 grid gap-5 p-6 md:grid-cols-2" @submit.prevent="submit">
            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-aski-900">Judul Dokumen</label>
                <input v-model="form.title" class="mt-1 w-full rounded-lg border-slate-300" />
                <InputError :message="form.errors.title" class="mt-2" />
            </div>
            <div>
                <label class="text-sm font-semibold text-aski-900">Kategori</label>
                <select v-model="form.category" class="mt-1 w-full rounded-lg border-slate-300">
                    <option v-for="category in categories" :key="category.value" :value="category.value">{{ category.label }}</option>
                </select>
                <InputError :message="form.errors.category" class="mt-2" />
            </div>
            <div>
                <label class="text-sm font-semibold text-aski-900">Status</label>
                <select v-model="form.status" class="mt-1 w-full rounded-lg border-slate-300">
                    <option value="draft">Draft</option>
                    <option value="published">Terbit</option>
                </select>
                <InputError :message="form.errors.status" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-aski-900">Deskripsi</label>
                <textarea v-model="form.description" rows="4" class="mt-1 w-full rounded-lg border-slate-300"></textarea>
                <InputError :message="form.errors.description" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-aski-900">File PDF</label>
                <input type="file" accept="application/pdf" class="mt-1 w-full rounded-lg border border-slate-300 bg-white p-2" @input="form.file = $event.target.files[0]" />
                <p v-if="isEditing" class="mt-2 text-xs text-slate-500">Kosongkan jika tidak ingin mengganti file.</p>
                <InputError :message="form.errors.file" class="mt-2" />
            </div>
            <div class="flex gap-3 md:col-span-2">
                <button class="btn-primary" type="submit" :disabled="form.processing">Simpan</button>
                <Link :href="route('admin.documents.index')" class="rounded-lg border border-aski-200 px-4 py-2 font-semibold text-aski-700">Batal</Link>
            </div>
        </form>
    </AdminLayout>
</template>
