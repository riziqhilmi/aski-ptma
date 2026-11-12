<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ contents: Array });
</script>

<template>
    <Head title="Konten Website" />
    <AdminLayout>
        <p class="text-sm font-semibold uppercase tracking-wide text-aski-700">Publikasi · Konten Website</p>
        <h1 class="mt-1 text-3xl font-bold text-aski-900">Kelola Konten Website</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Perbarui informasi profil, visi dan misi, serta kontak yang ditampilkan kepada pengunjung.</p>

        <div class="mt-6 grid gap-4 lg:grid-cols-2">
            <section v-for="content in contents" :key="content.id" class="card flex min-w-0 flex-col justify-between gap-5 p-5 sm:p-6">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-widest text-aski-700">{{ content.key.replaceAll('_', ' ') }}</p>
                    <h2 class="mt-2 text-xl font-bold text-aski-900">{{ content.title }}</h2>
                    <p class="mt-3 line-clamp-3 whitespace-pre-line text-sm leading-6 text-slate-600">{{ content.body }}</p>
                </div>
                <Link :href="route('admin.site-contents.edit', content.id)" class="inline-flex min-h-10 w-fit items-center rounded-lg border border-aski-200 px-4 text-sm font-semibold text-aski-700 hover:bg-aski-50">Ubah Konten</Link>
            </section>
        </div>
    </AdminLayout>
</template>
