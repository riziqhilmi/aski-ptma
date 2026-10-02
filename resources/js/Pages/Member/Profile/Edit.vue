<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';

const props = defineProps({ user: Object, profile: Object });
const form = useForm({
    name: props.user.name,
    email: props.user.email,
    account_type: props.profile.account_type || '',
    phone: props.profile.phone || '',
    address: props.profile.address || '',
    institution_name: props.profile.institution_name || '',
    responsible_name: props.profile.responsible_name || '',
    responsible_position: props.profile.responsible_position || '',
});

const submit = () => form.put(route('member.profile.update'));
</script>

<template>
    <Head title="Profil Saya" />
    <MemberLayout>
        <div class="mb-7 flex flex-wrap items-center justify-between gap-3"><div><p class="text-sm font-semibold text-aski-700">PENGATURAN AKUN</p><h1 class="mt-1 text-3xl font-bold text-aski-900">Profil Saya</h1></div><span class="rounded-full px-3 py-1 text-sm font-semibold" :class="profile.completion_status === 'lengkap' ? 'bg-aski-100 text-aski-800' : 'bg-gold-100 text-gold-600'">{{ profile.completion_status === 'lengkap' ? 'Profil lengkap' : 'Profil belum lengkap' }}</span></div>
        <form class="card max-w-3xl p-6 sm:p-8" @submit.prevent="submit">
            <div class="grid gap-5 sm:grid-cols-2"><label class="text-sm font-medium text-slate-700">Nama<input v-model="form.name" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500" /><span class="mt-1 block text-xs text-red-600">{{ form.errors.name }}</span></label><label class="text-sm font-medium text-slate-700">Email<input v-model="form.email" type="email" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500" /><span class="mt-1 block text-xs text-red-600">{{ form.errors.email }}</span></label></div>
            <fieldset class="mt-7"><legend class="text-sm font-semibold text-slate-800">Tipe akun</legend><div class="mt-3 flex flex-wrap gap-3"><label class="cursor-pointer rounded-lg border px-4 py-3" :class="form.account_type === 'individual' ? 'border-aski-600 bg-aski-50 text-aski-800' : 'border-slate-200'"><input v-model="form.account_type" value="individual" type="radio" class="text-aski-700 focus:ring-aski-500" /> Individu</label><label class="cursor-pointer rounded-lg border px-4 py-3" :class="form.account_type === 'institution' ? 'border-aski-600 bg-aski-50 text-aski-800' : 'border-slate-200'"><input v-model="form.account_type" value="institution" type="radio" class="text-aski-700 focus:ring-aski-500" /> Institusi</label></div><span class="mt-1 block text-xs text-red-600">{{ form.errors.account_type }}</span></fieldset>
            <div v-if="form.account_type === 'institution'" class="mt-6 grid gap-5 sm:grid-cols-2"><label class="text-sm font-medium">Nama institusi<input v-model="form.institution_name" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500" /><span class="text-xs text-red-600">{{ form.errors.institution_name }}</span></label><label class="text-sm font-medium">Nama penanggung jawab<input v-model="form.responsible_name" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500" /><span class="text-xs text-red-600">{{ form.errors.responsible_name }}</span></label><label class="text-sm font-medium">Jabatan penanggung jawab<input v-model="form.responsible_position" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500" /></label></div>
            <div class="mt-6 grid gap-5 sm:grid-cols-2"><label class="text-sm font-medium">Nomor telepon<input v-model="form.phone" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500" /><span class="text-xs text-red-600">{{ form.errors.phone }}</span></label><label class="text-sm font-medium sm:col-span-2">Alamat<textarea v-model="form.address" rows="4" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500" /><span class="text-xs text-red-600">{{ form.errors.address }}</span></label></div>
            <button class="btn-primary mt-7" :disabled="form.processing">Simpan Profil</button>
        </form>
    </MemberLayout>
</template>
