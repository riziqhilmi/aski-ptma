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
        <div class="mx-auto max-w-5xl">
        <Link :href="route('admin.articles.index')" class="text-sm font-semibold text-aski-700 hover:text-aski-900">← Kembali ke Berita</Link>
        <p class="mt-5 text-sm font-semibold uppercase tracking-wide text-aski-700">Publikasi · Berita</p>
        <h1 class="mt-1 text-3xl font-bold text-aski-900">{{ isEditing ? 'Ubah Artikel' : 'Tambah Artikel' }}</h1>
        <p class="mt-2 text-sm text-slate-600">Susun berita untuk dibagikan kepada pengunjung website.</p>

        <form class="card mt-6 grid gap-5 p-5 sm:p-7 md:grid-cols-2" @submit.prevent="submit">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-aski-900">Judul <span aria-hidden="true">(wajib)</span>
                    <input v-model="form.title" required aria-required="true" :aria-invalid="Boolean(form.errors.title)" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500" />
                </label>
                <InputError :message="form.errors.title" class="mt-2" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-aski-900">Slug (opsional)
                    <input v-model="form.slug" :aria-invalid="Boolean(form.errors.slug)" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500" placeholder="Otomatis dari judul jika dikosongkan" />
                </label>
                <InputError :message="form.errors.slug" class="mt-2" />
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
                <label class="block text-sm font-semibold text-aski-900">Gambar Utama (opsional)
                <input type="file" accept="image/jpeg,image/png,image/webp" :aria-invalid="Boolean(form.errors.cover_image)" class="mt-2 min-h-11 w-full rounded-xl border border-slate-300 bg-white p-2 text-sm focus:border-aski-500 focus:ring-aski-500" @input="form.cover_image = $event.target.files[0]" />
                </label>
                <p v-if="isEditing" class="mt-2 text-xs text-slate-500">Kosongkan jika tidak ingin mengganti gambar.</p>
                <InputError :message="form.errors.cover_image" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-aski-900">Ringkasan <span aria-hidden="true">(wajib)</span>
                <textarea v-model="form.excerpt" required aria-required="true" :aria-invalid="Boolean(form.errors.excerpt)" rows="3" class="mt-2 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500"></textarea>
                </label>
                <InputError :message="form.errors.excerpt" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-aski-900">Isi Artikel <span aria-hidden="true">(wajib)</span>
                <textarea v-model="form.body" required aria-required="true" :aria-invalid="Boolean(form.errors.body)" rows="12" class="mt-2 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500"></textarea>
                </label>
                <InputError :message="form.errors.body" class="mt-2" />
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end md:col-span-2">
                <Link :href="route('admin.articles.index')" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-5 font-semibold text-slate-700">Batal</Link>
                <button class="btn-primary inline-flex min-h-11 items-center justify-center" type="submit" :disabled="form.processing">{{ form.processing ? 'Menyimpan…' : 'Simpan Artikel' }}</button>
            </div>
        </form>
        </div>
    </AdminLayout>
</template>
