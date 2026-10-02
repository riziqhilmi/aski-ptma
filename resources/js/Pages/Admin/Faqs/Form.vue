<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ faq: Object });
const form = useForm({
    question: props.faq?.question || '',
    answer: props.faq?.answer || '',
    is_published: props.faq?.is_published ?? true,
    sort_order: props.faq?.sort_order || 0,
});

const submit = () => {
    props.faq
        ? form.put(route('admin.faqs.update', props.faq.id))
        : form.post(route('admin.faqs.store'));
};
</script>

<template>
    <Head :title="faq ? 'Ubah FAQ' : 'Tambah FAQ'" />
    <AdminLayout>
        <Link :href="route('admin.faqs.index')" class="text-sm font-semibold text-aski-700">← Kembali</Link>
        <form class="card mt-6 max-w-3xl p-7" @submit.prevent="submit">
            <p class="text-sm font-semibold text-aski-700">FAQ</p>
            <h1 class="mt-1 text-3xl font-bold text-aski-900">{{ faq ? 'Ubah FAQ' : 'Tambah FAQ' }}</h1>

            <div class="mt-7 space-y-5">
                <label class="block text-sm font-semibold text-slate-700">Pertanyaan
                    <input v-model="form.question" class="mt-1 w-full rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500" />
                    <span class="text-xs text-red-600">{{ form.errors.question }}</span>
                </label>
                <label class="block text-sm font-semibold text-slate-700">Jawaban
                    <textarea v-model="form.answer" rows="8" class="mt-1 w-full rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500"></textarea>
                    <span class="text-xs text-red-600">{{ form.errors.answer }}</span>
                </label>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-semibold text-slate-700">Urutan
                        <input v-model="form.sort_order" type="number" min="0" class="mt-1 w-full rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500" />
                        <span class="text-xs text-red-600">{{ form.errors.sort_order }}</span>
                    </label>
                    <label class="flex items-center gap-3 pt-7 text-sm font-semibold text-slate-700">
                        <input v-model="form.is_published" type="checkbox" class="rounded border-slate-300 text-aski-700 focus:ring-aski-500" />
                        Terbitkan FAQ
                    </label>
                </div>
            </div>
            <button class="btn-primary mt-7">Simpan FAQ</button>
        </form>
    </AdminLayout>
</template>
