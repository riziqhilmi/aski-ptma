<script setup>
import { Head, router } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import PaginationLinks from '@/Components/PaginationLinks.vue';
import { formatDateTime } from '@/utils/date.js';

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

const fileSize = (bytes) => {
    const size = Number(bytes || 0);
    if (!size) return 'Ukuran tidak tersedia';
    if (size < 1024 * 1024) return `${Math.max(1, Math.round(size / 1024))} KB`;
    return `${(size / (1024 * 1024)).toFixed(1)} MB`;
};

const fileType = (document) => {
    const extension = String(document.original_filename || '').split('.').pop();
    return extension && extension !== document.original_filename ? extension.toUpperCase() : 'FILE';
};
</script>

<template>
    <Head title="Dokumen Member" />
    <MemberLayout>
        <div class="space-y-6">
            <header class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-aski-700">Pusat Dokumen</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-aski-900 sm:text-3xl">Dokumen Member</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Temukan dan unduh pedoman, notifikasi, serta template dokumen yang diterbitkan untuk anggota.</p>
                </div>
                <div class="flex w-full items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:w-auto">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-aski-50 text-aski-800" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M8 4.75h6.5L19 9.25v9A1.75 1.75 0 0 1 17.25 20h-10A1.75 1.75 0 0 1 5.5 18.25v-11A2.5 2.5 0 0 1 8 4.75Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 5v4.5h4.5M8.5 13h7m-7 3h7"/></svg>
                    </span>
                    <div>
                        <p class="text-xs text-slate-500">Dokumen tersedia</p>
                        <p class="text-lg font-bold leading-5 text-aski-900">{{ documents.total }}</p>
                    </div>
                </div>
            </header>

            <section class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:px-5" aria-label="Filter dokumen">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Jelajahi dokumen</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Pilih kategori untuk mempersempit daftar.</p>
                </div>
                <div class="w-full sm:w-64">
                    <label class="sr-only" for="member-document-category-filter">Filter kategori dokumen</label>
                    <select id="member-document-category-filter" :value="filters.category" class="min-h-11 w-full rounded-lg border-slate-300 bg-white text-sm text-slate-800 focus:border-aski-500 focus:ring-aski-500" @change="filter">
                        <option value="">Semua kategori</option>
                        <option v-for="category in categories" :key="category.value" :value="category.value">{{ category.label }}</option>
                    </select>
                </div>
            </section>

            <section aria-labelledby="document-list-heading">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <h2 id="document-list-heading" class="text-base font-semibold text-aski-900">{{ filters.category ? 'Hasil kategori terpilih' : 'Dokumen terbaru' }}</h2>
                    <p class="text-xs text-slate-500">{{ documents.from || 0 }}–{{ documents.to || 0 }} dari {{ documents.total }}</p>
                </div>

                <div v-if="documents.data.length" class="grid gap-4 lg:grid-cols-2">
                    <article v-for="document in documents.data" :key="document.id" class="flex min-w-0 flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-aski-200 hover:shadow-md sm:p-6">
                        <div class="flex items-start gap-4">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-aski-50 text-aski-800" aria-hidden="true">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M8 4.75h6.5L19 9.25v9A1.75 1.75 0 0 1 17.25 20h-10A1.75 1.75 0 0 1 5.5 18.25v-11A2.5 2.5 0 0 1 8 4.75Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 5v4.5h4.5M8.5 13h7m-7 3h7"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <span class="inline-flex max-w-full rounded-full bg-aski-50 px-2.5 py-1 text-xs font-semibold text-aski-800">{{ document.category_label }}</span>
                                <h3 class="mt-2 break-words text-base font-semibold leading-6 text-slate-900">{{ document.title }}</h3>
                            </div>
                        </div>

                        <p class="mt-4 line-clamp-3 text-sm leading-6 text-slate-600">{{ document.description || 'Dokumen resmi yang diterbitkan untuk anggota.' }}</p>

                        <div class="mt-5 flex flex-wrap gap-x-4 gap-y-2 border-t border-slate-100 pt-4 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 font-bold text-slate-600">{{ fileType(document) }}</span>
                                <span class="max-w-[180px] truncate" :title="document.original_filename">{{ document.original_filename }}</span>
                            </span>
                            <span>{{ fileSize(document.file_size) }}</span>
                            <time v-if="document.published_at" :datetime="document.published_at">Terbit {{ formatDateTime(document.published_at) }}</time>
                        </div>

                        <div class="mt-5 flex justify-end">
                            <a :href="route('member.documents.download', document.id)" class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-lg bg-aski-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-aski-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700 sm:w-auto">
                                <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2.75a.75.75 0 0 1 .75.75v7.69l2.22-2.22a.75.75 0 1 1 1.06 1.06l-3.5 3.5a.75.75 0 0 1-1.06 0l-3.5-3.5a.75.75 0 1 1 1.06-1.06l2.22 2.22V3.5a.75.75 0 0 1 .75-.75ZM3.5 13.25a.75.75 0 0 1 .75.75v1.5c0 .41.34.75.75.75h10c.41 0 .75-.34.75-.75V14a.75.75 0 0 1 1.5 0v1.5A2.25 2.25 0 0 1 15 17.75H5A2.25 2.25 0 0 1 2.75 15.5V14a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/></svg>
                                Unduh dokumen
                            </a>
                        </div>
                    </article>
                </div>

                <div v-else class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-14 text-center shadow-sm sm:px-8">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-aski-50 text-aski-700" aria-hidden="true">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M8 4.75h6.5L19 9.25v9A1.75 1.75 0 0 1 17.25 20h-10A1.75 1.75 0 0 1 5.5 18.25v-11A2.5 2.5 0 0 1 8 4.75Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 5v4.5h4.5M8.5 13h7m-7 3h7"/></svg>
                    </span>
                    <h3 class="mt-4 text-base font-semibold text-slate-900">{{ filters.category ? 'Belum ada dokumen pada kategori ini' : 'Belum ada dokumen terbit' }}</h3>
                    <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500">Dokumen yang diterbitkan oleh pengelola akan muncul di sini.</p>
                    <button v-if="filters.category" type="button" class="mt-4 rounded-lg px-3 py-2 text-sm font-semibold text-aski-800 hover:bg-aski-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700" @click="router.get(route('member.documents.index'))">Tampilkan semua kategori</button>
                </div>
            </section>

            <PaginationLinks :links="documents.links" />
        </div>
    </MemberLayout>
</template>
