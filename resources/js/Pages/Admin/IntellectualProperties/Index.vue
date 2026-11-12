<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PaginationLinks from '@/Components/PaginationLinks.vue';
import { formatDateOnly } from '@/utils/date.js';
import { Chart, CategoryScale, LinearScale, LineElement, PointElement, LineController, Tooltip, Legend, Filler } from 'chart.js';
import { onBeforeUnmount, onMounted, ref, watch, nextTick } from 'vue';

Chart.register(CategoryScale, LinearScale, LineElement, PointElement, LineController, Tooltip, Legend, Filler);

const props = defineProps({
    records: Object,
    filters: Object,
    summary: Object,
    chart: Array,
    options: Object,
});

const chartCanvas = ref(null);
let chartInstance = null;
const filterForm = ref({ ...props.filters });
const importForm = useForm({ file: null });
const importInput = ref(null);
const importFileName = ref('');
const chartLabels = props.chart.map((item) => {
    if (item.label.length === 7) return new Date(`${item.label}-01T00:00:00`).toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
    return item.label;
});

const applyFilters = () => router.get(route('admin.intellectual-properties.index'), filterForm.value, {
    preserveState: true,
    preserveScroll: true,
});

const exportUrl = () => route('admin.intellectual-properties.export', filterForm.value);
const submitImport = () => importForm.post(route('admin.intellectual-properties.import'), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => { importForm.reset(); importFileName.value = ''; if (importInput.value) importInput.value.value = ''; },
});
const chooseImportFile = () => importInput.value?.click();
const onImportFileSelected = (event) => {
    const file = event.target.files?.[0] ?? null;
    importForm.file = file;
    importFileName.value = file?.name ?? '';
    if (file) submitImport();
};

const renderChart = () => {
    if (!chartCanvas.value || props.chart.length === 0) return;
    chartInstance?.destroy();
    chartInstance = new Chart(chartCanvas.value, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Jumlah HKI',
                data: props.chart.map((item) => item.total),
                borderColor: '#047857',
                backgroundColor: 'rgba(4, 120, 87, 0.10)',
                fill: true,
                tension: 0.3,
                pointRadius: 3,
                pointHoverRadius: 6,
                pointBackgroundColor: '#f4c430',
                pointBorderColor: '#047857',
                pointBorderWidth: 2,
                borderWidth: 3,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#123d32', padding: 12, displayColors: false, callbacks: { label: (context) => `${context.parsed.y} data HKI` } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#64748b', maxRotation: 0, autoSkip: true } },
                y: { beginAtZero: true, ticks: { precision: 0, color: '#64748b', padding: 10 }, grid: { color: 'rgba(148, 163, 184, 0.18)' }, border: { display: false } },
            },
        },
    });
};

onMounted(renderChart);
watch(() => props.chart, async () => {
    await nextTick();
    renderChart();
}, { deep: true });

onBeforeUnmount(() => chartInstance?.destroy());
</script>

<template>
    <Head title="Data HKI" />
    <AdminLayout>
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-aski-700">DATA HKI</p>
                <h1 class="mt-1 text-3xl font-bold text-aski-900">Semua Data HKI</h1>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a :href="exportUrl()" class="btn-primary">Export Excel</a>
                <a :href="route('admin.intellectual-properties.import-template')" class="inline-flex min-h-10 items-center rounded-lg border border-aski-200 bg-white px-3 py-2 text-sm font-semibold text-aski-700 hover:bg-aski-50">Template</a>
                <input id="hki-import-file" ref="importInput" type="file" accept=".xlsx,.csv" class="sr-only" @change="onImportFileSelected" />
                <button type="button" class="inline-flex min-h-10 items-center rounded-lg border border-aski-200 bg-white px-3 py-2 text-sm font-semibold text-aski-700 hover:bg-aski-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700" :disabled="importForm.processing" @click="chooseImportFile">{{ importForm.processing ? 'Mengimporâ€¦' : 'Import Excel' }}</button>
            </div>
        </div>

        <div v-if="importFileName || importForm.errors.file || importForm.errors.import" role="status" class="mt-3 rounded-lg border border-aski-100 bg-white px-4 py-3 text-sm">
            <p v-if="importFileName && importForm.processing" class="text-slate-600">Mengimpor {{ importFileName }}â€¦</p>
            <p v-if="importForm.errors.file" class="text-red-700">{{ importForm.errors.file }}</p>
            <p v-if="importForm.errors.import" class="text-red-700">{{ importForm.errors.import }}</p>
        </div>

        <div class="mt-7 grid gap-4 md:grid-cols-3">
            <div class="card p-5"><p class="text-sm text-slate-500">Total HKI</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.total }}</p></div>
            <div class="card p-5"><p class="text-sm text-slate-500">Jenis HKI</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.types }}</p></div>
            <div class="card p-5"><p class="text-sm text-slate-500">Institusi Terkait</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.institutions }}</p></div>
        </div>

        <form class="card mt-6 grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-5" @submit.prevent="applyFilters">
            <label class="sr-only" for="hki-period-start">Tanggal mulai</label>
            <input id="hki-period-start" v-model="filterForm.period_start" type="date" aria-label="Tanggal mulai" class="rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500" />
            <label class="sr-only" for="hki-period-end">Tanggal akhir</label>
            <input id="hki-period-end" v-model="filterForm.period_end" type="date" aria-label="Tanggal akhir" class="rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500" />
            <label class="sr-only" for="hki-institution-filter">Filter institusi</label>
            <select id="hki-institution-filter" v-model="filterForm.institution" class="rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500">
                <option value="">Semua institusi</option>
                <option v-for="institution in options.institutions" :key="institution" :value="institution">{{ institution }}</option>
            </select>
            <label class="sr-only" for="hki-type-filter">Filter jenis HKI</label>
            <select id="hki-type-filter" v-model="filterForm.type" class="rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500">
                <option value="">Semua jenis</option>
                <option v-for="type in options.types" :key="type" :value="type">{{ type }}</option>
            </select>
            <label class="sr-only" for="hki-group-filter">Kelompok waktu grafik</label>
            <select id="hki-group-filter" v-model="filterForm.group" class="rounded-lg border-slate-300 focus:border-aski-500 focus:ring-aski-500">
                <option value="month">Bulanan</option>
                <option value="year">Tahunan</option>
            </select>
            <div class="flex flex-wrap gap-3 sm:col-span-2 lg:col-span-5">
                <button class="btn-primary" type="submit">Terapkan Filter</button>
                <Link :href="route('admin.intellectual-properties.index')" class="rounded-lg border border-aski-200 px-4 py-2 font-semibold text-aski-700">Reset</Link>
            </div>
        </form>

        <div class="card mt-6 overflow-hidden p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-aski-700">TREN PENDAFTARAN</p>
                    <h2 class="mt-1 text-xl font-bold text-aski-900">Perkembangan Data HKI</h2>
                    <p class="mt-1 text-sm text-slate-500">Jumlah data berdasarkan tanggal permohonan</p>
                </div>
                <span class="rounded-full bg-aski-50 px-3 py-1 text-xs font-semibold text-aski-700">{{ filterForm.group === 'year' ? 'Tahunan' : 'Bulanan' }}</span>
            </div>
            <div v-if="chart.length" class="mt-5 h-72 sm:h-80">
                <canvas ref="chartCanvas"></canvas>
            </div>
            <div v-else class="mt-5 flex h-64 items-center justify-center rounded-xl border border-dashed border-aski-200 bg-aski-50/40 px-6 text-center text-sm text-slate-500">
                Belum ada data untuk ditampilkan pada rentang filter ini.
            </div>
        </div>

        <div tabindex="0" role="region" aria-label="Tabel data HKI, gunakan gulir horizontal untuk melihat kolom lain" class="card mt-6 overflow-x-auto focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-aski-50 text-aski-900">
                    <tr>
                        <th class="p-4">Judul</th>
                        <th class="p-4">Member</th>
                        <th class="p-4">Institusi</th>
                        <th class="p-4">Jenis</th>
                        <th class="p-4">Tanggal Permohonan</th>
                        <th class="p-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="record in records.data" :key="record.id" class="border-t border-aski-100">
                        <td class="p-4 font-semibold text-aski-900">{{ record.title }}</td>
                        <td class="p-4">{{ record.user.name }}</td>
                        <td class="p-4">{{ record.user.member_profile?.institution_name || '-' }}</td>
                        <td class="p-4">{{ record.ip_type }}</td>
                        <td class="p-4">{{ formatDateOnly(record.application_date) }}</td>
                        <td class="p-4"><Link :href="route('admin.intellectual-properties.show', record.id)" class="font-semibold text-aski-700 hover:text-aski-900">Detail</Link></td>
                    </tr>
                    <tr v-if="records.data.length === 0">
                        <td colspan="6" class="p-8 text-center text-slate-500">Belum ada data HKI.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <PaginationLinks :links="records.links" />
    </AdminLayout>
</template>

