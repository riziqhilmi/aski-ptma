<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { formatDateOnly } from '@/utils/date';

const props = defineProps({
    records: Object,
    filters: Object,
    summary: Object,
    types: Array,
    years: {
        type: Array,
        default: () => [],
    },
    analytics: {
        type: Object,
        default: () => ({
            byType: [],
            byYear: [],
            topHolders: [],
        }),
    },
});

const form = ref({
    search: props.filters?.search || '',
    type: props.filters?.type || '',
    year: props.filters?.year || '',
    sort: props.filters?.sort || 'latest',
});

const chartMode = ref('vertical'); // 'vertical' | 'horizontal'
const viewMode = ref('table'); // 'table' | 'grid'
const hoveredTypeKey = ref(null);
const hoveredYearKey = ref(null);
const selectedRecord = ref(null);
const copiedId = ref(null);
const chartReady = ref(false);

const CHART_HEIGHT_PX = 200;

const handleKeydown = (e) => {
    if (e.key === 'Escape' && selectedRecord.value) {
        closeDetail();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
    setTimeout(() => {
        chartReady.value = true;
    }, 100);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

const submit = () => {
    router.get(route('public.intellectual-properties.index'), form.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const selectTypeFilter = (typeVal) => {
    form.value.type = form.value.type === typeVal ? '' : typeVal;
    submit();
};

const selectYearFilter = (yearVal) => {
    form.value.year = form.value.year === yearVal ? '' : yearVal;
    submit();
};

const filterByHolder = (holderName) => {
    form.value.search = form.value.search === holderName ? '' : holderName;
    submit();
};

const clearSearch = () => {
    form.value.search = '';
    submit();
};

const resetAllFilters = () => {
    form.value = {
        search: '',
        type: '',
        year: '',
        sort: 'latest',
    };
    submit();
};

const hasActiveFilters = computed(() => {
    return Boolean(
        form.value.search ||
        form.value.type ||
        form.value.year ||
        (form.value.sort && form.value.sort !== 'latest')
    );
});

const buildNiceScale = (maxValue) => {
    const safeMax = Math.max(maxValue, 1);
    if (safeMax <= 4) {
        return {
            niceMax: 4,
            ticks: [4, 3, 2, 1, 0],
        };
    }
    const step = Math.ceil(safeMax / 4);
    const niceMax = step * 4;
    return {
        niceMax,
        ticks: [niceMax, step * 3, step * 2, step, 0],
    };
};

// Data Bar Chart 1: Distribusi Jenis HKI
const typeChartSeries = computed(() => {
    const raw = (props.analytics?.byType?.length
        ? props.analytics.byType
        : (props.types || []).map((t) => ({ label: t, total: 0 }))
    ).slice(0, 6);

    const maxVal = Math.max(...raw.map((i) => i.total), 1);
    const grandTotal = raw.reduce((a, b) => a + b.total, 0) || props.summary?.total || 1;
    const { niceMax, ticks } = buildNiceScale(maxVal);

    const bars = raw.map((item) => ({
        key: item.label,
        label: item.label,
        shortLabel: item.label.length > 16 ? `${item.label.slice(0, 15)}…` : item.label,
        total: item.total,
        percentage: Math.round((item.total / grandTotal) * 100),
        barPx: item.total === 0 ? 6 : Math.max(Math.round((item.total / niceMax) * CHART_HEIGHT_PX), 16),
        widthPct: item.total === 0 ? 4 : Math.max(Math.round((item.total / niceMax) * 100), 8),
        isPeak: item.total === maxVal && item.total > 0,
        isActive: form.value.type === item.label,
    }));

    return { bars, ticks, niceMax };
});

// Data Bar Chart 2: Pertumbuhan Tahunan
const yearlyChartSeries = computed(() => {
    const raw = props.analytics?.byYear || [];
    const maxVal = Math.max(...raw.map((i) => i.total), 1);
    const { niceMax, ticks } = buildNiceScale(maxVal);

    const bars = raw.map((item) => ({
        key: item.year,
        year: item.year,
        total: item.total,
        barPx: item.total === 0 ? 6 : Math.max(Math.round((item.total / niceMax) * CHART_HEIGHT_PX), 16),
        isPeak: item.total === maxVal && item.total > 0,
        isActive: form.value.year === item.year,
    }));

    return { bars, ticks, niceMax };
});

// Data Top 5 Pemegang Hak / Institusi
const topHoldersList = computed(() => {
    const raw = (props.analytics?.topHolders || []).slice(0, 5);
    const maxVal = Math.max(...raw.map((i) => i.total), 1);

    return raw.map((item, idx) => ({
        ...item,
        rank: idx + 1,
        isTop: item.total === maxVal && item.total > 0,
        barPct: Math.max(Math.round((item.total / maxVal) * 100), 14),
        isActive: form.value.search === item.name,
    }));
});

const getBadgeClasses = (ipType) => {
    const normalized = (ipType || '').toLowerCase();
    if (normalized.includes('paten')) {
        return 'bg-gold-100 text-gold-600 ring-1 ring-inset ring-gold-500/30';
    }
    if (normalized.includes('merek')) {
        return 'bg-emerald-50 text-emerald-800 ring-1 ring-inset ring-emerald-600/20';
    }
    if (normalized.includes('desain')) {
        return 'bg-teal-50 text-teal-800 ring-1 ring-inset ring-teal-600/20';
    }
    return 'bg-aski-50 text-aski-800 ring-1 ring-inset ring-aski-600/20';
};

const openDetail = (record) => {
    selectedRecord.value = record;
};

const closeDetail = () => {
    selectedRecord.value = null;
};

const copyCitation = async (record) => {
    const text = `${record.title} (${record.ip_type}) — Pencipta: ${record.creator || '-'} | Pemegang Hak: ${record.rights_holder || '-'} | Tanggal: ${formatDateOnly(record.application_date)}`;
    try {
        await navigator.clipboard.writeText(text);
        copiedId.value = record.id;
        setTimeout(() => {
            copiedId.value = null;
        }, 2200);
    } catch {
        // Abaikan jika clipboard dibatasi browser
    }
};
</script>

<template>
    <Head title="Direktori & Statistik Data HKI" />

    <PublicLayout>
        <!-- HERO SECTION -->
        <section class="relative overflow-hidden bg-gradient-to-br from-aski-900 via-aski-800 to-aski-900 text-white">
            <div aria-hidden="true" class="pointer-events-none absolute inset-0">
                <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-aski-500/20 blur-3xl"></div>
                <div class="absolute -bottom-32 left-1/4 h-80 w-80 rounded-full bg-gold-400/15 blur-3xl"></div>
                <svg class="absolute inset-0 h-full w-full opacity-[0.06]" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="hki-grid" width="36" height="36" patternUnits="userSpaceOnUse">
                            <path d="M 36 0 L 0 0 0 36" fill="none" stroke="currentColor" stroke-width="1" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#hki-grid)" />
                </svg>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 pb-24 pt-12 sm:px-6 sm:pb-28 sm:pt-16 lg:px-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl">
                        <div class="inline-flex items-center gap-2 rounded-full border border-gold-400/30 bg-gold-400/10 px-3.5 py-1.5 backdrop-blur-sm">
                            <span class="h-2 w-2 rounded-full bg-gold-400"></span>
                            <span class="text-xs font-bold uppercase tracking-[0.16em] text-gold-400">
                                Kekayaan Intelektual • ASKI-PTMA
                            </span>
                        </div>

                        <h1 class="mt-4 text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-[2.65rem] lg:leading-[1.15]">
                            Statistik &amp; Direktori Karya HKI
                        </h1>

                        <p class="mt-4 max-w-2xl text-base leading-7 text-aski-100/90">
                            Visualisasi capaian inovasi dan direktori kekayaan intelektual yang dicatat oleh sivitas akademika dan anggota ASKI-PTMA.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 backdrop-blur-md">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-aski-100/75">
                                Dominasi Kategori
                            </p>
                            <p class="mt-0.5 text-sm font-bold text-gold-400">
                                {{ typeChartSeries.bars[0]?.label || types?.[0] || 'Hak Cipta' }}
                                <span v-if="typeChartSeries.bars[0]?.total" class="text-white/80">
                                    ({{ typeChartSeries.bars[0].total }} HKI)
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- MAIN CONTAINER -->
        <div class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
            <!-- 4 FLOATING KPI SUMMARY CARDS -->
            <section aria-label="Ringkasan statistik HKI" class="relative z-10 -mt-14 grid gap-4 sm:-mt-16 sm:grid-cols-2 lg:grid-cols-4">
                <article class="group rounded-2xl border border-slate-200/90 bg-white p-5 shadow-lg shadow-aski-900/5 transition duration-300 hover:-translate-y-1 hover:border-aski-500/40 hover:shadow-xl sm:p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Total HKI Tercatat</p>
                            <p class="mt-2.5 text-3xl font-extrabold tracking-tight text-aski-900">{{ summary.total }}</p>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-aski-50 text-aski-700 transition group-hover:bg-aski-700 group-hover:text-white">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 flex items-center gap-1.5 text-xs text-slate-500">
                        <span class="h-1.5 w-1.5 rounded-full bg-aski-500"></span>
                        Terpublikasi di direktori resmi
                    </p>
                </article>

                <article class="group rounded-2xl border border-slate-200/90 bg-white p-5 shadow-lg shadow-aski-900/5 transition duration-300 hover:-translate-y-1 hover:border-gold-500/40 hover:shadow-xl sm:p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Kategori HKI</p>
                            <p class="mt-2.5 text-3xl font-extrabold tracking-tight text-aski-900">{{ summary.types }}</p>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gold-100 text-gold-600 transition group-hover:bg-gold-500 group-hover:text-white">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 flex items-center gap-1.5 text-xs text-slate-500">
                        <span class="h-1.5 w-1.5 rounded-full bg-gold-500"></span>
                        Klasifikasi kekayaan intelektual
                    </p>
                </article>

                <article class="group rounded-2xl border border-slate-200/90 bg-white p-5 shadow-lg shadow-aski-900/5 transition duration-300 hover:-translate-y-1 hover:border-aski-500/40 hover:shadow-xl sm:p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Kontributor Aktif</p>
                            <p class="mt-2.5 text-3xl font-extrabold tracking-tight text-aski-900">{{ summary.members }}</p>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-aski-50 text-aski-700 transition group-hover:bg-aski-700 group-hover:text-white">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 flex items-center gap-1.5 text-xs text-slate-500">
                        <span class="h-1.5 w-1.5 rounded-full bg-aski-500"></span>
                        Anggota dengan data tercatat
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200/90 bg-gradient-to-br from-aski-900 to-aski-800 p-5 text-white shadow-lg shadow-aski-900/10 transition duration-300 hover:-translate-y-1 hover:shadow-xl sm:p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-gold-400">
                                Capaian {{ summary.latest_year || 'Terkini' }}
                            </p>
                            <p class="mt-2.5 text-3xl font-extrabold tracking-tight text-white">
                                {{ summary.latest_year_total ?? summary.total }}
                            </p>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-gold-400 backdrop-blur-sm">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 flex items-center gap-1.5 text-xs text-aski-100/80">
                        <span class="h-1.5 w-1.5 rounded-full bg-gold-400"></span>
                        Pencatatan HKI tahun terakhir
                    </p>
                </article>
            </section>

            <!-- DUAL INTERACTIVE BAR CHARTS SECTION -->
            <section aria-label="Grafik Batang Statistik HKI" class="mt-8 grid gap-6 lg:grid-cols-12">
                <!-- BAR CHART 1: DISTRIBUSI JENIS HKI (7 Kolom) -->
                <div class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 lg:col-span-7">
                    <div>
                        <div class="flex flex-col gap-3 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-aski-50 text-aski-700">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                        </svg>
                                    </span>
                                    <h2 class="text-base font-bold text-aski-900 sm:text-lg">
                                        Distribusi Berdasarkan Jenis HKI
                                    </h2>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">
                                    Klik batang grafik untuk menyaring tabel direktori berdasarkan kategori HKI.
                                </p>
                            </div>

                            <!-- Mode Switcher: Batang Vertikal / Batang Horizontal -->
                            <div class="inline-flex self-start rounded-xl border border-slate-200 bg-slate-50 p-1 text-xs font-semibold">
                                <button
                                    type="button"
                                    class="rounded-lg px-3 py-1.5 transition"
                                    :class="chartMode === 'vertical' ? 'bg-aski-700 text-white shadow-sm' : 'text-slate-600 hover:text-aski-900'"
                                    @click="chartMode = 'vertical'"
                                >
                                    Batang Vertikal
                                </button>
                                <button
                                    type="button"
                                    class="rounded-lg px-3 py-1.5 transition"
                                    :class="chartMode === 'horizontal' ? 'bg-aski-700 text-white shadow-sm' : 'text-slate-600 hover:text-aski-900'"
                                    @click="chartMode = 'horizontal'"
                                >
                                    Horizontal
                                </button>
                            </div>
                        </div>

                        <!-- MODE 1: VERTICAL COLUMN BAR CHART -->
                        <div v-if="typeChartSeries.bars.length > 0 && chartMode === 'vertical'" class="pt-14">
                            <div class="flex gap-2.5 sm:gap-3">
                                <div class="flex h-[200px] w-7 shrink-0 flex-col justify-between text-right text-[11px] font-semibold text-slate-400">
                                    <span v-for="tick in typeChartSeries.ticks" :key="`y1-${tick}`">
                                        {{ tick }}
                                    </span>
                                </div>

                                <div class="relative flex-1 border-b-2 border-l border-slate-200">
                                    <div aria-hidden="true" class="pointer-events-none absolute inset-0 flex flex-col justify-between">
                                        <div
                                            v-for="tick in typeChartSeries.ticks"
                                            :key="`g1-${tick}`"
                                            class="w-full border-t border-dashed border-slate-200/80"
                                        ></div>
                                    </div>

                                    <div class="relative z-10 flex h-[200px] items-end justify-around gap-2 px-2 sm:gap-3 sm:px-3">
                                        <button
                                            v-for="bar in typeChartSeries.bars"
                                            :key="bar.key"
                                            type="button"
                                            class="group relative flex min-w-0 flex-1 flex-col items-center justify-end focus:outline-none"
                                            @mouseenter="hoveredTypeKey = bar.key"
                                            @mouseleave="hoveredTypeKey = null"
                                            @click="selectTypeFilter(bar.key)"
                                        >
                                            <div
                                                v-if="hoveredTypeKey === bar.key"
                                                class="pointer-events-none absolute bottom-full z-30 mb-2 w-max max-w-[190px] rounded-xl bg-aski-900 px-3 py-2 text-center text-[11px] leading-snug text-white shadow-xl"
                                            >
                                                <p class="font-bold text-gold-400">{{ bar.label }}</p>
                                                <p class="mt-0.5 font-medium text-white/90">{{ bar.total }} HKI ({{ bar.percentage }}%)</p>
                                            </div>

                                            <span
                                                class="mb-1.5 rounded-md px-1.5 py-0.5 text-xs font-extrabold transition group-hover:-translate-y-0.5"
                                                :class="bar.isPeak ? 'bg-gold-100 text-gold-600' : 'text-aski-900'"
                                            >
                                                {{ bar.total }}
                                            </span>

                                            <div
                                                class="w-10 rounded-t-xl transition-all duration-700 ease-out group-hover:brightness-105 sm:w-12"
                                                :class="[
                                                    bar.isPeak
                                                        ? 'bg-gradient-to-t from-gold-600 via-gold-500 to-gold-400 shadow-md shadow-gold-500/20'
                                                        : 'bg-gradient-to-t from-aski-900 via-aski-700 to-aski-500 shadow-sm shadow-aski-900/10',
                                                    bar.isActive ? 'ring-2 ring-aski-900 ring-offset-2' : ''
                                                ]"
                                                :style="{ height: chartReady ? `${bar.barPx}px` : '8px' }"
                                            ></div>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-2.5 flex gap-2.5 sm:gap-3">
                                <div class="w-7 shrink-0"></div>
                                <div class="flex flex-1 justify-around gap-1.5 px-2 sm:gap-2 sm:px-3">
                                    <button
                                        v-for="bar in typeChartSeries.bars"
                                        :key="`x1-${bar.key}`"
                                        type="button"
                                        class="flex min-w-0 flex-1 flex-col items-center rounded-lg px-1 py-1.5 text-center transition"
                                        :class="bar.isActive ? 'bg-aski-700 text-white' : 'hover:bg-aski-50'"
                                        :title="bar.label"
                                        @click="selectTypeFilter(bar.key)"
                                    >
                                        <span
                                            class="line-clamp-2 text-[11px] font-bold leading-tight sm:text-xs"
                                            :class="bar.isActive ? 'text-white' : 'text-aski-900'"
                                        >
                                            {{ bar.shortLabel }}
                                        </span>
                                        <span
                                            class="mt-0.5 text-[10px] font-semibold sm:text-[11px]"
                                            :class="bar.isActive ? 'text-aski-100' : 'text-slate-400'"
                                        >
                                            {{ bar.percentage }}%
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- MODE 2: HORIZONTAL BAR CHART -->
                        <div v-else-if="typeChartSeries.bars.length > 0 && chartMode === 'horizontal'" class="mt-5 space-y-3.5">
                            <button
                                v-for="bar in typeChartSeries.bars"
                                :key="`hbar-${bar.key}`"
                                type="button"
                                class="group block w-full rounded-xl p-2.5 text-left transition hover:bg-aski-50/70"
                                :class="bar.isActive ? 'bg-aski-50 ring-1 ring-aski-500/40' : ''"
                                @click="selectTypeFilter(bar.key)"
                            >
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-aski-900 group-hover:text-aski-700">{{ bar.label }}</span>
                                        <span
                                            v-if="bar.isPeak"
                                            class="rounded-full bg-gold-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-gold-600"
                                        >
                                            Terbanyak
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="font-extrabold text-aski-900">{{ bar.total }} HKI</span>
                                        <span class="rounded-md bg-slate-100 px-1.5 py-0.5 font-semibold text-slate-600">
                                            {{ bar.percentage }}%
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-2 h-3 w-full overflow-hidden rounded-full bg-slate-100">
                                    <div
                                        class="h-full rounded-full transition-all duration-700 ease-out"
                                        :class="bar.isPeak
                                            ? 'bg-gradient-to-r from-aski-700 via-aski-500 to-gold-400'
                                            : 'bg-gradient-to-r from-aski-800 to-aski-500'"
                                        :style="{ width: chartReady ? `${bar.widthPct}%` : '4%' }"
                                    ></div>
                                </div>
                            </button>
                        </div>

                        <div v-else class="py-16 text-center text-sm text-slate-500">
                            Belum ada data distribusi kategori HKI untuk ditampilkan.
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 rounded-xl bg-slate-50 px-3.5 py-2.5 text-xs text-slate-600">
                        <div class="flex items-center gap-4">
                            <span class="inline-flex items-center gap-1.5 font-medium">
                                <span class="h-3 w-3 rounded-sm bg-gradient-to-t from-gold-600 to-gold-400"></span>
                                Kategori tertinggi
                            </span>
                            <span class="inline-flex items-center gap-1.5 font-medium">
                                <span class="h-3 w-3 rounded-sm bg-gradient-to-t from-aski-800 to-aski-500"></span>
                                Kategori lainnya
                            </span>
                        </div>
                        <span class="font-semibold text-aski-700">Interaktif • Klik batang untuk filter</span>
                    </div>
                </div>

                <!-- BAR CHART 2: TREN PENCATATAN PER TAHUN (5 Kolom) -->
                <div class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 lg:col-span-5">
                    <div>
                        <div class="border-b border-slate-100 pb-4">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-gold-100 text-gold-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                                    </svg>
                                </span>
                                <h2 class="text-base font-bold text-aski-900 sm:text-lg">
                                    Tren Pencatatan per Tahun
                                </h2>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">
                                Pertumbuhan jumlah pencatatan HKI setiap tahun.
                            </p>
                        </div>

                        <div v-if="yearlyChartSeries.bars.length > 0" class="pt-14">
                            <div class="flex gap-2.5">
                                <div class="flex h-[200px] w-7 shrink-0 flex-col justify-between text-right text-[11px] font-semibold text-slate-400">
                                    <span v-for="tick in yearlyChartSeries.ticks" :key="`y2-${tick}`">
                                        {{ tick }}
                                    </span>
                                </div>

                                <div class="relative flex-1 border-b-2 border-l border-slate-200">
                                    <div aria-hidden="true" class="pointer-events-none absolute inset-0 flex flex-col justify-between">
                                        <div
                                            v-for="tick in yearlyChartSeries.ticks"
                                            :key="`g2-${tick}`"
                                            class="w-full border-t border-dashed border-slate-200/80"
                                        ></div>
                                    </div>

                                    <div class="relative z-10 flex h-[200px] items-end justify-around gap-2 px-3">
                                        <button
                                            v-for="bar in yearlyChartSeries.bars"
                                            :key="bar.year"
                                            type="button"
                                            class="group relative flex min-w-0 flex-1 flex-col items-center justify-end focus:outline-none"
                                            @mouseenter="hoveredYearKey = bar.year"
                                            @mouseleave="hoveredYearKey = null"
                                            @click="selectYearFilter(bar.year)"
                                        >
                                            <div
                                                v-if="hoveredYearKey === bar.year"
                                                class="pointer-events-none absolute bottom-full z-30 mb-2 whitespace-nowrap rounded-lg bg-aski-900 px-2.5 py-1.5 text-[11px] font-bold text-white shadow-lg"
                                            >
                                                Tahun {{ bar.year }}: {{ bar.total }} HKI
                                            </div>

                                            <span
                                                class="mb-1.5 rounded px-1.5 py-0.5 text-xs font-extrabold transition group-hover:-translate-y-0.5"
                                                :class="bar.isPeak ? 'bg-gold-100 text-gold-600' : 'text-aski-900'"
                                            >
                                                {{ bar.total }}
                                            </span>

                                            <div
                                                class="w-9 rounded-t-xl transition-all duration-700 ease-out group-hover:brightness-105 sm:w-11"
                                                :class="[
                                                    bar.isPeak
                                                        ? 'bg-gradient-to-t from-gold-600 via-gold-500 to-gold-400 shadow-md shadow-gold-500/20'
                                                        : 'bg-gradient-to-t from-aski-800 to-aski-500 shadow-sm',
                                                    bar.isActive ? 'ring-2 ring-aski-900 ring-offset-2' : ''
                                                ]"
                                                :style="{ height: chartReady ? `${bar.barPx}px` : '8px' }"
                                            ></div>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-2.5 flex gap-2.5">
                                <div class="w-7 shrink-0"></div>
                                <div class="flex flex-1 justify-around gap-2 px-3">
                                    <button
                                        v-for="bar in yearlyChartSeries.bars"
                                        :key="`x2-${bar.year}`"
                                        type="button"
                                        class="flex-1 rounded-lg py-1.5 text-center text-xs font-bold transition"
                                        :class="bar.isActive ? 'bg-aski-700 text-white' : 'text-slate-700 hover:bg-aski-50'"
                                        @click="selectYearFilter(bar.year)"
                                    >
                                        {{ bar.year }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-else class="py-16 text-center text-sm text-slate-500">
                            Data tren tahunan belum tersedia.
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between rounded-xl bg-slate-50 px-3.5 py-2.5 text-xs text-slate-600">
                        <span class="inline-flex items-center gap-1.5 font-medium">
                            <span class="h-3 w-3 rounded-sm bg-gold-400"></span>
                            Puncak capaian tahunan
                        </span>
                        <span class="font-semibold text-aski-700">Klik tahun untuk filter</span>
                    </div>
                </div>
            </section>

            <!-- TOP 5 PEMEGANG HAK / INSTITUSI KONTRIBUTOR -->
            <section v-if="topHoldersList.length > 0" aria-label="Pemegang hak teratas" class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-bold text-aski-900">Pemegang Hak / Institusi Paling Produktif</h2>
                        <p class="text-xs text-slate-500">Klik salah satu kartu institusi untuk menyaring direktori HKI milik pemegang hak tersebut.</p>
                    </div>
                    <span class="mt-1 inline-flex self-start rounded-full bg-aski-50 px-3 py-1 text-xs font-semibold text-aski-800 sm:mt-0">
                        Top {{ topHoldersList.length }} Pemegang Hak
                    </span>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    <button
                        v-for="holder in topHoldersList"
                        :key="holder.name"
                        type="button"
                        class="flex flex-col justify-between rounded-xl border p-3.5 text-left transition hover:-translate-y-0.5 hover:shadow-md"
                        :class="holder.isActive
                            ? 'border-aski-700 bg-aski-50/70 ring-1 ring-aski-700'
                            : 'border-slate-100 bg-slate-50/70 hover:border-aski-500/40 hover:bg-white'"
                        @click="filterByHolder(holder.name)"
                    >
                        <div class="w-full">
                            <div class="flex items-center justify-between">
                                <span
                                    class="inline-flex h-6 w-6 items-center justify-center rounded-lg text-xs font-extrabold"
                                    :class="holder.isTop ? 'bg-gold-400 text-aski-900' : 'bg-aski-100 text-aski-800'"
                                >
                                    #{{ holder.rank }}
                                </span>
                                <span class="text-xs font-extrabold text-aski-800">{{ holder.total }} HKI</span>
                            </div>
                            <p class="mt-2.5 line-clamp-2 text-xs font-bold leading-relaxed text-aski-900" :title="holder.name">
                                {{ holder.name }}
                            </p>
                        </div>

                        <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-slate-200/80">
                            <div
                                class="h-full rounded-full transition-all duration-700"
                                :class="holder.isTop ? 'bg-gold-500' : 'bg-aski-600'"
                                :style="{ width: chartReady ? `${holder.barPct}%` : '6%' }"
                            ></div>
                        </div>
                    </button>
                </div>
            </section>

            <!-- FILTER & SEARCH PANEL -->
            <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                <div v-if="types && types.length > 0" class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-4">
                    <span class="mr-1 text-xs font-bold uppercase tracking-wider text-slate-400">Filter Cepat:</span>
                    <button
                        type="button"
                        class="rounded-full px-3.5 py-1.5 text-xs font-semibold transition"
                        :class="form.type === ''
                            ? 'bg-aski-700 text-white shadow-sm'
                            : 'bg-slate-100 text-slate-600 hover:bg-aski-50 hover:text-aski-800'"
                        @click="selectTypeFilter('')"
                    >
                        Semua ({{ summary.total }})
                    </button>
                    <button
                        v-for="item in analytics?.byType || []"
                        :key="`pill-${item.label}`"
                        type="button"
                        class="rounded-full px-3.5 py-1.5 text-xs font-semibold transition"
                        :class="form.type === item.label
                            ? 'bg-aski-700 text-white shadow-sm'
                            : 'bg-slate-100 text-slate-600 hover:bg-aski-50 hover:text-aski-800'"
                        @click="selectTypeFilter(item.label)"
                    >
                        {{ item.label }} <span class="opacity-75">({{ item.total }})</span>
                    </button>
                </div>

                <form class="mt-4" role="search" aria-label="Cari data HKI" @submit.prevent="submit">
                    <div class="grid gap-3 md:grid-cols-12">
                        <div class="relative md:col-span-5">
                            <label for="ip-search" class="sr-only">Cari judul, pencipta, atau pemegang hak</label>
                            <svg aria-hidden="true" class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.44 9.79l3.63 3.63a.75.75 0 1 0 1.06-1.06L13 11.73A5.5 5.5 0 0 0 8.5 3ZM4.5 8.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
                            </svg>
                            <input
                                id="ip-search"
                                v-model="form.search"
                                class="min-h-11 w-full rounded-xl border-slate-300 pl-10 pr-9 text-sm focus:border-aski-700 focus:ring-aski-700"
                                placeholder="Cari judul karya, pencipta, atau pemegang hak..."
                            />
                            <button
                                v-if="form.search"
                                type="button"
                                aria-label="Hapus kata kunci"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                                @click="clearSearch"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                                </svg>
                            </button>
                        </div>

                        <div class="md:col-span-3">
                            <label for="ip-type" class="sr-only">Jenis HKI</label>
                            <select
                                id="ip-type"
                                v-model="form.type"
                                class="min-h-11 w-full rounded-xl border-slate-300 text-sm focus:border-aski-700 focus:ring-aski-700"
                            >
                                <option value="">Semua jenis HKI</option>
                                <option v-for="type in types" :key="type" :value="type">{{ type }}</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label for="ip-year" class="sr-only">Tahun</label>
                            <select
                                id="ip-year"
                                v-model="form.year"
                                class="min-h-11 w-full rounded-xl border-slate-300 text-sm focus:border-aski-700 focus:ring-aski-700"
                            >
                                <option value="">Semua tahun</option>
                                <option v-for="yr in years" :key="yr" :value="yr">{{ yr }}</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <button class="btn-primary min-h-11 w-full rounded-xl px-4" type="submit">
                                Cari Data
                            </button>
                        </div>
                    </div>

                    <div v-if="hasActiveFilters" class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3 text-xs">
                        <div class="flex flex-wrap items-center gap-2 text-slate-600">
                            <span class="font-semibold">Filter aktif:</span>
                            <span v-if="form.type" class="rounded-md bg-aski-50 px-2 py-1 font-semibold text-aski-800">
                                Jenis: {{ form.type }}
                            </span>
                            <span v-if="form.year" class="rounded-md bg-gold-100 px-2 py-1 font-semibold text-gold-600">
                                Tahun: {{ form.year }}
                            </span>
                            <span v-if="form.search" class="rounded-md bg-slate-100 px-2 py-1 font-semibold text-slate-700">
                                Kata kunci: "{{ form.search }}"
                            </span>
                        </div>
                        <button
                            type="button"
                            class="font-semibold text-rose-600 hover:text-rose-700 hover:underline"
                            @click="resetAllFilters"
                        >
                            Reset semua filter
                        </button>
                    </div>
                </form>
            </section>

            <!-- DIRECTORY TABLE & CARD VIEW -->
            <section id="direktori-hki" class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="directory-title">
                <div class="flex flex-col gap-4 border-b border-slate-200 bg-slate-50/60 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <h2 id="directory-title" class="text-lg font-bold text-aski-900">Direktori Data HKI</h2>
                        <p class="mt-0.5 text-sm text-slate-500">
                            Menampilkan <span class="font-semibold text-aski-900">{{ records.from || 0 }}–{{ records.to || 0 }}</span> dari <span class="font-semibold text-aski-900">{{ records.total }}</span> data terdaftar
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        <select
                            v-model="form.sort"
                            aria-label="Urutkan data HKI"
                            class="min-h-9 rounded-lg border-slate-300 bg-white py-1.5 pl-3 pr-8 text-xs font-semibold text-slate-700 focus:border-aski-700 focus:ring-aski-700"
                            @change="submit"
                        >
                            <option value="latest">Urutkan: Terbaru</option>
                            <option value="oldest">Urutkan: Terlama</option>
                            <option value="title">Urutkan: Judul (A–Z)</option>
                        </select>

                        <div class="inline-flex rounded-lg border border-slate-200 bg-white p-1">
                            <button
                                type="button"
                                class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-semibold transition"
                                :class="viewMode === 'table' ? 'bg-aski-700 text-white shadow-sm' : 'text-slate-600 hover:text-aski-900'"
                                @click="viewMode = 'table'"
                            >
                                Tabel
                            </button>
                            <button
                                type="button"
                                class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-semibold transition"
                                :class="viewMode === 'grid' ? 'bg-aski-700 text-white shadow-sm' : 'text-slate-600 hover:text-aski-900'"
                                @click="viewMode = 'grid'"
                            >
                                Kartu
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TABLE VIEW -->
                <div
                    v-if="viewMode === 'table'"
                    tabindex="0"
                    role="region"
                    aria-label="Tabel data HKI"
                    class="overflow-x-auto focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-aski-700"
                >
                    <table class="min-w-[820px] w-full text-left text-sm">
                        <thead class="bg-aski-50/80 text-xs uppercase tracking-wider text-aski-900">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 font-bold sm:px-6">Judul Karya / Invensi</th>
                                <th scope="col" class="px-5 py-3.5 font-bold">Jenis HKI</th>
                                <th scope="col" class="px-5 py-3.5 font-bold">Pencipta</th>
                                <th scope="col" class="px-5 py-3.5 font-bold">Pemegang Hak</th>
                                <th scope="col" class="px-5 py-3.5 font-bold">Tanggal</th>
                                <th scope="col" class="px-5 py-3.5 text-right font-bold sm:px-6">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="record in records.data"
                                :key="record.id"
                                class="group cursor-pointer transition hover:bg-aski-50/40"
                                @click="openDetail(record)"
                            >
                                <td class="max-w-sm px-5 py-4 sm:px-6">
                                    <p class="font-semibold leading-snug text-aski-900 transition group-hover:text-aski-700">
                                        {{ record.title }}
                                    </p>
                                    <p v-if="record.user?.member_profile?.institution_name" class="mt-1 text-xs text-slate-400">
                                        {{ record.user.member_profile.institution_name }}
                                    </p>
                                </td>
                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="getBadgeClasses(record.ip_type)"
                                    >
                                        {{ record.ip_type }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-700">{{ record.creator || '—' }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ record.rights_holder || '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                                    {{ formatDateOnly(record.application_date) }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-right sm:px-6" @click.stop>
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-aski-800 transition hover:border-aski-600 hover:bg-aski-700 hover:text-white"
                                        @click="openDetail(record)"
                                    >
                                        Detail
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="records.data.length === 0">
                                <td colspan="6" class="px-5 py-14 text-center sm:px-6">
                                    <p class="font-bold text-aski-900">Data HKI tidak ditemukan</p>
                                    <p class="mt-1 text-sm text-slate-500">Coba ubah kata kunci pencarian atau pilih kategori HKI lainnya.</p>
                                    <button
                                        v-if="hasActiveFilters"
                                        type="button"
                                        class="mt-4 inline-flex items-center rounded-lg bg-aski-700 px-4 py-2 text-xs font-semibold text-white hover:bg-aski-800"
                                        @click="resetAllFilters"
                                    >
                                        Tampilkan Semua Data
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- CARD GRID VIEW -->
                <div v-else class="p-5 sm:p-6">
                    <div v-if="records.data.length > 0" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <article
                            v-for="record in records.data"
                            :key="`card-${record.id}`"
                            class="group flex cursor-pointer flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 transition duration-300 hover:-translate-y-1 hover:border-aski-500/50 hover:shadow-lg hover:shadow-aski-900/5"
                            @click="openDetail(record)"
                        >
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="getBadgeClasses(record.ip_type)"
                                    >
                                        {{ record.ip_type }}
                                    </span>
                                    <span class="text-xs font-medium text-slate-400">
                                        {{ formatDateOnly(record.application_date) }}
                                    </span>
                                </div>

                                <h3 class="mt-3.5 line-clamp-2 text-base font-bold leading-snug text-aski-900 transition group-hover:text-aski-700">
                                    {{ record.title }}
                                </h3>

                                <dl class="mt-4 space-y-2 border-t border-slate-100 pt-3.5 text-xs">
                                    <div class="flex items-start justify-between gap-3">
                                        <dt class="shrink-0 font-medium text-slate-400">Pencipta</dt>
                                        <dd class="text-right font-semibold text-slate-700">{{ record.creator || '—' }}</dd>
                                    </div>
                                    <div class="flex items-start justify-between gap-3">
                                        <dt class="shrink-0 font-medium text-slate-400">Pemegang Hak</dt>
                                        <dd class="text-right font-semibold text-slate-700">{{ record.rights_holder || '—' }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-3">
                                <span class="truncate text-[11px] font-medium text-slate-400">
                                    {{ record.user?.member_profile?.institution_name || 'Direktori Publik ASKI' }}
                                </span>
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-aski-700 transition group-hover:translate-x-0.5">
                                    Rincian →
                                </span>
                            </div>
                        </article>
                    </div>

                    <div v-else class="py-12 text-center">
                        <p class="font-bold text-aski-900">Data HKI tidak ditemukan</p>
                        <p class="mt-1 text-sm text-slate-500">Coba ubah kata kunci atau filter jenis HKI.</p>
                    </div>
                </div>

                <!-- PAGINATION -->
                <nav
                    v-if="records.links?.length > 3"
                    aria-label="Navigasi halaman data HKI"
                    class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/40 px-5 py-4 sm:px-6"
                >
                    <p class="text-xs text-slate-500">
                        Informasi ditampilkan sesuai data yang tersedia untuk publik.
                    </p>
                    <div class="flex flex-wrap justify-center gap-1.5">
                        <Link
                            v-for="link in records.links"
                            :key="link.label"
                            :href="link.url || '#direktori-hki'"
                            v-html="link.label"
                            :aria-current="link.active ? 'page' : undefined"
                            class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg border px-3 py-1.5 text-xs font-semibold transition"
                            :class="link.active
                                ? 'border-aski-700 bg-aski-700 text-white shadow-sm'
                                : 'border-slate-200 bg-white text-aski-700 hover:bg-aski-50'"
                        />
                    </div>
                </nav>
            </section>
        </div>

        <!-- MODAL DETAIL HKI -->
        <Teleport to="body">
            <div
                v-if="selectedRecord"
                class="fixed inset-0 z-50 flex items-center justify-center bg-aski-900/60 p-4 backdrop-blur-sm transition"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-hki-title"
                @click.self="closeDetail"
            >
                <div class="w-full max-w-xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                    <div class="bg-gradient-to-r from-aski-900 to-aski-800 px-6 py-5 text-white">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="inline-flex rounded-full bg-gold-400/20 px-3 py-1 text-xs font-bold text-gold-400">
                                    {{ selectedRecord.ip_type }}
                                </span>
                                <h3 id="modal-hki-title" class="mt-2.5 text-lg font-bold leading-snug text-white">
                                    {{ selectedRecord.title }}
                                </h3>
                            </div>
                            <button
                                type="button"
                                aria-label="Tutup detail"
                                class="rounded-lg bg-white/10 p-1.5 text-white/80 transition hover:bg-white/20 hover:text-white"
                                @click="closeDetail"
                            >
                                ✕
                            </button>
                        </div>
                    </div>

                    <div class="space-y-4 p-6">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-xl bg-slate-50 p-3.5">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pencipta</p>
                                <p class="mt-1 text-sm font-semibold text-aski-900">{{ selectedRecord.creator || '—' }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3.5">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pemegang Hak</p>
                                <p class="mt-1 text-sm font-semibold text-aski-900">{{ selectedRecord.rights_holder || '—' }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3.5">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tanggal Permohonan</p>
                                <p class="mt-1 text-sm font-semibold text-aski-900">
                                    {{ formatDateOnly(selectedRecord.application_date) }}
                                </p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3.5">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Afiliasi Anggota</p>
                                <p class="mt-1 text-sm font-semibold text-aski-900">
                                    {{ selectedRecord.user?.member_profile?.institution_name || 'Anggota ASKI-PTMA' }}
                                </p>
                            </div>
                        </div>

                        <div v-if="selectedRecord.description" class="rounded-xl border border-slate-100 p-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Deskripsi / Ringkasan</p>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-600">{{ selectedRecord.description }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50 px-6 py-4">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100"
                            @click="copyCitation(selectedRecord)"
                        >
                            {{ copiedId === selectedRecord.id ? '✓ Ringkasan Tersalin!' : 'Salin Info HKI' }}
                        </button>
                        <button type="button" class="btn-primary px-5 py-2 text-xs" @click="closeDetail">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </PublicLayout>
</template>