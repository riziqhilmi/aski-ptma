<script setup>
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineProps({ contact: Object });
</script>

<template>
    <Head title="Kontak" />
    <PublicLayout>
        <section class="bg-aski-50">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <p class="text-sm font-semibold text-aski-700">KONTAK</p>
                <h1 class="mt-2 text-4xl font-bold text-aski-900">{{ contact.title }}</h1>
                <p class="mt-4 max-w-2xl text-slate-600">{{ contact.body }}</p>
            </div>
        </section>
        <section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="card grid gap-5 p-6 sm:grid-cols-2">
                <div><p class="text-xs font-semibold uppercase text-slate-500">Email</p><p class="mt-1 font-semibold text-aski-900">{{ contact.metadata.email || '-' }}</p></div>
                <div><p class="text-xs font-semibold uppercase text-slate-500">Telepon</p><p class="mt-1 font-semibold text-aski-900">{{ contact.metadata.phone || '-' }}</p></div>
                <div class="sm:col-span-2"><p class="text-xs font-semibold uppercase text-slate-500">Alamat</p><p class="mt-1 whitespace-pre-line font-semibold text-aski-900">{{ contact.metadata.address || '-' }}</p></div>
                <div><p class="text-xs font-semibold uppercase text-slate-500">Instagram</p><p class="mt-1 font-semibold text-aski-900">{{ contact.metadata.instagram || '-' }}</p></div>
                <div><p class="text-xs font-semibold uppercase text-slate-500">Website</p><p class="mt-1 font-semibold text-aski-900">{{ contact.metadata.website || '-' }}</p></div>
            </div>
        </section>
    </PublicLayout>
</template>
