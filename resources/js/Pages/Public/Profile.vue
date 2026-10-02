<script setup>
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineProps({
    profile: Object,
    visionMission: Object,
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
    </PublicLayout>
</template>
