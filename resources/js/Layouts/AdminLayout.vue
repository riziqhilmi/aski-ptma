<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, watch, nextTick, computed } from 'vue';

const page = usePage();
const mobileNavOpen = ref(false);
const showErrorPopup = ref(false);
const errorMessage = ref('');
const closeErrorPopup = () => { showErrorPopup.value = false; };
watch(() => page.props.flash?.error, async (message) => {
    if (!message) return;
    errorMessage.value = message;
    showErrorPopup.value = true;
    await nextTick();
    document.getElementById('admin-error-popup-close')?.focus();
}, { immediate: true });
const handlePopupKeydown = (event) => {
    if (event.key === 'Escape') closeErrorPopup();
};
const navClass = (pattern) => [
    'flex min-h-10 items-center justify-between rounded-lg border-l-2 px-3 py-2 text-sm font-medium transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-400',
    route().current(pattern)
        ? 'border-gold-400 bg-white/10 font-semibold text-white ring-1 ring-inset ring-white/10'
        : 'border-transparent text-aski-100 hover:bg-white/5 hover:text-white',
];
const adminSections = [
    { id: 'summary', label: 'Ringkasan', routes: ['admin.dashboard'] },
    { id: 'membership', label: 'Keanggotaan', routes: ['admin.accounts.*', 'admin.applications.*', 'admin.membership-documents.index', 'admin.membership-documents.settings*'] },
    { id: 'finance', label: 'Keuangan', routes: ['admin.rates.*', 'admin.invoices.*', 'admin.payments.*', 'admin.payment-settings.*'] },
    { id: 'ip', label: 'Kekayaan Intelektual', routes: ['admin.intellectual-properties.*'] },
    { id: 'publishing', label: 'Publikasi', routes: ['admin.documents.*', 'admin.articles.*', 'admin.site-contents.*', 'admin.organization-members.*'] },
    { id: 'help', label: 'Bantuan', routes: ['admin.faqs.*', 'admin.support-tickets.*'] },
    { id: 'reports', label: 'Laporan & Pengaturan', routes: ['admin.reports.*'] },
];
const currentAdminSection = computed(() => {
    page.url;
    return adminSections.find((section) => section.routes.some((pattern) => route().current(pattern)))?.id;
});
const openAdminSections = ref(new Set(currentAdminSection.value ? [currentAdminSection.value] : ['summary']));
watch(currentAdminSection, (id) => {
    if (id) openAdminSections.value = new Set([...openAdminSections.value, id]);
});
const toggleAdminSection = (id) => {
    const next = new Set(openAdminSections.value);
    if (next.has(id)) next.delete(id);
    else next.add(id);
    openAdminSections.value = next;
};
const sectionButtonClass = 'flex min-h-10 w-full items-center justify-between rounded-lg px-3 py-2 text-left text-[11px] font-bold uppercase tracking-[0.12em] text-gold-400 transition-colors hover:bg-white/5 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-400';
</script>

<template>
    <div class="min-h-screen bg-slate-50 lg:flex">
        <aside class="bg-aski-900 px-4 py-4 text-aski-100 sm:px-5 sm:py-5 lg:fixed lg:inset-y-0 lg:w-72 lg:overflow-y-auto">
            <div class="flex items-center justify-between gap-3 border-b border-white/10 pb-4">
                <Link :href="route('admin.dashboard')" class="flex min-w-0 items-center gap-3 rounded-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gold-400">
                    <img src="/images/logo-aski.png" alt="" class="h-11 w-11 shrink-0 rounded-full bg-white object-contain p-0.5" />
                    <span class="min-w-0"><span class="block truncate text-base font-bold leading-5 text-white">ASKI-PTMA</span><span class="mt-0.5 block text-xs font-medium text-aski-100/75">Panel Admin</span></span>
                </Link>
                <button class="rounded-lg border border-white/15 px-3 py-2 text-xs font-semibold text-white transition hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-400 lg:hidden" :aria-expanded="mobileNavOpen" aria-controls="admin-navigation" @click="mobileNavOpen = !mobileNavOpen">{{ mobileNavOpen ? 'Tutup' : 'Menu' }}</button>
            </div>
            <nav id="admin-navigation" :class="[mobileNavOpen ? 'mt-5 block' : 'hidden lg:mt-6 lg:block', 'space-y-1']" aria-label="Navigasi Admin">
                <section v-for="section in adminSections" :key="section.id">
                    <h2>
                        <button type="button" :class="sectionButtonClass" :aria-expanded="openAdminSections.has(section.id)" :aria-controls="`admin-section-${section.id}`" @click="toggleAdminSection(section.id)">
                            <span>{{ section.label }}</span>
                            <svg aria-hidden="true" class="h-4 w-4 shrink-0 transition-transform" :class="openAdminSections.has(section.id) ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.22 7.47a.75.75 0 0 1 1.06 0L10 11.19l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.53a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
                        </button>
                    </h2>
                    <div v-show="openAdminSections.has(section.id)" :id="`admin-section-${section.id}`" class="space-y-1 pb-2 pl-2">
                        <template v-if="section.id === 'summary'">
                            <Link :href="route('admin.dashboard')" :class="navClass('admin.dashboard')" :aria-current="route().current('admin.dashboard') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Dashboard</span><span v-if="route().current('admin.dashboard')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                        </template>
                        <template v-else-if="section.id === 'membership'">
                            <Link :href="route('admin.accounts.index')" :class="navClass('admin.accounts.*')" :aria-current="route().current('admin.accounts.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Manajemen Akun</span><span v-if="route().current('admin.accounts.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                            <Link :href="route('admin.applications.index')" :class="navClass('admin.applications.*')" :aria-current="route().current('admin.applications.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Permohonan</span><span v-if="route().current('admin.applications.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                            <Link :href="route('admin.membership-documents.index')" :class="navClass('admin.membership-documents.index')" :aria-current="route().current('admin.membership-documents.index') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Dokumen Keanggotaan</span><span v-if="route().current('admin.membership-documents.index')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                            <Link :href="route('admin.membership-documents.settings')" :class="navClass('admin.membership-documents.settings*')" :aria-current="route().current('admin.membership-documents.settings*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Format Sertifikat & Kartu</span><span v-if="route().current('admin.membership-documents.settings*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                        </template>
                        <template v-else-if="section.id === 'finance'">
                            <Link :href="route('admin.rates.index')" :class="navClass('admin.rates.*')" :aria-current="route().current('admin.rates.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Tarif Keanggotaan</span><span v-if="route().current('admin.rates.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                            <Link :href="route('admin.invoices.index')" :class="navClass('admin.invoices.*')" :aria-current="route().current('admin.invoices.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Tagihan</span><span v-if="route().current('admin.invoices.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                            <Link :href="route('admin.payments.index')" :class="navClass('admin.payments.*')" :aria-current="route().current('admin.payments.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Pembayaran</span><span v-if="route().current('admin.payments.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                            <Link :href="route('admin.payment-settings.edit')" :class="navClass('admin.payment-settings.*')" :aria-current="route().current('admin.payment-settings.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Rekening Pembayaran</span><span v-if="route().current('admin.payment-settings.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                        </template>
                        <template v-else-if="section.id === 'ip'">
                            <Link :href="route('admin.intellectual-properties.index')" :class="navClass('admin.intellectual-properties.*')" :aria-current="route().current('admin.intellectual-properties.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Data HKI</span><span v-if="route().current('admin.intellectual-properties.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                        </template>
                        <template v-else-if="section.id === 'publishing'">
                            <Link :href="route('admin.documents.index')" :class="navClass('admin.documents.*')" :aria-current="route().current('admin.documents.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Dokumen Panduan</span><span v-if="route().current('admin.documents.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                            <Link :href="route('admin.articles.index')" :class="navClass('admin.articles.*')" :aria-current="route().current('admin.articles.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Berita</span><span v-if="route().current('admin.articles.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                            <Link :href="route('admin.site-contents.index')" :class="navClass('admin.site-contents.*')" :aria-current="route().current('admin.site-contents.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Konten Website</span><span v-if="route().current('admin.site-contents.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                            <Link :href="route('admin.organization-members.index')" :class="navClass('admin.organization-members.*')" :aria-current="route().current('admin.organization-members.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Struktur Organisasi</span><span v-if="route().current('admin.organization-members.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                        </template>
                        <template v-else-if="section.id === 'help'">
                            <Link :href="route('admin.faqs.index')" :class="navClass('admin.faqs.*')" :aria-current="route().current('admin.faqs.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>FAQ</span><span v-if="route().current('admin.faqs.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                            <Link :href="route('admin.support-tickets.index')" :class="navClass('admin.support-tickets.*')" :aria-current="route().current('admin.support-tickets.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Tiket Bantuan</span><span v-if="route().current('admin.support-tickets.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                        </template>
                        <template v-else-if="section.id === 'reports'">
                            <Link :href="route('admin.reports.index')" :class="navClass('admin.reports.*')" :aria-current="route().current('admin.reports.*') ? 'page' : undefined" @click="mobileNavOpen = false"><span>Laporan</span><span v-if="route().current('admin.reports.*')" aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-gold-400"></span></Link>
                        </template>
                    </div>
                </section>
            </nav>
        </aside>
        <div class="min-w-0 flex-1 lg:ml-72">
            <header class="flex items-center justify-between border-b border-aski-100 bg-white px-5 py-4 sm:px-8"><div><p class="text-sm text-slate-500">Selamat datang</p><p class="font-semibold text-aski-900">{{ $page.props.auth.user.name }}</p></div><Link :href="route('logout')" method="post" as="button" class="rounded-lg border border-aski-200 px-4 py-2 text-sm font-semibold text-aski-700 transition hover:bg-aski-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">Keluar</Link></header>
            <main class="px-5 py-8 sm:px-8"><div v-if="page.props.flash?.success" role="status" class="mb-6 rounded-lg border border-aski-200 bg-aski-50 px-4 py-3 text-sm font-medium text-aski-900">{{ page.props.flash.success }}</div><div v-if="page.props.flash?.error" role="alert" class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">{{ page.props.flash.error }}</div><slot /></main>
        <Teleport to="body"><div v-if="showErrorPopup" class="fixed inset-0 z-[100] grid place-items-center bg-slate-950/50 p-4" @keydown="handlePopupKeydown"><section role="alertdialog" aria-modal="true" aria-labelledby="admin-error-popup-title" aria-describedby="admin-error-popup-message" class="w-full max-w-md rounded-2xl border border-red-100 bg-white p-6 shadow-2xl"><div class="flex items-start gap-4"><span aria-hidden="true" class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-red-50 text-xl font-bold text-red-700">!</span><div class="min-w-0 flex-1"><h2 id="admin-error-popup-title" class="text-lg font-bold text-aski-900">Permohonan belum dapat disetujui</h2><p id="admin-error-popup-message" class="mt-2 text-sm leading-6 text-slate-600">{{ errorMessage }}</p></div></div><div class="mt-6 flex justify-end"><button id="admin-error-popup-close" type="button" class="rounded-lg bg-aski-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-aski-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-400" @click="closeErrorPopup">Mengerti</button></div></section></div></Teleport>
        </div>
    </div>
</template>
