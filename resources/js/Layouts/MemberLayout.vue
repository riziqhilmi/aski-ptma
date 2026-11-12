<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const page = usePage();
const mobileNavOpen = ref(false);
const navClass = (pattern) => [
    'flex min-h-10 items-center justify-between rounded-lg border-l-2 px-3 py-2 text-sm font-medium transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-400',
    route().current(pattern)
        ? 'border-gold-400 bg-white/10 font-semibold text-white ring-1 ring-inset ring-white/10'
        : 'border-transparent text-aski-100 hover:bg-white/5 hover:text-white',
];
const memberSections = [
    { id: 'summary', label: 'Ringkasan', routes: ['member.dashboard'] },
    { id: 'membership', label: 'Keanggotaan', routes: ['member.membership.*', 'member.applications.*', 'member.membership-documents.*'] },
    { id: 'finance', label: 'Keuangan', routes: ['member.invoices.*'] },
    { id: 'services', label: 'Layanan & Data', routes: ['member.intellectual-properties.*', 'member.documents.*', 'member.support.*'] },
    { id: 'account', label: 'Akun', routes: ['member.profile.*', 'member.settings.*'] },
];
const currentMemberSection = computed(() => {
    page.url;
    return memberSections.find((section) => section.routes.some((pattern) => route().current(pattern)))?.id;
});
const openMemberSections = ref(new Set(currentMemberSection.value ? [currentMemberSection.value] : ['summary']));
watch(currentMemberSection, (id) => {
    if (id) openMemberSections.value = new Set([...openMemberSections.value, id]);
});
const toggleMemberSection = (id) => {
    const next = new Set(openMemberSections.value);
    if (next.has(id)) next.delete(id);
    else next.add(id);
    openMemberSections.value = next;
};
const sectionButtonClass = 'flex min-h-10 w-full items-center justify-between rounded-lg px-3 py-2 text-left text-[11px] font-bold uppercase tracking-[0.12em] text-gold-400 transition-colors hover:bg-white/5 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-400';
</script>

<template>
    <div class="min-h-screen bg-slate-50 lg:flex">
        <aside class="bg-aski-900 px-4 py-4 text-aski-100 sm:px-5 sm:py-5 lg:fixed lg:inset-y-0 lg:w-72 lg:overflow-y-auto">
            <div class="flex items-center justify-between gap-3 border-b border-white/10 pb-4">
                <Link :href="route('member.dashboard')" class="flex min-w-0 items-center gap-3 rounded-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gold-400">
                    <img src="/images/logo-aski.png" alt="" class="h-11 w-11 shrink-0 rounded-full bg-white object-contain p-0.5" />
                    <span class="min-w-0"><span class="block truncate text-base font-bold leading-5 text-white">ASKI-PTMA</span><span class="mt-0.5 block text-xs font-medium text-aski-100/75">Portal Member</span></span>
                </Link>
                <button class="rounded-lg border border-white/15 px-3 py-2 text-xs font-semibold text-white transition hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-400 lg:hidden" :aria-expanded="mobileNavOpen" aria-controls="member-navigation" @click="mobileNavOpen = !mobileNavOpen">{{ mobileNavOpen ? 'Tutup' : 'Menu' }}</button>
            </div>
            <nav id="member-navigation" :class="[mobileNavOpen ? 'mt-5 block' : 'hidden lg:mt-6 lg:block', 'space-y-1']" aria-label="Navigasi Member">
                <section v-for="section in memberSections" :key="section.id">
                    <h2>
                        <button type="button" :class="sectionButtonClass" :aria-expanded="openMemberSections.has(section.id)" :aria-controls="`member-section-${section.id}`" @click="toggleMemberSection(section.id)">
                            <span>{{ section.label }}</span>
                            <svg aria-hidden="true" class="h-4 w-4 shrink-0 transition-transform" :class="openMemberSections.has(section.id) ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.22 7.47a.75.75 0 0 1 1.06 0L10 11.19l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.53a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
                        </button>
                    </h2>
                    <div v-show="openMemberSections.has(section.id)" :id="`member-section-${section.id}`" class="space-y-1 pb-2 pl-2">
                        <template v-if="section.id === 'summary'">
                            <Link :href="route('member.dashboard')" :class="navClass('member.dashboard')" :aria-current="route().current('member.dashboard') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Dashboard</span><span v-if="route().current('member.dashboard')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                        </template>
                        <template v-else-if="section.id === 'membership'">
                            <Link :href="route('member.membership.index')" :class="navClass('member.membership.*')" :aria-current="route().current('member.membership.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Ringkasan Keanggotaan</span><span v-if="route().current('member.membership.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                            <Link :href="route('member.applications.index')" :class="navClass('member.applications.*')" :aria-current="route().current('member.applications.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Permohonan & Perpanjangan</span><span v-if="route().current('member.applications.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                            <Link :href="route('member.membership-documents.index')" :class="navClass('member.membership-documents.*')" :aria-current="route().current('member.membership-documents.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Sertifikat & Kartu Anggota</span><span v-if="route().current('member.membership-documents.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                        </template>
                        <template v-else-if="section.id === 'finance'">
                            <Link :href="route('member.invoices.index')" :class="navClass('member.invoices.*')" :aria-current="route().current('member.invoices.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Tagihan & Pembayaran</span><span v-if="route().current('member.invoices.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                        </template>
                        <template v-else-if="section.id === 'services'">
                            <Link :href="route('member.intellectual-properties.index')" :class="navClass('member.intellectual-properties.*')" :aria-current="route().current('member.intellectual-properties.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Data HKI Saya</span><span v-if="route().current('member.intellectual-properties.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                            <Link :href="route('member.documents.index')" :class="navClass('member.documents.*')" :aria-current="route().current('member.documents.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Dokumen & Panduan</span><span v-if="route().current('member.documents.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                            <Link :href="route('member.support.index')" :class="navClass('member.support.*')" :aria-current="route().current('member.support.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Bantuan & FAQ</span><span v-if="route().current('member.support.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                        </template>
                        <template v-else-if="section.id === 'account'">
                            <Link :href="route('member.profile.edit')" :class="navClass('member.profile.*')" :aria-current="route().current('member.profile.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Profil Saya</span><span v-if="route().current('member.profile.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                            <Link :href="route('member.settings.edit')" :class="navClass('member.settings.*')" :aria-current="route().current('member.settings.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Pengaturan Akun</span><span v-if="route().current('member.settings.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                        </template>
                    </div>
                </section>
            </nav>
        </aside>
        <div class="min-w-0 flex-1 lg:ml-72">
            <header class="flex items-center justify-between border-b border-aski-100 bg-white px-5 py-4 sm:px-8"><div><p class="text-sm text-slate-500">Selamat datang</p><p class="font-semibold text-aski-900">{{ page.props.auth.user.name }}</p></div><Link :href="route('logout')" method="post" as="button" class="rounded-lg border border-aski-200 px-4 py-2 text-sm font-semibold text-aski-700 transition hover:bg-aski-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">Keluar</Link></header>
            <main class="px-5 py-8 sm:px-8"><div v-if="page.props.flash?.success" role="status" class="mb-6 rounded-lg border border-aski-200 bg-aski-50 px-4 py-3 text-sm font-medium text-aski-900">{{ page.props.flash.success }}</div><div v-if="page.props.flash?.error" role="alert" class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">{{ page.props.flash.error }}</div><slot /></main>
        </div>
    </div>
</template>
