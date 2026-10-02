<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-slate-50 lg:flex">
        <aside class="bg-aski-900 px-5 py-6 text-aski-100 lg:fixed lg:inset-y-0 lg:w-64">
            <Link :href="route('admin.dashboard')" class="flex items-center gap-3 text-lg font-bold text-white"><span class="grid h-9 w-9 place-items-center rounded-lg bg-gold-400 text-aski-900">A</span>ASKI-PTMA</Link>
            <p class="mt-1 text-xs font-medium uppercase tracking-widest text-gold-400">Panel Admin</p>
            <nav class="mt-10 space-y-2"><Link :href="route('admin.dashboard')" class="block rounded-lg bg-aski-700 px-4 py-3 text-sm font-semibold text-white">Dashboard</Link><span class="block rounded-lg px-4 py-3 text-sm text-aski-100/60">Menu pengelolaan segera hadir</span></nav>
        </aside>
        <div class="min-w-0 flex-1 lg:ml-64"><header class="flex items-center justify-between border-b border-aski-100 bg-white px-5 py-4 sm:px-8"><div><p class="text-sm text-slate-500">Selamat datang</p><p class="font-semibold text-aski-900">{{ $page.props.auth.user.name }}</p></div><Link :href="route('logout')" method="post" as="button" class="rounded-lg border border-aski-200 px-4 py-2 text-sm font-semibold text-aski-700 transition hover:bg-aski-50">Keluar</Link></header><main class="px-5 py-8 sm:px-8"><slot /></main></div>
    </div>
</template>
