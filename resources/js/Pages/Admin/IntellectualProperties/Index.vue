<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Chart, CategoryScale, LinearScale, LineElement, PointElement, LineController, Tooltip, Legend } from 'chart.js';
import { onBeforeUnmount, onMounted, ref } from 'vue';

Chart.register(CategoryScale, LinearScale, LineElement, PointElement, LineController, Tooltip, Legend);

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

const applyFilters = () => router.get(route('admin.intellectual-properties.index'), filterForm.value, {
    preserveState: true,
    preserveScroll: true,
});

const exportUrl = () => route('admin.intellectual-properties.export', filterForm.value);

onMounted(() => {
    chartInstance = new Chart(chartCanvas.value, {
        type: 'line',
        data: {
            labels: props.chart.map((item) => item.label),
            datasets: [{
                label: 'Jumlah HKI',
                data: props.chart.map((item) => item.total),
                borderColor: '#047857',
                backgroundColor: '#f4c430',
                tension: 0.35,
                pointRadius: 4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });
});

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
            <a :href="exportUrl()" class="btn-primary">Export Excel</a>
        </div>

        <div class="mt-7 grid gap-4 md:grid-cols-3">
            <div class="card p-5"><p class="text-sm text-slate-500">Total HKI</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.total }}</p></div>
            <div class="card p-5"><p class="text-sm text-slate-500">Jenis HKI</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.types }}</p></div>
            <div class="card p-5"><p class="text-sm text-slate-500">Institusi Terkait</p><p class="mt-2 text-3xl font-bold text-aski-900">{{ summary.institutions }}</p></div>
        </div>

        <form class="card mt-6 grid gap-4 p-5 md:grid-cols-5" @submit.prevent="applyFilters">
            <input v-model="filterForm.period_start" type="date" class="rounded-lg border-slate-300" />
            <input v-model="filterForm.period_end" type="date" class="rounded-lg border-slate-300" />
            <select v-model="filterForm.institution" class="rounded-lg border-slate-300">
                <option value="">Semua institusi</option>
                <option v-for="institution in options.institutions" :key="institution" :value="institution">{{ institution }}</option>
            </select>
            <select v-model="filterForm.type" class="rounded-lg border-slate-300">
                <option value="">Semua jenis</option>
                <option v-for="type in options.types" :key="type" :value="type">{{ type }}</option>
            </select>
            <select v-model="filterForm.group" class="rounded-lg border-slate-300">
                <option value="month">Bulanan</option>
                <option value="year">Tahunan</option>
            </select>
            <div class="flex gap-3 md:col-span-5">
                <button class="btn-primary" type="submit">Terapkan Filter</button>
                <Link :href="route('admin.intellectual-properties.index')" class="rounded-lg border border-aski-200 px-4 py-2 font-semibold text-aski-700">Reset</Link>
            </div>
        </form>

        <div class="card mt-6 p-5">
            <h2 class="text-lg font-bold text-aski-900">Perkembangan Data HKI</h2>
            <div class="mt-4 h-72">
                <canvas ref="chartCanvas"></canvas>
            </div>
        </div>

        <div class="card mt-6 overflow-x-auto">
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
                        <td class="p-4">{{ record.application_date }}</td>
                        <td class="p-4"><Link :href="route('admin.intellectual-properties.show', record.id)" class="font-semibold text-aski-700 hover:text-aski-900">Detail</Link></td>
                    </tr>
                    <tr v-if="records.data.length === 0">
                        <td colspan="6" class="p-8 text-center text-slate-500">Belum ada data HKI.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="records.links?.length > 3" class="mt-5 flex flex-wrap gap-2">
            <Link v-for="link in records.links" :key="link.label" :href="link.url || '#'" v-html="link.label" class="rounded-lg border px-3 py-2 text-sm" :class="link.active ? 'border-aski-700 bg-aski-700 text-white' : 'border-aski-100 bg-white text-aski-700'" />
        </div>
    </AdminLayout>
</template>
