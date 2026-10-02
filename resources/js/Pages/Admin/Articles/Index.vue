<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    articles: Object,
    filters: Object,
});

const filter = (event) => router.get(route('admin.articles.index'), { status: event.target.value }, {
    preserveState: true,
    preserveScroll: true,
});
</script>

<template>
    <Head title="Berita" />
    <AdminLayout>
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-aski-700">BERITA</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">Kelola Artikel Berita</h1>
            </div>
            <Link :href="route('admin.articles.create')" class="btn-primary">Tambah Artikel</Link>
        </div>

        <div class="card mt-7 p-5">
            <select :value="filters.status" class="rounded-lg border-slate-300" @change="filter">
                <option value="">Semua status</option>
                <option value="draft">Draft</option>
                <option value="published">Terbit</option>
            </select>
        </div>

        <div class="card mt-6 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-aski-50 text-aski-900">
                    <tr>
                        <th class="p-4">Judul</th>
                        <th class="p-4">Penulis</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Publikasi</th>
                        <th class="p-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="article in articles.data" :key="article.id" class="border-t border-aski-100">
                        <td class="p-4 font-semibold text-aski-900">{{ article.title }}</td>
                        <td class="p-4">{{ article.author?.name }}</td>
                        <td class="p-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="article.status === 'published' ? 'bg-aski-50 text-aski-700' : 'bg-gold-100 text-gold-600'">{{ article.status_label }}</span>
                        </td>
                        <td class="p-4">{{ article.published_at || '-' }}</td>
                        <td class="p-4"><Link :href="route('admin.articles.show', article.id)" class="font-semibold text-aski-700 hover:text-aski-900">Detail</Link></td>
                    </tr>
                    <tr v-if="articles.data.length === 0">
                        <td colspan="5" class="p-8 text-center text-slate-500">Belum ada artikel.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="articles.links?.length > 3" class="mt-5 flex flex-wrap gap-2">
            <Link v-for="link in articles.links" :key="link.label" :href="link.url || '#'" v-html="link.label" class="rounded-lg border px-3 py-2 text-sm" :class="link.active ? 'border-aski-700 bg-aski-700 text-white' : 'border-aski-100 bg-white text-aski-700'" />
        </div>
    </AdminLayout>
</template>
