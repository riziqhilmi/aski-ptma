<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    records: Object,
    filters: Object,
    summary: Object,
    types: Array,
});

const form = ref({ ...props.filters });
const submit = () => router.get(route('public.intellectual-properties.index'), form.value, { preserveState: true });
</script>

<template>
    <Head title="Data HKI" />
    <PublicLayout>
        <section class="bg-aski-50">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <p class="text-sm font-semibold text-aski-700">DATA HKI</p>
                <h1 class="mt-2 text-4xl font-bold text-aski-900">Statistik dan Direktori HKI</h1>
                <p class="mt-3 max-w-2xl text-slate-600">Ringkasan data HKI yang dicatat oleh member ASKI-PTMA.</p>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="card p-5"><p class="text-sm text-slate-500">Total HKI</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.total }}</p></div>
                <div class="card p-5"><p class="text-sm text-slate-500">Jenis HKI</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.types }}</p></div>
                <div class="card p-5"><p class="text-sm text-slate-500">Kontributor</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.members }}</p></div>
            </div>

            <form class="card my-7 flex flex-wrap gap-3 p-5" @submit.prevent="submit">
                <input v-model="form.search" class="rounded-lg border-slate-300" placeholder="Cari judul, pencipta, pemegang hak" />
                <select v-model="form.type" class="rounded-lg border-slate-300">
                    <option value="">Semua jenis</option>
                    <option v-for="type in types" :key="type" :value="type">{{ type }}</option>
                </select>
                <button class="btn-primary" type="submit">Cari</button>
            </form>

            <div class="card overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-aski-50 text-aski-900">
                        <tr>
                            <th class="p-4">Judul</th>
                            <th class="p-4">Jenis</th>
                            <th class="p-4">Pencipta</th>
                            <th class="p-4">Pemegang Hak</th>
                            <th class="p-4">Tanggal Permohonan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="record in records.data" :key="record.id" class="border-t border-aski-100">
                            <td class="p-4 font-semibold text-aski-900">{{ record.title }}</td>
                            <td class="p-4">{{ record.ip_type }}</td>
                            <td class="p-4">{{ record.creator }}</td>
                            <td class="p-4">{{ record.rights_holder }}</td>
                            <td class="p-4">{{ record.application_date }}</td>
                        </tr>
                        <tr v-if="records.data.length === 0">
                            <td colspan="5" class="p-8 text-center text-slate-500">Belum ada data HKI.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="records.links?.length > 3" class="mt-8 flex flex-wrap gap-2">
                <Link v-for="link in records.links" :key="link.label" :href="link.url || '#'" v-html="link.label" class="rounded-lg border px-3 py-2 text-sm" :class="link.active ? 'border-aski-700 bg-aski-700 text-white' : 'border-aski-100 bg-white text-aski-700'" />
            </div>
        </section>
    </PublicLayout>
</template>
