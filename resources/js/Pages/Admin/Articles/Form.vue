<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({ article: Object });
const isEditing = Boolean(props.article);
const form = useForm({
    _method: isEditing ? 'put' : undefined,
    title: props.article?.title ?? '',
    slug: props.article?.slug ?? '',
    excerpt: props.article?.excerpt ?? '',
    body: props.article?.body ?? '',
    status: props.article?.status ?? 'draft',
    cover_image: null,
});

const submit = () => {
    if (isEditing) {
        form.post(route('admin.articles.update', props.article.id), { forceFormData: true });
    } else {
        form.post(route('admin.articles.store'), { forceFormData: true });
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Ubah Artikel' : 'Tambah Artikel'" />
    <AdminLayout>
        <p class="text-sm font-semibold text-aski-700">BERITA</p>
        <h1 class="mt-1 text-3xl font-bold text-aski-900">{{ isEditing ? 'Ubah Artikel' : 'Tambah Artikel' }}</h1>

        <form class="card mt-7 grid gap-5 p-6 md:grid-cols-2" @submit.prevent="submit">
            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-aski-900">Judul</label>
                <input v-model="form.title" class="mt-1 w-full rounded-lg border-slate-300" />
                <InputError :message="form.errors.title" class="mt-2" />
            </div>
            <div>
                <label class="text-sm font-semibold text-aski-900">Slug</label>
                <input v-model="form.slug" class="mt-1 w-full rounded-lg border-slate-300" placeholder="otomatis jika dikosongkan" />
                <InputError :message="form.errors.slug" class="mt-2" />
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
                <label class="text-sm font-semibold text-aski-900">Gambar Utama</label>
                <input type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 w-full rounded-lg border border-slate-300 bg-white p-2" @input="form.cover_image = $event.target.files[0]" />
                <p v-if="isEditing" class="mt-2 text-xs text-slate-500">Kosongkan jika tidak ingin mengganti gambar.</p>
                <InputError :message="form.errors.cover_image" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-aski-900">Ringkasan</label>
                <textarea v-model="form.excerpt" rows="3" class="mt-1 w-full rounded-lg border-slate-300"></textarea>
                <InputError :message="form.errors.excerpt" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-aski-900">Isi Artikel</label>
                <textarea v-model="form.body" rows="12" class="mt-1 w-full rounded-lg border-slate-300"></textarea>
                <InputError :message="form.errors.body" class="mt-2" />
            </div>
            <div class="flex gap-3 md:col-span-2">
                <button class="btn-primary" type="submit" :disabled="form.processing">Simpan</button>
                <Link :href="route('admin.articles.index')" class="rounded-lg border border-aski-200 px-4 py-2 font-semibold text-aski-700">Batal</Link>
            </div>
        </form>
    </AdminLayout>
</template>
