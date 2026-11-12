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
        <div class="mx-auto max-w-4xl">
        <Link :href="route('admin.documents.index')" class="text-sm font-semibold text-aski-700 hover:text-aski-900">← Kembali ke Dokumen</Link>
        <p class="mt-5 text-sm font-semibold uppercase tracking-wide text-aski-700">Publikasi · Dokumen Panduan</p>
        <h1 class="mt-1 text-3xl font-bold text-aski-900">{{ isEditing ? 'Ubah Dokumen' : 'Tambah Dokumen' }}</h1>
        <p class="mt-2 text-sm text-slate-600">Kelola dokumen yang tersedia bagi anggota.</p>

        <form class="card mt-6 grid gap-5 p-5 sm:p-7 md:grid-cols-2" @submit.prevent="submit">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-aski-900">Judul Dokumen <span aria-hidden="true">(wajib)</span>
                <input v-model="form.title" required aria-required="true" :aria-invalid="Boolean(form.errors.title)" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500" />
                </label>
                <InputError :message="form.errors.title" class="mt-2" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-aski-900">Kategori <span aria-hidden="true">(wajib)</span>
                <select v-model="form.category" required aria-required="true" :aria-invalid="Boolean(form.errors.category)" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500">
                    <option v-for="category in categories" :key="category.value" :value="category.value">{{ category.label }}</option>
                </select>
                </label>
                <InputError :message="form.errors.category" class="mt-2" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-aski-900">Status <span aria-hidden="true">(wajib)</span>
                <select v-model="form.status" required aria-required="true" :aria-invalid="Boolean(form.errors.status)" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500">
                    <option value="draft">Draft</option>
                    <option value="published">Terbit</option>
                </select>
                </label>
                <InputError :message="form.errors.status" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-aski-900">Deskripsi (opsional)
                <textarea v-model="form.description" :aria-invalid="Boolean(form.errors.description)" rows="4" class="mt-2 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500"></textarea>
                </label>
                <InputError :message="form.errors.description" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-aski-900">File PDF {{ isEditing ? '(opsional)' : '(wajib)' }}
                <input type="file" :required="!isEditing" :aria-required="!isEditing" accept="application/pdf" :aria-invalid="Boolean(form.errors.file)" class="mt-2 min-h-11 w-full rounded-xl border border-slate-300 bg-white p-2 text-sm focus:border-aski-500 focus:ring-aski-500" @input="form.file = $event.target.files[0]" />
                </label>
                <p v-if="isEditing" class="mt-2 text-xs text-slate-500">Kosongkan jika tidak ingin mengganti file.</p>
                <InputError :message="form.errors.file" class="mt-2" />
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end md:col-span-2">
                <Link :href="route('admin.documents.index')" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-5 font-semibold text-slate-700">Batal</Link>
                <button class="btn-primary inline-flex min-h-11 items-center justify-center" type="submit" :disabled="form.processing">{{ form.processing ? 'Menyimpan…' : 'Simpan Dokumen' }}</button>
            </div>
        </form>
        </div>
    </AdminLayout>
</template>
