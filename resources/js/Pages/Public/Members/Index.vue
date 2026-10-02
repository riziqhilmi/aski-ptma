<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    members: Object,
    filters: Object,
});

const form = ref({ ...props.filters });
const submit = () => router.get(route('public.members.index'), form.value, { preserveState: true });
</script>

<template>
    <Head title="Anggota" />
    <PublicLayout>
        <section class="bg-aski-50">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <p class="text-sm font-semibold text-aski-700">ANGGOTA</p>
                <h1 class="mt-2 text-4xl font-bold text-aski-900">Direktori Anggota ASKI-PTMA</h1>
                <p class="mt-3 max-w-2xl text-slate-600">Daftar anggota aktif yang telah menyelesaikan proses keanggotaan.</p>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <form class="card mb-7 flex flex-wrap gap-3 p-5" @submit.prevent="submit">
                <input v-model="form.search" class="rounded-lg border-slate-300" placeholder="Cari nama atau institusi" />
                <select v-model="form.type" class="rounded-lg border-slate-300">
                    <option value="">Semua tipe</option>
                    <option value="individual">Individu</option>
                    <option value="institution">Institusi</option>
                </select>
                <button class="btn-primary" type="submit">Cari</button>
            </form>

            <div class="grid gap-5 md:grid-cols-3">
                <div v-for="membership in members.data" :key="membership.id" class="card p-6">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gold-600">{{ membership.user.member_profile?.account_type === 'institution' ? 'Institusi' : 'Individu' }}</p>
                    <h2 class="mt-2 text-xl font-bold text-aski-900">{{ membership.user.member_profile?.institution_name || membership.user.name }}</h2>
                    <p class="mt-2 text-sm text-slate-500">Aktif sampai {{ membership.ends_at }}</p>
                </div>
                <div v-if="members.data.length === 0" class="card p-8 text-center text-slate-500 md:col-span-3">Belum ada anggota aktif yang dapat ditampilkan.</div>
            </div>
            <div v-if="members.links?.length > 3" class="mt-8 flex flex-wrap gap-2">
                <Link v-for="link in members.links" :key="link.label" :href="link.url || '#'" v-html="link.label" class="rounded-lg border px-3 py-2 text-sm" :class="link.active ? 'border-aski-700 bg-aski-700 text-white' : 'border-aski-100 bg-white text-aski-700'" />
            </div>
        </section>
    </PublicLayout>
</template>
