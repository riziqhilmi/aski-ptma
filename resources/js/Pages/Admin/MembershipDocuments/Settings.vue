<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
const props = defineProps({ settings: Array });
const forms = Object.fromEntries(props.settings.map((setting) => [setting.id, useForm({ title: setting.title, number_prefix: setting.number_prefix, background_color: setting.background_color, accent_color: setting.accent_color })]));
</script>
<template>
    <Head title="Format Dokumen" />
    <AdminLayout>
        <h1 class="text-3xl font-bold text-aski-900">Format Dokumen Keanggotaan</h1>
        <div v-for="setting in settings" :key="setting.id" class="card mt-6 max-w-2xl p-6">
            <h2 class="font-bold text-aski-900">{{ setting.type === 'certificate' ? 'Sertifikat' : 'Kartu Anggota' }}</h2>
            <form class="mt-4 grid gap-4 sm:grid-cols-2" @submit.prevent="forms[setting.id].put(route('admin.membership-documents.settings.update', setting.id))">
                <div>
                    <label class="block text-sm font-medium">Judul <span aria-hidden="true">(wajib)</span>
                        <input v-model="forms[setting.id].title" required aria-required="true" :aria-invalid="Boolean(forms[setting.id].errors.title)" class="mt-1 w-full rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500" />
                    </label>
                    <InputError :message="forms[setting.id].errors.title" class="mt-1" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Prefix nomor <span aria-hidden="true">(wajib)</span>
                        <input v-model="forms[setting.id].number_prefix" required aria-required="true" :aria-invalid="Boolean(forms[setting.id].errors.number_prefix)" class="mt-1 w-full rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500" />
                    </label>
                    <InputError :message="forms[setting.id].errors.number_prefix" class="mt-1" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Warna latar <span aria-hidden="true">(wajib)</span>
                        <input v-model="forms[setting.id].background_color" required aria-required="true" :aria-invalid="Boolean(forms[setting.id].errors.background_color)" class="mt-1 w-full rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500" placeholder="#RRGGBB" />
                    </label>
                    <InputError :message="forms[setting.id].errors.background_color" class="mt-1" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Warna aksen <span aria-hidden="true">(wajib)</span>
                        <input v-model="forms[setting.id].accent_color" required aria-required="true" :aria-invalid="Boolean(forms[setting.id].errors.accent_color)" class="mt-1 w-full rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500" placeholder="#RRGGBB" />
                    </label>
                    <InputError :message="forms[setting.id].errors.accent_color" class="mt-1" />
                </div>
                <button class="btn-primary sm:col-span-2" :disabled="forms[setting.id].processing">{{ forms[setting.id].processing ? 'Menyimpan…' : 'Simpan' }}</button>
            </form>
        </div>
    </AdminLayout>
</template>
