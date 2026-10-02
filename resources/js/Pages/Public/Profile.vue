<script setup>
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineProps({
    profile: Object,
    visionMission: Object,
    organizationMembers: Array,
});
</script>

<template>
    <Head title="Profil" />
    <PublicLayout>
        <section class="bg-aski-50">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <p class="text-sm font-semibold text-aski-700">PROFIL ORGANISASI</p>
                <h1 class="mt-2 text-4xl font-bold text-aski-900">{{ profile.title }}</h1>
            </div>
        </section>
        <section class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div class="card p-6">
                <h2 class="text-2xl font-bold text-aski-900">{{ profile.title }}</h2>
                <div class="mt-4 whitespace-pre-line leading-8 text-slate-700">{{ profile.body }}</div>
            </div>
            <div class="card p-6">
                <h2 class="text-2xl font-bold text-aski-900">{{ visionMission.title }}</h2>
                <div class="mt-4 whitespace-pre-line leading-8 text-slate-700">{{ visionMission.body }}</div>
            </div>
        </section>
        <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
            <div class="mb-7">
                <p class="text-sm font-semibold text-aski-700">STRUKTUR ORGANISASI</p>
                <h2 class="mt-1 text-3xl font-bold text-aski-900">Pengurus ASKI-PTMA</h2>
            </div>
            <div class="grid gap-5 md:grid-cols-3">
                <div v-for="member in organizationMembers" :key="member.id" class="card overflow-hidden">
                    <img v-if="member.photo_url" :src="member.photo_url" :alt="member.name" class="h-64 w-full object-cover" />
                    <div v-else class="grid h-64 place-items-center bg-aski-50 text-3xl font-bold text-aski-700">{{ member.name.charAt(0) }}</div>
                    <div class="p-5">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gold-600">{{ member.position }}</p>
                        <h3 class="mt-2 text-xl font-bold text-aski-900">{{ member.name }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">{{ member.description || '-' }}</p>
                    </div>
                </div>
                <div v-if="organizationMembers.length === 0" class="card p-8 text-center text-slate-500 md:col-span-3">Struktur organisasi belum tersedia.</div>
            </div>
        </section>
    </PublicLayout>
</template>
