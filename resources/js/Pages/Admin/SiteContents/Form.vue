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
        <p class="text-sm font-semibold text-aski-700">KONTEN WEBSITE</p>
        <h1 class="mt-1 text-3xl font-bold text-aski-900">Ubah {{ content.title }}</h1>

        <form class="card mt-7 grid gap-5 p-6 md:grid-cols-2" @submit.prevent="submit">
            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-aski-900">Judul</label>
                <input v-model="form.title" class="mt-1 w-full rounded-lg border-slate-300" />
                <InputError :message="form.errors.title" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-aski-900">Isi Konten</label>
                <textarea v-model="form.body" rows="10" class="mt-1 w-full rounded-lg border-slate-300"></textarea>
                <InputError :message="form.errors.body" class="mt-2" />
            </div>

            <template v-if="content.key === 'contact'">
                <div>
                    <label class="text-sm font-semibold text-aski-900">Email</label>
                    <input v-model="form.metadata.email" class="mt-1 w-full rounded-lg border-slate-300" />
                    <InputError :message="form.errors['metadata.email']" class="mt-2" />
                </div>
                <div>
                    <label class="text-sm font-semibold text-aski-900">Telepon</label>
                    <input v-model="form.metadata.phone" class="mt-1 w-full rounded-lg border-slate-300" />
                    <InputError :message="form.errors['metadata.phone']" class="mt-2" />
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-aski-900">Alamat</label>
                    <textarea v-model="form.metadata.address" rows="3" class="mt-1 w-full rounded-lg border-slate-300"></textarea>
                    <InputError :message="form.errors['metadata.address']" class="mt-2" />
                </div>
                <div>
                    <label class="text-sm font-semibold text-aski-900">Instagram</label>
                    <input v-model="form.metadata.instagram" class="mt-1 w-full rounded-lg border-slate-300" />
                    <InputError :message="form.errors['metadata.instagram']" class="mt-2" />
                </div>
                <div>
                    <label class="text-sm font-semibold text-aski-900">Website</label>
                    <input v-model="form.metadata.website" class="mt-1 w-full rounded-lg border-slate-300" />
                    <InputError :message="form.errors['metadata.website']" class="mt-2" />
                </div>
            </template>

            <div class="flex gap-3 md:col-span-2">
                <button class="btn-primary" type="submit" :disabled="form.processing">Simpan</button>
                <Link :href="route('admin.site-contents.index')" class="rounded-lg border border-aski-200 px-4 py-2 font-semibold text-aski-700">Batal</Link>
            </div>
        </form>
    </AdminLayout>
</template>
