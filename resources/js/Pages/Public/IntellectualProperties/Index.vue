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

const activeTab = ref('holders'); // 'holders' | 'type' | 'year'
const showAnalytics = ref(true);
const chartMode = ref('vertical'); // 'vertical' | 'horizontal'
const viewMode = ref('table'); // 'table' | 'grid'
const hoveredTypeKey = ref(null);
const hoveredYearKey = ref(null);
const selectedRecord = ref(null);
const copiedId = ref(null);
const chartReady = ref(false);

const CHART_HEIGHT_PX = 210;

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

const switchTab = (tab) => {
    if (activeTab.value === tab) return;
    activeTab.value = tab;
    chartReady.value = false;
    setTimeout(() => {
        chartReady.value = true;
    }, 40);
};

const submit = () => {
    router.get(route('public.intellectual-properties.index'), form.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const exportUrl = computed(() => {
    const params = {};
    if (form.value.search) params.search = form.value.search;
    if (form.value.type) params.type = form.value.type;
    if (form.value.year) params.year = form.value.year;
    if (form.value.sort && form.value.sort !== 'latest') params.sort = form.value.sort;

    return route('public.intellectual-properties.export', params);
});

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

// Data Tab 2: Distribusi Jenis HKI
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
        barPx: item.total === 0 ? 6 : Math.max(Math.round((item.total / niceMax) * CHART_HEIGHT_PX), 18),
        widthPct: item.total === 0 ? 4 : Math.max(Math.round((item.total / niceMax) * 100), 10),
        isPeak: item.total === maxVal && item.total > 0,
        isActive: form.value.type === item.label,
    }));

    const topCategory = bars.find((b) => b.isPeak) || bars[0] || null;

    return { bars, ticks, niceMax, topCategory };
});

// Data Tab 3: Tren Pencatatan Tahunan
const yearlyChartSeries = computed(() => {
    const raw = props.analytics?.byYear || [];
    const maxVal = Math.max(...raw.map((i) => i.total), 1);
    const totalAll = raw.reduce((acc, item) => acc + item.total, 0) || props.summary?.total || 1;
    const { niceMax, ticks } = buildNiceScale(maxVal);

    const bars = raw.map((item) => ({
        key: item.year,
        year: item.year,
        total: item.total,
        percentage: Math.round((item.total / totalAll) * 100),
        barPx: item.total === 0 ? 6 : Math.max(Math.round((item.total / niceMax) * CHART_HEIGHT_PX), 18),
        widthPct: item.total === 0 ? 4 : Math.max(Math.round((item.total / niceMax) * 100), 10),
        isPeak: item.total === maxVal && item.total > 0,
        isActive: form.value.year === item.year,
    }));

    const peakYear = bars.find((b) => b.isPeak) || bars[bars.length - 1] || null;
    const avgPerYear = bars.length > 0 ? (totalAll / bars.length).toFixed(1) : '0';

    let growthInfo = null;
    if (bars.length >= 2) {
        const prev = bars[bars.length - 2];
        const curr = bars[bars.length - 1];
        const diff = curr.total - prev.total;
        const pct = prev.total > 0 ? Math.round((diff / prev.total) * 100) : null;
        growthInfo = {
            fromYear: prev.year,
            toYear: curr.year,
            diff,
            pct,
            isPositive: diff >= 0,
        };
    }

    return { bars, ticks, niceMax, peakYear, avgPerYear, growthInfo };
});

const getInitials = (name) => {
    if (!name) return 'PT';
    const words = name
        .replace(/[^a-zA-Z0-9\s]/g, '')
        .split(/\s+/)
        .filter(Boolean);
    if (words.length === 1) return words[0].slice(0, 3).toUpperCase();
    return words
        .slice(0, 3)
        .map((w) => w[0])
        .join('')
        .toUpperCase();
};

// Data Tab 1: Peringkat Institusi / Pemegang Hak (hingga 10 peringkat)
const topHoldersList = computed(() => {
    const raw = (props.analytics?.topHolders || []).slice(0, 10);
    const maxVal = Math.max(...raw.map((i) => i.total), 1);
    const totalAll = Math.max(props.summary?.total || raw.reduce((acc, item) => acc + item.total, 0), 1);

    return raw.map((item, idx) => {
        // Hanya peringkat #1 yang penuh 100%; peringkat #2 dst. dibuat bertingkat di bawah 100% agar kontras
        const rankScale = idx === 0 ? 100 : Math.max(84 - (idx - 1) * 6, 36);
        const barPct = idx === 0
            ? 100
            : Math.max(Math.round((item.total / maxVal) * rankScale), 16);

        return {
            ...item,
            initials: getInitials(item.name),
            rank: idx + 1,
            isTop: idx === 0 && item.total > 0,
            sharePct: Math.round((item.total / totalAll) * 100),
            barPct,
            isActive: form.value.search === item.name,
        };
    });
});

// Data Podium Top 3 (#2 Kiri, #1 Tengah, #3 Kanan)
const podiumHolders = computed(() => ({
    first: topHoldersList.value[0] || null,
    second: topHoldersList.value[1] || null,
    third: topHoldersList.value[2] || null,
}));

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
        <!-- HERO BANNER MEWAH DARK EMERALD + 3 KARTU KACA -->
        <section class="relative overflow-hidden border-b border-aski-700/60 bg-gradient-to-br from-aski-900 via-aski-800 to-aski-900 text-white">
            <div aria-hidden="true" class="pointer-events-none absolute inset-0">
                <div class="absolute -right-20 -top-24 h-80 w-80 rounded-full bg-gold-400/15 blur-3xl"></div>
                <div class="absolute -bottom-28 left-1/4 h-72 w-72 rounded-full bg-aski-500/20 blur-3xl"></div>
                <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold-400/40 to-transparent"></div>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-12 lg:px-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-gold-400">
                            Kekayaan Intelektual • ASKI-PTMA
                        </p>
                        <h1 class="mt-2.5 text-2xl font-extrabold tracking-tight text-white sm:text-4xl">
                            Statistik &amp; Direktori Karya HKI
                        </h1>
                        <p class="mt-3 text-sm leading-relaxed text-aski-100/85 sm:text-base">
                            Pangkalan data publik dan visualisasi capaian kekayaan intelektual anggota Perguruan Tinggi Muhammadiyah &amp; &lsquo;Aisyiyah.
                        </p>
                    </div>

                    <!-- 3 KARTU STATISTIK KACA MEWAH -->
                    <div class="grid grid-cols-3 gap-3 sm:gap-4 lg:min-w-[410px]">
                        <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3.5 shadow-lg shadow-aski-900/20 backdrop-blur-md">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-aski-100/75">
                                Total HKI
                            </p>
                            <p class="mt-1 text-2xl font-extrabold tabular-nums text-white sm:text-3xl">
                                {{ summary?.total ?? 0 }}
                            </p>
                            <p class="mt-0.5 text-[11px] font-medium text-gold-400">Terpublikasi</p>
                        </div>

                        <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3.5 shadow-lg shadow-aski-900/20 backdrop-blur-md">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-aski-100/75">
                                Kontributor
                            </p>
                            <p class="mt-1 text-2xl font-extrabold tabular-nums text-white sm:text-3xl">
                                {{ summary?.members ?? 0 }}
                            </p>
                            <p class="mt-0.5 text-[11px] text-aski-100/80">Institusi PTMA</p>
                        </div>

                        <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3.5 shadow-lg shadow-aski-900/20 backdrop-blur-md">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-aski-100/75">
                                Kategori
                            </p>
                            <p class="mt-1 text-2xl font-extrabold tabular-nums text-white sm:text-3xl">
                                {{ types?.length ?? 0 }}
                            </p>
                            <p class="mt-0.5 text-[11px] text-aski-100/80">Jenis HKI</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="mx-auto max-w-7xl px-4 pb-16 pt-8 sm:px-6 lg:px-8">
            <!-- SATU WADAH ANALITIK TERPADU BERBASIS TAB -->
            <section aria-label="Analitik Data HKI" class="rounded-2xl border border-slate-200/90 bg-white shadow-sm">
                <!-- TOP NAVIGATION BAR: TABS + TOGGLE SEMBUNYIKAN STATISTIK -->
                <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div role="tablist" aria-label="Pilih tampilan statistik" class="inline-flex flex-wrap rounded-xl bg-slate-100/80 p-1 text-xs font-bold">
                        <button
                            type="button"
                            role="tab"
                            :aria-selected="activeTab === 'holders'"
                            class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 transition"
                            :class="activeTab === 'holders'
                                ? 'bg-aski-700 text-white shadow-sm'
                                : 'text-slate-600 hover:text-aski-900'"
                            @click="switchTab('holders')"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                            </svg>
                            <span>Peringkat Institusi</span>
                        </button>

                        <button
                            type="button"
                            role="tab"
                            :aria-selected="activeTab === 'type'"
                            class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 transition"
                            :class="activeTab === 'type'
                                ? 'bg-aski-700 text-white shadow-sm'
                                : 'text-slate-600 hover:text-aski-900'"
                            @click="switchTab('type')"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                            <span>Distribusi Jenis HKI</span>
                        </button>

                        <button
                            type="button"
                            role="tab"
                            :aria-selected="activeTab === 'year'"
                            class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 transition"
                            :class="activeTab === 'year'
                                ? 'bg-aski-700 text-white shadow-sm'
                                : 'text-slate-600 hover:text-aski-900'"
                            @click="switchTab('year')"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                            </svg>
                            <span>Tren Tahunan</span>
                        </button>
                    </div>

                    <button
                        type="button"
                        class="inline-flex items-center gap-1.5 self-start rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-aski-900 sm:self-auto"
                        @click="showAnalytics = !showAnalytics"
                    >
                        <span>{{ showAnalytics ? 'Sembunyikan Statistik' : 'Tampilkan Statistik' }}</span>
                        <svg
                            class="h-4 w-4 transition-transform duration-200"
                            :class="showAnalytics ? '' : 'rotate-180'"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                        </svg>
                    </button>
                </div>

                <!-- ISI TAB ANALITIK -->
                <div v-show="showAnalytics" class="p-5 sm:p-6">
                    <!-- TAB 1: PERINGKAT INSTITUSI -->
                    <div v-if="activeTab === 'holders'">
                        <div class="flex flex-col gap-2 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-gold-100 text-gold-600">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                                        </svg>
                                    </span>
                                    <h2 class="text-base font-bold text-aski-900 sm:text-lg">
                                        Peringkat Pemegang Hak / Institusi Paling Produktif
                                    </h2>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">
                                    Klik podium atau baris institusi untuk menyaring direktori karya HKI milik institusi tersebut.
                                </p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 self-start rounded-full bg-aski-50 px-3 py-1 text-xs font-bold text-aski-800 sm:self-center">
                                <span class="h-1.5 w-1.5 rounded-full bg-gold-500"></span>
                                Top {{ topHoldersList.length }} Institusi
                            </span>
                        </div>

                        <div v-if="topHoldersList.length > 0" class="mt-6 grid items-stretch gap-8 lg:grid-cols-12 lg:gap-0 lg:divide-x lg:divide-slate-100">
                            <!-- KIRI: PANGGUNG PODIUM TERBUKA -->
                            <div class="flex flex-col justify-between lg:col-span-6 lg:pr-8">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-gold-600">
                                            Hall of Fame • PTMA
                                        </span>
                                        <p class="mt-0.5 text-xs text-slate-500">
                                            3 Pemegang Hak dengan Capaian Tertinggi
                                        </p>
                                    </div>
                                    <span class="text-[11px] font-medium text-slate-400">
                                        Klik untuk filter
                                    </span>
                                </div>

                                <!-- SUSUNAN PODIUM BERTINGKAT (#2 KIRI, #1 TENGAH, #3 KANAN) -->
                                <div class="mt-6 grid grid-cols-3 items-end gap-3 pt-2 sm:gap-4">
                                    <!-- JUARA 2 (PERAK - KIRI) -->
                                    <button
                                        v-if="podiumHolders.second"
                                        type="button"
                                        class="group flex flex-col items-center text-center focus:outline-none"
                                        :title="podiumHolders.second.name"
                                        @click="filterByHolder(podiumHolders.second.name)"
                                    >
                                        <div
                                            class="relative flex h-12 w-12 items-center justify-center rounded-full border border-slate-300 bg-slate-100 text-xs font-extrabold tracking-wider text-slate-700 transition group-hover:scale-105"
                                            :class="podiumHolders.second.isActive ? 'ring-2 ring-aski-700 ring-offset-2' : ''"
                                        >
                                            {{ podiumHolders.second.initials }}
                                            <span class="absolute -bottom-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-slate-600 text-[10px] font-black text-white">
                                                2
                                            </span>
                                        </div>
                                        <p class="mt-2.5 line-clamp-2 min-h-[2.25rem] text-[11px] font-bold leading-snug text-aski-900 group-hover:text-aski-700 sm:text-xs">
                                            {{ podiumHolders.second.name }}
                                        </p>
                                        <span
                                            class="mt-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-extrabold transition"
                                            :class="podiumHolders.second.isActive
                                                ? 'bg-aski-700 text-white'
                                                : 'bg-slate-100 text-slate-700'"
                                        >
                                            {{ podiumHolders.second.total }} HKI
                                        </span>
                                        <div
                                            class="mt-3 flex w-full flex-col items-center justify-center rounded-t-2xl border-x border-t-2 border-slate-300 bg-gradient-to-b from-slate-200/80 to-slate-100/40 transition-all duration-700 group-hover:from-slate-200"
                                            :style="{ height: chartReady ? '100px' : '24px' }"
                                        >
                                            <span class="text-2xl font-black text-slate-700">2</span>
                                            <span class="text-[9px] font-bold uppercase tracking-widest text-slate-500">Perak</span>
                                        </div>
                                    </button>
                                    <div v-else></div>

                                    <!-- JUARA 1 (EMAS - TENGAH TERTINGGI) -->
                                    <button
                                        v-if="podiumHolders.first"
                                        type="button"
                                        class="group flex flex-col items-center text-center focus:outline-none"
                                        :title="podiumHolders.first.name"
                                        @click="filterByHolder(podiumHolders.first.name)"
                                    >
                                        <svg class="mb-1.5 h-5 w-5 text-gold-500" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M2.25 18.75a.75.75 0 0 0 .75.75h18a.75.75 0 0 0 .75-.75v-1.5a.75.75 0 0 0-.75-.75H3a.75.75 0 0 0-.75.75v1.5ZM3.47 6.97a.75.75 0 0 1 1.06 0L8.25 10.69l3.14-5.23a.75.75 0 0 1 1.22 0l3.14 5.23 3.72-3.72a.75.75 0 0 1 1.27.64l-1.2 7.2a.75.75 0 0 1-.74.63H5.2a.75.75 0 0 1-.74-.63l-1.2-7.2a.75.75 0 0 1 .21-.64Z" />
                                        </svg>
                                        <div
                                            class="relative flex h-12 w-12 items-center justify-center rounded-full border-2 border-gold-400 bg-gradient-to-br from-gold-400 to-gold-500 text-xs font-black tracking-wider text-aski-900 shadow-sm transition group-hover:scale-105"
                                            :class="podiumHolders.first.isActive ? 'ring-2 ring-aski-700 ring-offset-2' : ''"
                                        >
                                            {{ podiumHolders.first.initials }}
                                            <span class="absolute -bottom-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-aski-900 text-[10px] font-black text-gold-400 ring-2 ring-white">
                                                1
                                            </span>
                                        </div>
                                        <p class="mt-2.5 line-clamp-2 min-h-[2.4rem] text-xs font-extrabold leading-snug text-aski-900 group-hover:text-aski-700 sm:text-[13px]">
                                            {{ podiumHolders.first.name }}
                                        </p>
                                        <span
                                            class="mt-1.5 rounded-full px-3 py-0.5 text-[11px] font-black transition"
                                            :class="podiumHolders.first.isActive
                                                ? 'bg-aski-700 text-white'
                                                : 'bg-gold-400 text-aski-900'"
                                        >
                                            {{ podiumHolders.first.total }} HKI
                                        </span>
                                        <div
                                            class="mt-3 flex w-full flex-col items-center justify-center rounded-t-2xl border-x border-t-2 border-gold-400 bg-gradient-to-b from-gold-400 via-gold-500 to-amber-500 text-aski-900 transition-all duration-700 group-hover:brightness-105"
                                            :style="{ height: chartReady ? '140px' : '24px' }"
                                        >
                                            <span class="text-3xl font-black leading-none text-aski-900">1</span>
                                            <span class="mt-1 rounded-full bg-aski-900/15 px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-widest text-aski-900">
                                                Teratas
                                            </span>
                                        </div>
                                    </button>

                                    <!-- JUARA 3 (PERUNGGU - KANAN) -->
                                    <button
                                        v-if="podiumHolders.third"
                                        type="button"
                                        class="group flex flex-col items-center text-center focus:outline-none"
                                        :title="podiumHolders.third.name"
                                        @click="filterByHolder(podiumHolders.third.name)"
                                    >
                                        <div
                                            class="relative flex h-12 w-12 items-center justify-center rounded-full border border-amber-300 bg-amber-50 text-xs font-extrabold tracking-wider text-amber-800 transition group-hover:scale-105"
                                            :class="podiumHolders.third.isActive ? 'ring-2 ring-aski-700 ring-offset-2' : ''"
                                        >
                                            {{ podiumHolders.third.initials }}
                                            <span class="absolute -bottom-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-amber-700 text-[10px] font-black text-white">
                                                3
                                            </span>
                                        </div>
                                        <p class="mt-2.5 line-clamp-2 min-h-[2.25rem] text-[11px] font-bold leading-snug text-aski-900 group-hover:text-aski-700 sm:text-xs">
                                            {{ podiumHolders.third.name }}
                                        </p>
                                        <span
                                            class="mt-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-extrabold transition"
                                            :class="podiumHolders.third.isActive
                                                ? 'bg-aski-700 text-white'
                                                : 'bg-amber-100/80 text-amber-800'"
                                        >
                                            {{ podiumHolders.third.total }} HKI
                                        </span>
                                        <div
                                            class="mt-3 flex w-full flex-col items-center justify-center rounded-t-2xl border-x border-t-2 border-amber-300 bg-gradient-to-b from-amber-200/80 to-amber-100/40 transition-all duration-700 group-hover:from-amber-200"
                                            :style="{ height: chartReady ? '78px' : '24px' }"
                                        >
                                            <span class="text-2xl font-black text-amber-800">3</span>
                                            <span class="text-[9px] font-bold uppercase tracking-widest text-amber-700">Perunggu</span>
                                        </div>
                                    </button>
                                    <div v-else></div>
                                </div>
                            </div>

                            <!-- KANAN: DAFTAR PERINGKAT DATAR DENGAN BATANG TEBAL (HANYA #1 YANG FULL 100%) -->
                            <div class="flex flex-col justify-between lg:col-span-6 lg:pl-8">
                                <div>
                                    <div class="flex flex-wrap items-center justify-between gap-2 pb-3">
                                        <span class="text-xs font-extrabold uppercase tracking-wider text-aski-900">
                                            Grafik Perbandingan &amp; Kontribusi
                                        </span>
                                        <div class="flex items-center gap-3 text-[11px] text-slate-500">
                                            <span class="inline-flex items-center gap-1.5 font-medium">
                                                <span class="h-2.5 w-2.5 rounded-full bg-gold-500"></span>
                                                Peringkat #1
                                            </span>
                                            <span class="inline-flex items-center gap-1.5 font-medium">
                                                <span class="h-2.5 w-2.5 rounded-full bg-aski-700"></span>
                                                Peringkat Lainnya
                                            </span>
                                        </div>
                                    </div>

                                    <div class="divide-y divide-slate-100 border-y border-slate-100">
                                        <button
                                            v-for="holder in topHoldersList"
                                            :key="holder.name"
                                            type="button"
                                            class="group block w-full py-3.5 pr-3 text-left transition focus:outline-none"
                                            :class="holder.isActive
                                                ? 'border-l-4 border-aski-700 bg-aski-100/75 pl-2'
                                                : 'pl-3 hover:bg-slate-50/80'"
                                            @click="filterByHolder(holder.name)"
                                        >
                                            <div class="flex items-center justify-between gap-3 text-xs">
                                                <div class="flex min-w-0 items-center gap-2.5">
                                                    <span
                                                        class="w-6 shrink-0 text-xs font-black tabular-nums"
                                                        :class="[
                                                            holder.isActive ? 'text-aski-800' : '',
                                                            !holder.isActive && holder.rank === 1 ? 'text-gold-600' : '',
                                                            !holder.isActive && holder.rank === 2 ? 'text-slate-600' : '',
                                                            !holder.isActive && holder.rank === 3 ? 'text-amber-700' : '',
                                                            !holder.isActive && holder.rank > 3 ? 'text-slate-400' : ''
                                                        ]"
                                                    >
                                                        #{{ holder.rank }}
                                                    </span>
                                                    <span
                                                        class="truncate transition"
                                                        :class="holder.isActive
                                                            ? 'font-extrabold text-aski-800'
                                                            : 'font-bold text-aski-900 group-hover:text-aski-700'"
                                                        :title="holder.name"
                                                    >
                                                        {{ holder.name }}
                                                    </span>
                                                </div>

                                                <div class="flex shrink-0 items-center gap-2 tabular-nums">
                                                    <span
                                                        :class="holder.isActive
                                                            ? 'font-black text-aski-800'
                                                            : 'font-extrabold text-aski-900'"
                                                    >
                                                        {{ holder.total }} HKI
                                                    </span>
                                                    <span
                                                        class="text-[11px]"
                                                        :class="holder.isActive
                                                            ? 'font-bold text-aski-700'
                                                            : 'font-semibold text-slate-400'"
                                                    >
                                                        ({{ holder.sharePct }}%)
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="mt-2.5 h-3.5 w-full overflow-hidden rounded-full bg-slate-100 ring-1 ring-inset ring-slate-200/80">
                                                <div
                                                    class="h-full rounded-full transition-all duration-700 ease-out"
                                                    :class="holder.rank === 1
                                                        ? 'bg-gradient-to-r from-gold-600 via-gold-500 to-gold-400'
                                                        : holder.rank <= 3
                                                            ? 'bg-gradient-to-r from-aski-900 to-aski-600'
                                                            : 'bg-gradient-to-r from-aski-700 to-aski-500'"
                                                    :style="{ width: chartReady ? `${holder.barPct}%` : '6%' }"
                                                ></div>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <p class="mt-4 text-right text-[11px] text-slate-400">
                                    Persentase dihitung terhadap total {{ summary?.total ?? 0 }} HKI yang terpublikasi.
                                </p>
                            </div>
                        </div>

                        <div v-else class="py-14 text-center text-sm text-slate-500">
                            Belum ada data peringkat institusi untuk ditampilkan.
                        </div>
                    </div>

                    <!-- TAB 2: DISTRIBUSI BERDASARKAN JENIS HKI (TETAP DENGAN KARTU KOTAK) -->
                    <div v-else-if="activeTab === 'type'">
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
                                    Klik batang grafik atau kartu kategori untuk menyaring tabel direktori berdasarkan jenis HKI.
                                </p>
                            </div>

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

                        <div v-if="typeChartSeries.bars.length > 0" class="mt-5 grid items-stretch gap-6 lg:grid-cols-12">
                            <!-- KIRI: DIAGRAM BATANG (7 Kolom) -->
                            <div class="flex flex-col justify-between rounded-2xl border border-slate-100 bg-white p-4 sm:p-5 lg:col-span-7">
                                <!-- MODE VERTIKAL -->
                                <div v-if="chartMode === 'vertical'" class="pt-6">
                                    <div class="flex gap-2.5 sm:gap-3">
                                        <div class="flex h-[210px] w-7 shrink-0 flex-col justify-between text-right text-[11px] font-semibold text-slate-400">
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

                                            <div class="relative z-10 flex h-[210px] items-end justify-around gap-2 px-2 sm:gap-3 sm:px-3">
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

                                <!-- MODE HORIZONTAL -->
                                <div v-else class="space-y-3 py-2">
                                    <button
                                        v-for="bar in typeChartSeries.bars"
                                        :key="`hbar-${bar.key}`"
                                        type="button"
                                        class="group block w-full rounded-xl p-2.5 text-left transition"
                                        :class="bar.isActive
                                            ? 'bg-aski-100/90 ring-1 ring-aski-600/40'
                                            : 'hover:bg-aski-50/70'"
                                        @click="selectTypeFilter(bar.key)"
                                    >
                                        <div class="flex items-center justify-between text-sm">
                                            <div class="flex items-center gap-2">
                                                <span
                                                    :class="bar.isActive
                                                        ? 'font-extrabold text-aski-800'
                                                        : 'font-bold text-aski-900 group-hover:text-aski-700'"
                                                >
                                                    {{ bar.label }}
                                                </span>
                                                <span
                                                    v-if="bar.isPeak"
                                                    class="rounded-full bg-gold-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-gold-600"
                                                >
                                                    Tertinggi
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2 text-xs tabular-nums">
                                                <span :class="bar.isActive ? 'font-black text-aski-800' : 'font-extrabold text-aski-900'">
                                                    {{ bar.total }} HKI
                                                </span>
                                                <span
                                                    class="rounded-md px-1.5 py-0.5 font-semibold"
                                                    :class="bar.isActive
                                                        ? 'bg-aski-200/70 text-aski-800'
                                                        : 'bg-slate-100 text-slate-600'"
                                                >
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
                                    <span class="font-semibold text-aski-700">Klik batang untuk filter</span>
                                </div>
                            </div>

                            <!-- KANAN: RINGKASAN PROPORSI KATEGORI (KARTU KOTAK UTUH) -->
                            <div class="flex flex-col justify-between rounded-2xl border border-slate-100 bg-slate-50/50 p-4 sm:p-5 lg:col-span-5">
                                <div>
                                    <span class="text-xs font-extrabold uppercase tracking-wider text-aski-900">
                                        Rincian Proporsi Kategori
                                    </span>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        Komposisi jenis karya HKI yang telah tercatat.
                                    </p>

                                    <div class="mt-4 space-y-2.5">
                                        <button
                                            v-for="bar in typeChartSeries.bars"
                                            :key="`summary-type-${bar.key}`"
                                            type="button"
                                            class="group flex w-full items-center justify-between rounded-xl border px-3.5 py-3 text-left shadow-2xs transition"
                                            :class="bar.isActive
                                                ? 'border-aski-600 bg-aski-100/90 ring-1 ring-aski-600/30'
                                                : 'border-slate-200/80 bg-white hover:border-aski-500/40 hover:bg-aski-50/40'"
                                            @click="selectTypeFilter(bar.key)"
                                        >
                                            <div class="flex items-center gap-2.5">
                                                <span
                                                    class="h-2.5 w-2.5 shrink-0 rounded-full"
                                                    :class="bar.isActive
                                                        ? 'bg-aski-700 ring-2 ring-aski-300'
                                                        : bar.isPeak
                                                            ? 'bg-gold-500'
                                                            : 'bg-aski-700'"
                                                ></span>
                                                <span
                                                    class="text-xs transition"
                                                    :class="bar.isActive
                                                        ? 'font-extrabold text-aski-800'
                                                        : 'font-bold text-aski-900 group-hover:text-aski-700'"
                                                >
                                                    {{ bar.label }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2 text-xs tabular-nums">
                                                <span
                                                    :class="bar.isActive
                                                        ? 'font-black text-aski-800'
                                                        : 'font-extrabold text-aski-900'"
                                                >
                                                    {{ bar.total }} HKI
                                                </span>
                                                <span
                                                    class="rounded-md px-1.5 py-0.5 text-[11px] font-bold"
                                                    :class="bar.isActive
                                                        ? 'bg-aski-200/70 text-aski-800'
                                                        : 'bg-slate-100 text-slate-600'"
                                                >
                                                    {{ bar.percentage }}%
                                                </span>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <p class="mt-4 text-right text-[11px] text-slate-400">
                                    Klik salah satu jenis HKI untuk memfilter direktori.
                                </p>
                            </div>
                        </div>

                        <div v-else class="py-14 text-center text-sm text-slate-500">
                            Belum ada data distribusi kategori HKI untuk ditampilkan.
                        </div>
                    </div>

                    <!-- TAB 3: TREN PENCATATAN PER TAHUN (TETAP DENGAN KARTU KOTAK) -->
                    <div v-else-if="activeTab === 'year'">
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
                                Pertumbuhan jumlah pencatatan HKI setiap tahun. Klik batang tahun untuk menyaring direktori.
                            </p>
                        </div>

                        <div v-if="yearlyChartSeries.bars.length > 0" class="mt-5 grid items-stretch gap-6 lg:grid-cols-12">
                            <!-- KIRI: DIAGRAM BATANG TAHUNAN -->
                            <div class="flex flex-col justify-between rounded-2xl border border-slate-100 bg-white p-4 sm:p-5 lg:col-span-6">
                                <div class="pt-6">
                                    <div class="flex gap-2.5">
                                        <div class="flex h-[210px] w-7 shrink-0 flex-col justify-between text-right text-[11px] font-semibold text-slate-400">
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

                                            <div
                                                class="relative z-10 flex h-[210px] items-end"
                                                :class="yearlyChartSeries.bars.length <= 3
                                                    ? 'justify-center gap-12 sm:gap-16'
                                                    : 'justify-around gap-3 px-4'"
                                            >
                                                <button
                                                    v-for="bar in yearlyChartSeries.bars"
                                                    :key="bar.year"
                                                    type="button"
                                                    class="group relative flex flex-col items-center justify-end focus:outline-none"
                                                    :class="yearlyChartSeries.bars.length <= 3 ? 'w-24 shrink-0 sm:w-28' : 'min-w-0 flex-1'"
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
                                                        class="rounded-t-xl transition-all duration-700 ease-out group-hover:brightness-105"
                                                        :class="[
                                                            yearlyChartSeries.bars.length <= 3 ? 'w-14 sm:w-16' : 'w-11 sm:w-14',
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
                                        <div
                                            class="flex flex-1"
                                            :class="yearlyChartSeries.bars.length <= 3
                                                ? 'justify-center gap-12 sm:gap-16'
                                                : 'justify-around gap-3 px-4'"
                                        >
                                            <button
                                                v-for="bar in yearlyChartSeries.bars"
                                                :key="`x2-${bar.year}`"
                                                type="button"
                                                class="rounded-lg py-1.5 text-center text-xs font-bold transition"
                                                :class="[
                                                    yearlyChartSeries.bars.length <= 3 ? 'w-24 shrink-0 sm:w-28' : 'flex-1',
                                                    bar.isActive ? 'bg-aski-700 text-white shadow-xs' : 'text-slate-700 hover:bg-aski-50'
                                                ]"
                                                @click="selectYearFilter(bar.year)"
                                            >
                                                {{ bar.year }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 rounded-xl bg-slate-50 px-3.5 py-2.5 text-xs text-slate-600">
                                    <span class="inline-flex items-center gap-1.5 font-medium">
                                        <span class="h-3 w-3 rounded-sm bg-gold-400"></span>
                                        Puncak capaian tahunan
                                    </span>
                                    <span
                                        v-if="yearlyChartSeries.growthInfo"
                                        class="rounded-md px-2 py-0.5 text-[11px] font-bold"
                                        :class="yearlyChartSeries.growthInfo.isPositive
                                            ? 'bg-emerald-100/80 text-emerald-800'
                                            : 'bg-amber-100 text-amber-800'"
                                    >
                                        {{ yearlyChartSeries.growthInfo.isPositive ? '+' : '' }}{{ yearlyChartSeries.growthInfo.diff }} HKI
                                        <template v-if="yearlyChartSeries.growthInfo.pct !== null">
                                            ({{ yearlyChartSeries.growthInfo.isPositive ? '+' : '' }}{{ yearlyChartSeries.growthInfo.pct }}%)
                                        </template>
                                        vs {{ yearlyChartSeries.growthInfo.fromYear }}
                                    </span>
                                    <span v-else class="font-semibold text-aski-700">Klik tahun untuk filter</span>
                                </div>
                            </div>

                            <!-- KANAN: KOTAK SOROTAN & REKAP PER TAHUN -->
                            <div class="flex flex-col justify-between rounded-2xl border border-slate-100 bg-slate-50/50 p-4 sm:p-5 lg:col-span-6">
                                <div>
                                    <div v-if="yearlyChartSeries.peakYear" class="rounded-xl bg-gradient-to-r from-aski-900 to-aski-800 p-4 text-white shadow-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] font-bold uppercase tracking-wider text-gold-400">
                                                Tahun Paling Produktif
                                            </span>
                                            <span class="rounded-full bg-white/10 px-2.5 py-0.5 text-[11px] font-semibold text-aski-100">
                                                Rata-rata {{ yearlyChartSeries.avgPerYear }} HKI/thn
                                            </span>
                                        </div>
                                        <div class="mt-2 flex items-baseline justify-between">
                                            <p class="text-2xl font-extrabold text-white">
                                                Tahun {{ yearlyChartSeries.peakYear.year }}
                                            </p>
                                            <p class="text-sm font-bold text-gold-400">
                                                {{ yearlyChartSeries.peakYear.total }} HKI ({{ yearlyChartSeries.peakYear.percentage }}%)
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-3.5 space-y-2.5">
                                        <button
                                            v-for="bar in yearlyChartSeries.bars"
                                            :key="`summary-year-${bar.year}`"
                                            type="button"
                                            class="group flex w-full items-center justify-between rounded-xl border px-4 py-3 text-left shadow-2xs transition"
                                            :class="bar.isActive
                                                ? 'border-aski-600 bg-aski-100/90 ring-1 ring-aski-600/30'
                                                : 'border-slate-200/80 bg-white hover:border-aski-500/40 hover:bg-aski-50/40'"
                                            @click="selectYearFilter(bar.year)"
                                        >
                                            <span
                                                class="text-xs transition"
                                                :class="bar.isActive
                                                    ? 'font-extrabold text-aski-800'
                                                    : 'font-bold text-aski-900 group-hover:text-aski-700'"
                                            >
                                                Tahun {{ bar.year }}
                                            </span>
                                            <div class="flex items-center gap-2 text-xs tabular-nums">
                                                <span
                                                    :class="bar.isActive
                                                        ? 'font-black text-aski-800'
                                                        : 'font-extrabold text-aski-900'"
                                                >
                                                    {{ bar.total }} HKI
                                                </span>
                                                <span
                                                    class="rounded-md px-1.5 py-0.5 text-[11px] font-bold"
                                                    :class="bar.isActive
                                                        ? 'bg-aski-200/70 text-aski-800'
                                                        : 'bg-slate-100 text-slate-600'"
                                                >
                                                    {{ bar.percentage }}%
                                                </span>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <p class="mt-4 text-right text-[11px] text-slate-400">
                                    Klik tahun untuk menyaring data pada tabel direktori.
                                </p>
                            </div>
                        </div>

                        <div v-else class="py-14 text-center text-sm text-slate-500">
                            Data tren tahunan belum tersedia.
                        </div>
                    </div>
                </div>
            </section>

            <!-- FILTER & SEARCH PANEL -->
            <section class="mt-8 rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm sm:p-6">
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
                        Semua ({{ summary?.total ?? 0 }})
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
            <section id="direktori-hki" class="mt-8 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm" aria-labelledby="directory-title">
                <div class="flex flex-col gap-4 border-b border-slate-200 bg-slate-50/60 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <h2 id="directory-title" class="text-lg font-bold text-aski-900">Direktori Data HKI</h2>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        <!-- TOMBOL UNDUH REKAP (EXCEL / CSV) -->
                        <a
                            :href="exportUrl"
                            class="inline-flex min-h-9 items-center gap-1.5 rounded-lg border border-aski-600/30 bg-aski-50 px-3 py-1.5 text-xs font-bold text-aski-800 shadow-sm transition hover:border-aski-700 hover:bg-aski-700 hover:text-white"
                            :title="hasActiveFilters ? 'Unduh rekap sesuai filter aktif (CSV/Excel)' : 'Unduh seluruh rekap data HKI (CSV/Excel)'"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            <span>Unduh Rekap</span>
                        </a>

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