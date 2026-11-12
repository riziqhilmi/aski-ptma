<script setup>
import { Head } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import PaginationLinks from '@/Components/PaginationLinks.vue';
import { formatDateTime } from '@/utils/date';
defineProps({ documents: Object });
</script>

<template>
    <Head title="Sertifikat & Kartu Anggota" />
    <MemberLayout>
        <p class="text-sm font-semibold text-aski-700">KEANGGOTAAN</p>
        <h1 class="mt-1 text-3xl font-bold text-aski-900">Sertifikat & Kartu Anggota</h1>
        <p class="mt-2 text-slate-600">Dokumen resmi yang diterbitkan untuk keanggotaan Anda.</p>
        <div class="mt-7 grid gap-4">
            <article v-for="document in documents.data" :key="document.id" class="card flex flex-wrap items-center justify-between gap-4 p-5">
                <div><p class="font-bold text-aski-900">{{ document.snapshot.title }}</p><p class="mt-1 text-sm text-slate-500">{{ document.number }} · {{ formatDateTime(document.issued_at) }}</p></div>
                <div class="flex flex-wrap gap-2"><a class="rounded-lg border border-aski-200 px-4 py-2 text-sm font-semibold text-aski-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2" target="_blank" rel="noopener noreferrer" :href="route('member.membership-documents.preview', document.id)">Pratinjau</a><a class="btn-primary" :href="route('member.membership-documents.download', document.id)">Unduh PDF</a></div>
            </article>
            <p v-if="documents.data.length === 0" class="card p-6 text-slate-500">Dokumen keanggotaan akan muncul di sini setelah pembayaran diverifikasi.</p>
        </div>
        <PaginationLinks :links="documents.links" />
    </MemberLayout>
</template>
