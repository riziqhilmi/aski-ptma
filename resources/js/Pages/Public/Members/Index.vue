<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { ref } from 'vue';
import { formatDateOnly } from '@/utils/date';

const props = defineProps({ members: Object, filters: Object });
const form = ref({ ...props.filters });
const submit = () => router.get(route('public.members.index'), form.value, { preserveState: true, preserveScroll: true });
</script>

<template>
    <Head title="Direktori Anggota" />
    <PublicLayout>
        <section class="bg-aski-900 text-white">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-gold-400">Jejaring organisasi</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Direktori Anggota ASKI-PTMA</h1>
                <p class="mt-4 max-w-2xl text-base leading-7 text-aski-100">Daftar anggota aktif yang telah menyelesaikan proses keanggotaan.</p>
            </div>
        </section>
        <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
            <form class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5" role="search" aria-label="Cari anggota" @submit.prevent="submit">
                <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto]">
                    <div class="relative">
                        <label for="member-search" class="sr-only">Cari nama atau institusi</label>
                        <svg aria-hidden="true" class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.44 9.79l3.63 3.63a.75.75 0 1 0 1.06-1.06L13 11.73A5.5 5.5 0 0 0 8.5 3ZM4.5 8.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" /></svg>
                        <input id="member-search" v-model="form.search" class="min-h-11 w-full rounded-lg border-slate-300 pl-10 text-sm focus:border-aski-700 focus:ring-aski-700" placeholder="Cari nama atau institusi" />
                    </div>
                    <div>
                        <label for="member-type" class="sr-only">Tipe anggota</label>
                        <select id="member-type" v-model="form.type" class="min-h-11 w-full rounded-lg border-slate-300 text-sm focus:border-aski-700 focus:ring-aski-700">
                            <option value="">Semua tipe anggota</option><option value="individual">Individu</option><option value="institution">Institusi</option>
                        </select>
                    </div>
                    <button class="btn-primary min-h-11 px-5" type="submit">Cari anggota</button>
                </div>
            </form>

            <div class="mb-5 mt-8 flex items-center justify-between gap-4">
                <h2 class="text-lg font-bold text-aski-900">Anggota aktif</h2>
                <span class="text-sm text-slate-500">{{ members.total }} anggota</span>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <article v-for="membership in members.data" :key="membership.id" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-aski-200 hover:shadow-md sm:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <span class="inline-flex rounded-full bg-aski-50 px-3 py-1 text-xs font-bold text-aski-800">{{ membership.user.member_profile?.account_type === 'institution' ? 'Institusi' : 'Individu' }}</span>
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-aski-50 text-sm font-bold text-aski-700" aria-hidden="true">{{ (membership.user.member_profile?.institution_name || membership.user.name).charAt(0) }}</span>
                    </div>
                    <h3 class="mt-4 break-words text-lg font-bold leading-6 text-aski-900">{{ membership.user.member_profile?.institution_name || membership.user.name }}</h3>
                    <div class="mt-5 border-t border-slate-100 pt-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.13em] text-slate-500">Keanggotaan aktif sampai</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800">{{ formatDateOnly(membership.ends_at) }}</p>
                    </div>
                </article>
                <div v-if="members.data.length === 0" class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center sm:col-span-2 lg:col-span-3"><p class="font-semibold text-aski-900">Anggota tidak ditemukan</p><p class="mt-1 text-sm text-slate-500">Coba gunakan kata kunci atau tipe anggota yang berbeda.</p></div>
            </div>
            <nav v-if="members.links?.length > 3" aria-label="Navigasi halaman anggota" class="mt-8 flex flex-wrap justify-center gap-2">
                <Link v-for="link in members.links" :key="link.label" :href="link.url || '#anggota'" v-html="link.label" :aria-current="link.active ? 'page' : undefined" class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-lg border px-3 py-2 text-sm font-semibold transition" :class="link.active ? 'border-aski-700 bg-aski-700 text-white' : 'border-slate-200 bg-white text-aski-700 hover:bg-aski-50'" />
            </nav>
            <div class="mt-10 rounded-2xl bg-aski-50 p-5 sm:flex sm:items-center sm:justify-between sm:gap-6 sm:p-6">
                <div><p class="font-bold text-aski-900">Ingin mengetahui lebih lanjut tentang ASKI-PTMA?</p><p class="mt-1 text-sm text-slate-600">Pelajari profil organisasi dan informasi keanggotaan.</p></div>
                <Link :href="route('public.profile')" class="mt-4 inline-flex min-h-10 items-center justify-center rounded-lg border border-aski-200 bg-white px-4 text-sm font-semibold text-aski-800 hover:bg-aski-100 sm:mt-0">Lihat profil</Link>
            </div>
        </section>
    </PublicLayout>
</template>
