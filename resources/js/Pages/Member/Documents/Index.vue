<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';

const props = defineProps({
    documents: Object,
    filters: Object,
    categories: Array,
});

const filter = (event) => {
    router.get(route('member.documents.index'), { ...props.filters, category: event.target.value }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Dokumen" />
    <MemberLayout>
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-aski-700">DOKUMEN DAN PANDUAN</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">Dokumen Member</h1>
            </div>
            <select :value="filters.category" class="rounded-lg border-slate-300" @change="filter">
                <option value="">Semua kategori</option>
                <option v-for="category in categories" :key="category.value" :value="category.value">{{ category.label }}</option>
            </select>
        </div>

        <div class="mt-7 grid gap-4">
            <div v-for="document in documents.data" :key="document.id" class="card flex flex-wrap items-center justify-between gap-4 p-6">
                <div>
                    <span class="rounded-full bg-aski-50 px-3 py-1 text-xs font-semibold text-aski-700">{{ document.category_label }}</span>
                    <h2 class="mt-3 text-lg font-bold text-aski-900">{{ document.title }}</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-500">{{ document.description || 'Dokumen tersedia untuk diunduh.' }}</p>
                </div>
                <a :href="route('member.documents.download', document.id)" class="btn-primary">Download PDF</a>
            </div>
            <div v-if="documents.data.length === 0" class="card p-8 text-center text-slate-500">Belum ada dokumen terbit.</div>
        </div>

        <div v-if="documents.links?.length > 3" class="mt-5 flex flex-wrap gap-2">
            <Link v-for="link in documents.links" :key="link.label" :href="link.url || '#'" v-html="link.label" class="rounded-lg border px-3 py-2 text-sm" :class="link.active ? 'border-aski-700 bg-aski-700 text-white' : 'border-aski-100 bg-white text-aski-700'" />
        </div>
    </MemberLayout>
</template>
