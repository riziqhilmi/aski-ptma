<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-white text-slate-800">
        <header class="border-b border-aski-100 bg-white">
            <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <Link :href="route('home')" class="flex items-center gap-2 text-lg font-bold text-aski-800"><span class="grid h-9 w-9 place-items-center rounded-lg bg-aski-700 text-sm text-gold-100">A</span>ASKI-PTMA</Link>
                <div class="flex items-center gap-4 text-sm font-medium">
                    <Link :href="route('home')" class="hover:text-aski-700">Beranda</Link>
                    <Link :href="route('public.profile')" class="hover:text-aski-700">Profil</Link>
                    <Link :href="route('public.members.index')" class="hover:text-aski-700">Anggota</Link>
                    <Link :href="route('public.intellectual-properties.index')" class="hover:text-aski-700">Data HKI</Link>
                    <Link :href="route('public.articles.index')" class="hover:text-aski-700">Berita</Link>
                    <Link :href="route('public.contact')" class="hover:text-aski-700">Kontak</Link>
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="btn-primary">Dashboard</Link>
                    <Link v-else :href="route('login')" class="btn-primary">Login</Link>
                </div>
            </nav>
        </header>
        <main><slot /></main>
        <footer class="mt-12 border-t border-slate-200 bg-white py-6 text-center text-sm text-slate-500">© {{ new Date().getFullYear() }} ASKI-PTMA</footer>
    </div>
</template>
