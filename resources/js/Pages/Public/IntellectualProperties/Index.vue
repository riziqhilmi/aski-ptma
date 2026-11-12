<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { ref } from 'vue';
import { formatDateOnly } from '@/utils/date';

const props = defineProps({ records: Object, filters: Object, summary: Object, types: Array });
const form = ref({ ...props.filters });
const submit = () => router.get(route('public.intellectual-properties.index'), form.value, { preserveState: true, preserveScroll: true });
</script>

<template>
    <Head title="Direktori Data HKI" />
    <PublicLayout>
        <section class="bg-aski-900 text-white">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-gold-400">Kekayaan intelektual</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Statistik dan Direktori HKI</h1>
                <p class="mt-4 max-w-2xl text-base leading-7 text-aski-100">Ringkasan data HKI yang dicatat oleh anggota ASKI-PTMA.</p>
            </div>
        </section>
        <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-3">
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Total HKI</p><p class="mt-2 text-3xl font-bold tracking-tight text-aski-900">{{ summary.total }}</p><p class="mt-1 text-xs text-slate-500">Data yang tersedia di direktori</p></article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Jenis HKI</p><p class="mt-2 text-3xl font-bold tracking-tight text-aski-900">{{ summary.types }}</p><p class="mt-1 text-xs text-slate-500">Kategori kekayaan intelektual</p></article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"><p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Kontributor</p><p class="mt-2 text-3xl font-bold tracking-tight text-aski-900">{{ summary.members }}</p><p class="mt-1 text-xs text-slate-500">Anggota yang memiliki data tercatat</p></article>
            </div>

            <form class="mt-7 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5" role="search" aria-label="Cari data HKI" @submit.prevent="submit">
                <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_250px_auto]">
                    <div class="relative">
                        <label for="ip-search" class="sr-only">Cari judul, pencipta, atau pemegang hak</label>
                        <svg aria-hidden="true" class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.44 9.79l3.63 3.63a.75.75 0 1 0 1.06-1.06L13 11.73A5.5 5.5 0 0 0 8.5 3ZM4.5 8.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" /></svg>
                        <input id="ip-search" v-model="form.search" class="min-h-11 w-full rounded-lg border-slate-300 pl-10 text-sm focus:border-aski-700 focus:ring-aski-700" placeholder="Cari judul, pencipta, atau pemegang hak" />
                    </div>
                    <div><label for="ip-type" class="sr-only">Jenis HKI</label><select id="ip-type" v-model="form.type" class="min-h-11 w-full rounded-lg border-slate-300 text-sm focus:border-aski-700 focus:ring-aski-700"><option value="">Semua jenis HKI</option><option v-for="type in types" :key="type" :value="type">{{ type }}</option></select></div>
                    <button class="btn-primary min-h-11 px-5" type="submit">Cari data</button>
                </div>
            </form>

            <section class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="directory-title">
                <div class="flex flex-col gap-1 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-end sm:justify-between sm:px-6">
                    <div><h2 id="directory-title" class="text-lg font-bold text-aski-900">Direktori data HKI</h2><p class="mt-1 text-sm text-slate-500">{{ records.from || 0 }}–{{ records.to || 0 }} dari {{ records.total }} data</p></div>
                    <p class="text-xs text-slate-500">Informasi ditampilkan sesuai data yang tersedia untuk publik.</p>
                </div>
                <div tabindex="0" role="region" aria-label="Tabel data HKI, gunakan gulir horizontal untuk melihat kolom lain" class="overflow-x-auto focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-aski-700">
                    <table class="min-w-[760px] w-full text-left text-sm">
                        <thead class="bg-aski-50 text-xs uppercase tracking-wide text-aski-900"><tr><th scope="col" class="px-5 py-3.5 font-bold sm:px-6">Judul</th><th scope="col" class="px-5 py-3.5 font-bold">Jenis</th><th scope="col" class="px-5 py-3.5 font-bold">Pencipta</th><th scope="col" class="px-5 py-3.5 font-bold">Pemegang hak</th><th scope="col" class="px-5 py-3.5 font-bold">Tanggal</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="record in records.data" :key="record.id" class="transition hover:bg-slate-50"><td class="max-w-sm px-5 py-4 font-semibold text-aski-900 sm:px-6">{{ record.title }}</td><td class="px-5 py-4"><span class="inline-flex rounded-full bg-aski-50 px-2.5 py-1 text-xs font-semibold text-aski-800">{{ record.ip_type }}</span></td><td class="px-5 py-4 text-slate-700">{{ record.creator || '—' }}</td><td class="px-5 py-4 text-slate-700">{{ record.rights_holder || '—' }}</td><td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ formatDateOnly(record.application_date) }}</td></tr>
                            <tr v-if="records.data.length === 0"><td colspan="5" class="px-5 py-12 text-center sm:px-6"><p class="font-semibold text-aski-900">Data HKI tidak ditemukan</p><p class="mt-1 text-sm text-slate-500">Coba ubah kata kunci atau filter jenis HKI.</p></td></tr>
                        </tbody>
                    </table>
                </div>
                <nav v-if="records.links?.length > 3" aria-label="Navigasi halaman data HKI" class="flex flex-wrap justify-center gap-2 border-t border-slate-100 px-4 py-4">
                    <Link v-for="link in records.links" :key="link.label" :href="link.url || '#direktori-hki'" v-html="link.label" :aria-current="link.active ? 'page' : undefined" class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-lg border px-3 py-2 text-sm font-semibold transition" :class="link.active ? 'border-aski-700 bg-aski-700 text-white' : 'border-slate-200 bg-white text-aski-700 hover:bg-aski-50'" />
                </nav>
            </section>
        </section>
    </PublicLayout>
</template>
