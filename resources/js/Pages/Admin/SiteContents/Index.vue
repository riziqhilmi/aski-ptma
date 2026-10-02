<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ contents: Array });
</script>

<template>
    <Head title="Konten Website" />
    <AdminLayout>
        <p class="text-sm font-semibold text-aski-700">KONTEN WEBSITE</p>
        <h1 class="mt-1 text-3xl font-bold text-aski-900">Kelola Profil, Visi-Misi, dan Kontak</h1>

        <div class="mt-7 grid gap-4">
            <div v-for="content in contents" :key="content.id" class="card flex flex-wrap items-center justify-between gap-4 p-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-gold-600">{{ content.key }}</p>
                    <h2 class="mt-1 text-xl font-bold text-aski-900">{{ content.title }}</h2>
                    <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500">{{ content.body }}</p>
                </div>
                <Link :href="route('admin.site-contents.edit', content.id)" class="btn-primary">Ubah Konten</Link>
            </div>
        </div>
    </AdminLayout>
</template>
