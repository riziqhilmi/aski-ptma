<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ faq: Object });
const form = useForm({
    question: props.faq?.question || '',
    answer: props.faq?.answer || '',
    is_published: props.faq?.is_published ?? true,
    sort_order: props.faq?.sort_order ?? 0,
});

const submit = () => props.faq
    ? form.put(route('admin.faqs.update', props.faq.id))
    : form.post(route('admin.faqs.store'));
</script>

<template>
    <Head :title="faq ? 'Ubah FAQ' : 'Tambah FAQ'" />
    <AdminLayout>
        <div class="mx-auto max-w-4xl">
            <Link :href="route('admin.faqs.index')" class="inline-flex min-h-9 items-center text-sm font-semibold text-aski-700 hover:text-aski-900">← Kembali ke FAQ</Link>
            <header class="mt-5">
                <p class="text-sm font-semibold uppercase tracking-wide text-aski-700">Bantuan · FAQ</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">{{ faq ? 'Ubah FAQ' : 'Tambah FAQ' }}</h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">Kelola pertanyaan dan jawaban yang membantu anggota menemukan informasi dengan cepat.</p>
            </header>

            <form class="card mt-6 overflow-hidden" @submit.prevent="submit">
                <div class="border-b border-slate-100 px-5 py-4 sm:px-7">
                    <h2 class="font-semibold text-aski-900">Informasi FAQ</h2>
                    <p class="mt-1 text-sm text-slate-500">Kolom bertanda * wajib diisi.</p>
                </div>
                <div class="space-y-5 px-5 py-6 sm:px-7">
                    <div>
                        <label for="faq-question" class="text-sm font-semibold text-slate-800">Pertanyaan <span class="text-red-600">*</span></label>
                        <input id="faq-question" v-model="form.question" type="text" maxlength="255" required aria-required="true" :aria-invalid="Boolean(form.errors.question)" :aria-describedby="form.errors.question ? 'faq-question-error' : undefined" placeholder="Tuliskan pertanyaan yang sering diajukan" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 text-sm focus:border-aski-500 focus:ring-aski-500" />
                        <p v-if="form.errors.question" id="faq-question-error" class="mt-1.5 text-sm text-red-600">{{ form.errors.question }}</p>
                    </div>
                    <div>
                        <label for="faq-answer" class="text-sm font-semibold text-slate-800">Jawaban <span class="text-red-600">*</span></label>
                        <textarea id="faq-answer" v-model="form.answer" rows="8" required aria-required="true" :aria-invalid="Boolean(form.errors.answer)" :aria-describedby="form.errors.answer ? 'faq-answer-error' : undefined" placeholder="Berikan jawaban yang ringkas dan mudah dipahami" class="mt-2 w-full rounded-xl border-slate-300 text-sm leading-6 focus:border-aski-500 focus:ring-aski-500"></textarea>
                        <p v-if="form.errors.answer" id="faq-answer-error" class="mt-1.5 text-sm text-red-600">{{ form.errors.answer }}</p>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="faq-sort-order" class="text-sm font-semibold text-slate-800">Urutan tampil <span class="text-red-600">*</span></label>
                            <input id="faq-sort-order" v-model="form.sort_order" type="number" min="0" step="1" required aria-required="true" :aria-invalid="Boolean(form.errors.sort_order)" :aria-describedby="form.errors.sort_order ? 'faq-sort-order-error' : undefined" class="mt-2 min-h-11 w-full rounded-xl border-slate-300 text-sm focus:border-aski-500 focus:ring-aski-500" />
                            <p v-if="form.errors.sort_order" id="faq-sort-order-error" class="mt-1.5 text-sm text-red-600">{{ form.errors.sort_order }}</p>
                        </div>
                        <label class="flex min-h-11 items-center gap-3 self-end rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-800">
                            <input v-model="form.is_published" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-aski-700 focus:ring-aski-500" />
                            Terbitkan FAQ
                        </label>
                    </div>
                </div>
                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50/70 px-5 py-4 sm:flex-row sm:justify-end sm:px-7">
                    <Link :href="route('admin.faqs.index')" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-700">Batal</Link>
                    <button type="submit" class="btn-primary inline-flex min-h-11 items-center justify-center" :disabled="form.processing">{{ form.processing ? 'Menyimpan…' : 'Simpan FAQ' }}</button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
