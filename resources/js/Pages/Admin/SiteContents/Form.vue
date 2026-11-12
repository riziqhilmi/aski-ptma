<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({ content: Object });
const form = useForm({
    title: props.content.title ?? '',
    body: props.content.body ?? '',
    metadata: {
        email: props.content.metadata?.email ?? '',
        phone: props.content.metadata?.phone ?? '',
        address: props.content.metadata?.address ?? '',
        instagram: props.content.metadata?.instagram ?? '',
        website: props.content.metadata?.website ?? '',
    },
});

const submit = () => form.put(route('admin.site-contents.update', props.content.id));
</script>

<template>
    <Head title="Ubah Konten Website" />
    <AdminLayout>
        <div class="mx-auto max-w-4xl">
        <Link :href="route('admin.site-contents.index')" class="text-sm font-semibold text-aski-700 hover:text-aski-900">← Kembali ke Konten Website</Link>
        <p class="mt-5 text-sm font-semibold uppercase tracking-wide text-aski-700">Publikasi · Konten Website</p>
        <h1 class="mt-1 text-3xl font-bold text-aski-900">Ubah {{ content.title }}</h1>
        <p class="mt-2 text-sm text-slate-600">Perubahan akan ditampilkan pada halaman publik website.</p>

        <form class="card mt-6 grid gap-5 p-5 sm:p-7 md:grid-cols-2" @submit.prevent="submit">
            <div class="md:col-span-2">
                <label for="site-content-title" class="text-sm font-semibold text-aski-900">Judul</label>
                <input id="site-content-title" v-model="form.title" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500" />
                <InputError :message="form.errors.title" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <label for="site-content-body" class="text-sm font-semibold text-aski-900">Isi Konten</label>
                <textarea id="site-content-body" v-model="form.body" rows="10" class="mt-2 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500"></textarea>
                <InputError :message="form.errors.body" class="mt-2" />
            </div>

            <template v-if="content.key === 'contact'">
                <div>
                    <label for="site-contact-email" class="text-sm font-semibold text-aski-900">Email</label>
                    <input id="site-contact-email" v-model="form.metadata.email" type="email" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500" />
                    <InputError :message="form.errors['metadata.email']" class="mt-2" />
                </div>
                <div>
                    <label for="site-contact-phone" class="text-sm font-semibold text-aski-900">Telepon</label>
                    <input id="site-contact-phone" v-model="form.metadata.phone" type="tel" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500" />
                    <InputError :message="form.errors['metadata.phone']" class="mt-2" />
                </div>
                <div class="md:col-span-2">
                    <label for="site-contact-address" class="text-sm font-semibold text-aski-900">Alamat</label>
                    <textarea id="site-contact-address" v-model="form.metadata.address" rows="3" class="mt-2 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500"></textarea>
                    <InputError :message="form.errors['metadata.address']" class="mt-2" />
                </div>
                <div>
                    <label for="site-contact-instagram" class="text-sm font-semibold text-aski-900">Instagram</label>
                    <input id="site-contact-instagram" v-model="form.metadata.instagram" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500" />
                    <InputError :message="form.errors['metadata.instagram']" class="mt-2" />
                </div>
                <div>
                    <label for="site-contact-website" class="text-sm font-semibold text-aski-900">Website</label>
                    <input id="site-contact-website" v-model="form.metadata.website" type="url" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 focus:border-aski-500 focus:ring-aski-500" />
                    <InputError :message="form.errors['metadata.website']" class="mt-2" />
                </div>
            </template>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end md:col-span-2">
                <Link :href="route('admin.site-contents.index')" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-5 font-semibold text-slate-700">Batal</Link>
                <button class="btn-primary inline-flex min-h-11 items-center justify-center" type="submit" :disabled="form.processing">{{ form.processing ? 'Menyimpan…' : 'Simpan Perubahan' }}</button>
            </div>
        </form>
        </div>
    </AdminLayout>
</template>
