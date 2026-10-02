<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
</script>

<template>
    <Head title="Beranda" />
    <PublicLayout>
        <section class="bg-gradient-to-br from-aski-50 via-white to-gold-100">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <p class="font-semibold uppercase tracking-[0.18em] text-aski-700">Asosiasi Sentra Kekayaan Intelektual</p>
                <h1 class="mt-5 max-w-3xl text-4xl font-bold tracking-tight text-aski-900 sm:text-6xl">Sistem Informasi ASKI-PTMA</h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">Platform pengelolaan keanggotaan dan data HKI Perguruan Tinggi Muhammadiyah dan 'Aisyiyah.</p>
                <Link :href="$page.props.auth.user ? route('dashboard') : route('login')" class="btn-primary mt-8 px-5 py-3">{{ $page.props.auth.user ? 'Buka Dashboard' : 'Masuk ke Sistem' }}</Link>
            </div>
        </section>
    </PublicLayout>
</template>
