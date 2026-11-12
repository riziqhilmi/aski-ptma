<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineProps({ profile: Object, visionMission: Object, organizationMembers: Array });
</script>

<template>
    <Head title="Profil ASKI-PTMA" />
    <PublicLayout>
        <section class="relative overflow-hidden bg-aski-900 text-white">
            <div class="mx-auto grid max-w-7xl items-center gap-8 px-4 py-12 sm:px-6 sm:py-16 lg:grid-cols-[1fr_auto] lg:px-8 lg:py-20">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-gold-400">Tentang organisasi</p>
                    <h1 class="mt-3 max-w-3xl text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl">{{ profile.title }}</h1>
                    <p class="mt-4 max-w-2xl text-base leading-7 text-aski-100">Mengenal ASKI-PTMA, arah organisasi, dan para pengurus.</p>
                </div>
                <div class="hidden rounded-3xl border border-white/15 bg-white/10 p-5 sm:block">
                    <img src="/images/logo-aski.png" alt="Logo ASKI-PTMA" class="h-24 w-24 object-contain" />
                </div>
            </div>
        </section>

        <section class="mx-auto grid max-w-7xl gap-5 px-4 py-10 sm:px-6 md:grid-cols-2 lg:px-8 lg:py-14">
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-aski-50 text-aski-800" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2.5a.75.75 0 0 1 .75.75v5.69l4.03-2.33a.75.75 0 1 1 .75 1.3l-4.03 2.33 4.03 2.33a.75.75 0 1 1-.75 1.3l-4.03-2.33v4.66a.75.75 0 0 1-1.5 0v-4.66l-4.03 2.33a.75.75 0 1 1-.75-1.3l4.03-2.33-4.03-2.33a.75.75 0 1 1 .75-1.3l4.03 2.33V3.25A.75.75 0 0 1 10 2.5Z" /></svg></span>
                    <div><p class="text-xs font-bold uppercase tracking-[0.14em] text-aski-700">Organisasi</p><h2 class="mt-0.5 text-xl font-bold text-aski-900">{{ profile.title }}</h2></div>
                </div>
                <div class="mt-5 whitespace-pre-line text-sm leading-7 text-slate-600">{{ profile.body }}</div>
            </article>
            <article class="rounded-2xl border border-aski-100 bg-aski-50/60 p-6 shadow-sm sm:p-8">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-white text-aski-800 shadow-sm" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1.75a.75.75 0 0 1 .75.75v.53a7 7 0 0 1 5.47 5.47h.53a.75.75 0 0 1 0 1.5h-.53a7 7 0 0 1-5.47 5.47v.53a.75.75 0 0 1-1.5 0v-.53a7 7 0 0 1-5.47-5.47h-.53a.75.75 0 0 1 0-1.5h.53a7 7 0 0 1 5.47-5.47V2.5a.75.75 0 0 1 .75-.75ZM10 5a5 5 0 1 0 0 10 5 5 0 0 0 0-10Z" clip-rule="evenodd" /></svg></span>
                    <div><p class="text-xs font-bold uppercase tracking-[0.14em] text-aski-700">Arah organisasi</p><h2 class="mt-0.5 text-xl font-bold text-aski-900">{{ visionMission.title }}</h2></div>
                </div>
                <div class="mt-5 whitespace-pre-line text-sm leading-7 text-slate-700">{{ visionMission.body }}</div>
            </article>
        </section>

        <section class="bg-slate-50 py-10 sm:py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-3 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
                    <div><p class="text-xs font-bold uppercase tracking-[0.16em] text-aski-700">Orang-orang di balik organisasi</p><h2 class="mt-2 text-2xl font-bold tracking-tight text-aski-900 sm:text-3xl">Pengurus ASKI-PTMA</h2></div>
                    <Link :href="route('public.contact')" class="text-sm font-semibold text-aski-700 hover:text-aski-900">Hubungi organisasi <span aria-hidden="true">→</span></Link>
                </div>
                <div class="mt-7 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <article v-for="member in organizationMembers" :key="member.id" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                        <img v-if="member.photo_url" :src="member.photo_url" :alt="`Foto ${member.name}`" class="aspect-[4/3] w-full object-cover" />
                        <div v-else class="grid aspect-[4/3] place-items-center bg-aski-50"><span class="grid h-20 w-20 place-items-center rounded-full border border-aski-100 bg-white text-3xl font-bold text-aski-700">{{ member.name.charAt(0) }}</span></div>
                        <div class="p-5 sm:p-6">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gold-600">{{ member.position }}</p>
                            <h3 class="mt-2 text-lg font-bold text-aski-900">{{ member.name }}</h3>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ member.description || ' ' }}</p>
                        </div>
                    </article>
                    <div v-if="organizationMembers.length === 0" class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center sm:col-span-2 lg:col-span-3">
                        <p class="font-semibold text-aski-900">Struktur organisasi belum tersedia</p><p class="mt-1 text-sm text-slate-500">Informasi pengurus akan ditampilkan di sini.</p>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
