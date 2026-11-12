<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatDateTime } from '@/utils/date';

defineProps({ latestArticles: Array });
</script>

<template>
    <Head title="Beranda" />
    <PublicLayout>
        <section class="relative isolate overflow-hidden bg-aski-900 text-white">
            <div aria-hidden="true" class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top_right,rgba(25,135,84,0.4),transparent_55%)]"></div>
            <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 sm:px-6 sm:py-20 lg:grid-cols-[1.15fr_0.85fr] lg:px-8 lg:py-24">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.15em] text-aski-100">
                        <span class="h-2 w-2 rounded-full bg-gold-400"></span>
                        Asosiasi Sentra Kekayaan Intelektual
                    </div>
                    <h1 class="mt-6 max-w-3xl text-4xl font-bold leading-tight tracking-tight sm:text-5xl lg:text-6xl">Menguatkan kolaborasi dan pengelolaan kekayaan intelektual PTMA</h1>
                    <p class="mt-6 max-w-2xl text-base leading-7 text-aski-100 sm:text-lg sm:leading-8">Sistem informasi ASKI-PTMA untuk mendukung layanan keanggotaan dan pencatatan data kekayaan intelektual Perguruan Tinggi Muhammadiyah dan 'Aisyiyah.</p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <Link :href="$page.props.auth.user ? route('dashboard') : route('login')" class="inline-flex min-h-12 items-center justify-center rounded-lg bg-gold-400 px-5 py-3 text-sm font-bold text-aski-900 shadow-sm transition hover:bg-gold-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">{{ $page.props.auth.user ? 'Buka Dashboard' : 'Masuk ke Sistem' }}</Link>
                        <Link :href="route('public.profile')" class="inline-flex min-h-12 items-center justify-center rounded-lg border border-white/30 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">Kenali ASKI-PTMA</Link>
                    </div>
                </div>
                <div class="relative mx-auto w-full max-w-md">
                    <div class="absolute -inset-3 rotate-2 rounded-[2rem] border border-gold-400/40"></div>
                    <div class="relative overflow-hidden rounded-[1.75rem] border border-white/15 bg-white p-6 text-aski-900 shadow-2xl sm:p-8">
                        <div class="flex items-center gap-4 border-b border-slate-100 pb-6">
                            <img src="/images/logo-aski.png" alt="Logo ASKI-PTMA" class="h-16 w-16 shrink-0 object-contain sm:h-20 sm:w-20" />
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.16em] text-aski-700">Sistem Informasi</p>
                                <p class="mt-1 text-xl font-bold tracking-tight sm:text-2xl">ASKI-PTMA</p>
                                <p class="mt-1 text-sm text-slate-500">Layanan organisasi dalam satu platform</p>
                            </div>
                        </div>
                        <div class="mt-5 grid gap-3">
                            <Link :href="route('public.members.index')" class="group flex items-center justify-between rounded-xl border border-slate-200 p-4 transition hover:border-aski-300 hover:bg-aski-50">
                                <span><span class="block text-sm font-bold text-aski-900">Keanggotaan</span><span class="mt-1 block text-xs text-slate-500">Kenali anggota ASKI-PTMA</span></span>
                                <span aria-hidden="true" class="text-lg text-aski-700 transition group-hover:translate-x-1">→</span>
                            </Link>
                            <Link :href="route('public.intellectual-properties.index')" class="group flex items-center justify-between rounded-xl border border-slate-200 p-4 transition hover:border-aski-300 hover:bg-aski-50">
                                <span><span class="block text-sm font-bold text-aski-900">Direktori Data HKI</span><span class="mt-1 block text-xs text-slate-500">Lihat data yang dipublikasikan</span></span>
                                <span aria-hidden="true" class="text-lg text-aski-700 transition group-hover:translate-x-1">→</span>
                            </Link>
                            <Link :href="route('public.articles.index')" class="group flex items-center justify-between rounded-xl border border-slate-200 p-4 transition hover:border-aski-300 hover:bg-aski-50">
                                <span><span class="block text-sm font-bold text-aski-900">Informasi dan Berita</span><span class="mt-1 block text-xs text-slate-500">Ikuti kabar terbaru organisasi</span></span>
                                <span aria-hidden="true" class="text-lg text-aski-700 transition group-hover:translate-x-1">→</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-16 lg:px-8">
            <div class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-aski-700">Kabar organisasi</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-tight text-aski-900 sm:text-3xl">Berita terbaru</h2>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-slate-600">Informasi dan pembaruan dari ASKI-PTMA.</p>
                </div>
                <Link :href="route('public.articles.index')" class="inline-flex min-h-10 items-center gap-2 self-start rounded-lg px-3 text-sm font-semibold text-aski-700 transition hover:bg-aski-50 hover:text-aski-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700 sm:self-auto">Lihat semua berita <span aria-hidden="true">→</span></Link>
            </div>
            <div class="mt-7 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                <Link v-for="article in latestArticles" :key="article.id" :href="route('public.articles.show', article.slug)" class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-aski-200 hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
                    <img v-if="article.cover_image_url" :src="article.cover_image_url" :alt="article.title" class="h-48 w-full object-cover transition duration-300 group-hover:scale-[1.02]" />
                    <div v-else class="grid h-48 place-items-center bg-aski-50"><img src="/images/logo-aski.png" alt="" class="h-20 w-20 object-contain" /></div>
                    <div class="p-5 sm:p-6">
                        <p class="text-xs font-semibold uppercase tracking-wider text-aski-700">{{ formatDateTime(article.published_at) }}</p>
                        <h3 class="mt-2 line-clamp-2 text-lg font-bold leading-7 text-aski-900">{{ article.title }}</h3>
                        <p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-600">{{ article.excerpt }}</p>
                        <span class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-aski-700">Baca berita <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span></span>
                    </div>
                </Link>
                <div v-if="latestArticles.length === 0" class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center md:col-span-2 lg:col-span-3">
                    <p class="font-semibold text-aski-900">Belum ada berita terbit</p>
                    <p class="mt-1 text-sm text-slate-500">Informasi terbaru akan tampil di sini.</p>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
