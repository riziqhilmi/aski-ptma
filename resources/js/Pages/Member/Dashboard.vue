<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { formatDateOnly } from '@/utils/date.js';

const props = defineProps({
    summary: {
        type: Object,
        default: () => ({
            membership_status: 'Belum mengajukan',
            hki_count: 0,
            unpaid_invoices: 0,
            unpaid_amount: 0,
            active_period: '-',
        }),
    },
    latestApplication: { type: Object, default: null },
    latestInvoices: { type: Array, default: () => [] },
    latestHki: { type: Array, default: () => [] },
    statusLabels: { type: Object, default: () => ({ applications: {}, invoices: {} }) },
    nextAction: {
        type: Object,
        default: () => ({ label: 'Lihat permohonan keanggotaan', route: 'member.applications.index' }),
    },
    monthlyTrends: { type: Array, default: () => [] },
});

const money = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0);
const formatMembershipPeriod = (period) => {
    if (!period || period === '-') return 'Belum ada periode aktif';
    const [startsAt, endsAt] = period.split(' s.d. ');
    return `${formatDateOnly(startsAt)} s.d. ${formatDateOnly(endsAt)}`;
};

const trendMax = computed(() => Math.max(1, ...props.monthlyTrends.flatMap((month) => [month.invoices, month.hki])));
const trendTotal = computed(() => props.monthlyTrends.reduce((totals, month) => ({
    invoices: totals.invoices + Number(month.invoices || 0),
    hki: totals.hki + Number(month.hki || 0),
}), { invoices: 0, hki: 0 }));
const barHeight = (value) => `${value > 0 ? Math.max(8, (value / trendMax.value) * 100) : 2}%`;
</script>

<template>
    <Head title="Dashboard Member" />
    <MemberLayout>
        <div class="space-y-7">
            <header class="flex flex-col gap-4 rounded-2xl border border-aski-100 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-7">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-aski-700">Ringkasan Member</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-aski-900 sm:text-3xl">Dashboard</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Pantau keanggotaan, kewajiban pembayaran, dan aktivitas data HKI Anda.</p>
                </div>
                <Link :href="nextAction.route === 'member.invoices.show' ? route(nextAction.route, nextAction.id) : route(nextAction.route)" class="btn-primary inline-flex min-h-11 items-center justify-center gap-2 self-start sm:self-auto">
                    {{ nextAction.label }}
                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69l-3.22-3.22a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 0 1-1.06-1.06l3.22-3.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                </Link>
            </header>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan akun">
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Status keanggotaan</p>
                    <p class="mt-3 text-xl font-bold text-aski-900">{{ summary.membership_status }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ summary.active_period !== '-' ? formatMembershipPeriod(summary.active_period) : 'Status terkini pada permohonan Anda' }}</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Data HKI</p>
                    <p class="mt-3 text-3xl font-bold tracking-tight text-aski-900">{{ summary.hki_count }}</p>
                    <Link :href="route('member.intellectual-properties.index')" class="mt-2 inline-flex text-xs font-semibold text-aski-700 hover:text-aski-900">Lihat data HKI <span aria-hidden="true" class="ml-1">→</span></Link>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Tagihan belum dibayar</p>
                    <p class="mt-3 text-3xl font-bold tracking-tight text-aski-900">{{ summary.unpaid_invoices }}</p>
                    <Link :href="route('member.invoices.index')" class="mt-2 inline-flex text-xs font-semibold text-aski-700 hover:text-aski-900">Lihat tagihan <span aria-hidden="true" class="ml-1">→</span></Link>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Total belum dibayar</p>
                    <p class="mt-3 break-words text-xl font-bold tracking-tight text-aski-900">{{ money(summary.unpaid_amount) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Akumulasi tagihan berstatus belum dibayar</p>
                </article>
            </section>

            <section class="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(280px,0.8fr)]">
                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="trend-heading">
                    <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-start sm:justify-between sm:px-6">
                        <div>
                            <h2 id="trend-heading" class="text-lg font-bold text-aski-900">Tren aktivitas</h2>
                            <p class="mt-1 text-sm text-slate-500">Tagihan terbit dan data HKI diajukan per bulan, enam bulan terakhir.</p>
                        </div>
                        <span class="inline-flex w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ trendTotal.invoices }} tagihan · {{ trendTotal.hki }} HKI</span>
                    </div>

                    <div class="p-5 sm:p-6">
                        <div class="mb-4 flex flex-wrap gap-x-5 gap-y-2 text-xs font-medium text-slate-600" aria-hidden="true">
                            <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-sm bg-aski-700"></span>Tagihan terbit</span>
                            <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-sm bg-gold-400"></span>Data HKI</span>
                        </div>
                        <div role="img" :aria-label="`Grafik tren enam bulan: ${trendTotal.invoices} tagihan terbit dan ${trendTotal.hki} data HKI diajukan`" class="grid grid-cols-6 gap-2 border-b border-slate-200 sm:gap-4">
                            <div v-for="month in monthlyTrends" :key="month.key" class="flex min-w-0 flex-col items-center">
                                <div class="flex h-36 w-full items-end justify-center gap-1 border-b border-slate-100 sm:gap-2">
                                    <div class="flex h-full w-1/3 max-w-7 items-end" :title="`${month.invoices} tagihan terbit`"><span class="w-full rounded-t bg-aski-700 transition-[height]" :style="{ height: barHeight(month.invoices) }"></span></div>
                                    <div class="flex h-full w-1/3 max-w-7 items-end" :title="`${month.hki} data HKI`"><span class="w-full rounded-t bg-gold-400 transition-[height]" :style="{ height: barHeight(month.hki) }"></span></div>
                                </div>
                                <span class="py-2 text-xs font-medium text-slate-500">{{ month.label }}</span>
                            </div>
                        </div>
                        <p v-if="trendTotal.invoices === 0 && trendTotal.hki === 0" class="mt-4 text-center text-sm text-slate-500">Belum ada aktivitas tagihan atau HKI dalam enam bulan terakhir.</p>
                        <p v-else class="mt-4 text-xs leading-5 text-slate-500">Grafik menunjukkan jumlah berdasarkan tanggal penerbitan tagihan dan tanggal permohonan HKI.</p>
                    </div>
                </article>

                <aside class="rounded-2xl border border-aski-100 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="insight-heading">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gold-400/20 text-aski-800" aria-hidden="true">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 18h6m-5 3h4m-2-19a7 7 0 0 0-4.2 12.6c.72.54 1.2 1.15 1.2 2.15h6c0-1 .48-1.61 1.2-2.15A7 7 0 0 0 12 2Z"/></svg>
                        </span>
                        <div>
                            <h2 id="insight-heading" class="text-lg font-bold text-aski-900">Insight untuk Anda</h2>
                            <p class="text-xs text-slate-500">Ringkasan berdasarkan data akun</p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-3">
                        <div v-if="summary.unpaid_invoices > 0" class="rounded-xl border border-gold-400 bg-gold-100/70 p-4">
                            <p class="text-sm font-semibold text-aski-900">Ada {{ summary.unpaid_invoices }} tagihan yang perlu ditindaklanjuti</p>
                            <p class="mt-1 text-sm leading-5 text-slate-700">Total kewajiban saat ini {{ money(summary.unpaid_amount) }}.</p>
                            <Link :href="route('member.invoices.index')" class="mt-3 inline-flex text-sm font-semibold text-aski-800 underline underline-offset-2">Periksa tagihan</Link>
                        </div>
                        <div v-else class="rounded-xl border border-aski-100 bg-aski-50/70 p-4">
                            <p class="text-sm font-semibold text-aski-900">Tidak ada tagihan belum dibayar</p>
                            <p class="mt-1 text-sm leading-5 text-slate-600">Berdasarkan status invoice yang tercatat saat ini.</p>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Aktivitas 6 bulan</p>
                            <p class="mt-2 text-sm leading-6 text-slate-700">{{ trendTotal.hki }} data HKI diajukan dan {{ trendTotal.invoices }} tagihan diterbitkan.</p>
                        </div>

                        <div v-if="latestApplication" class="rounded-xl border border-slate-200 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Permohonan terakhir</p>
                            <p class="mt-2 text-sm font-semibold text-slate-800">{{ statusLabels.applications[latestApplication.status] ?? 'Status tidak dikenal' }}</p>
                            <Link :href="route('member.applications.index')" class="mt-2 inline-flex text-sm font-semibold text-aski-700 hover:text-aski-900">Lihat permohonan <span aria-hidden="true" class="ml-1">→</span></Link>
                        </div>
                        <div v-else class="rounded-xl border border-slate-200 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Keanggotaan</p>
                            <p class="mt-2 text-sm font-semibold text-slate-800">{{ summary.membership_status }}</p>
                            <p class="mt-1 text-xs leading-5 text-slate-500">{{ formatMembershipPeriod(summary.active_period) }}</p>
                        </div>
                    </div>
                </aside>
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                        <div><h2 class="text-base font-bold text-aski-900">Tagihan terbaru</h2><p class="mt-0.5 text-xs text-slate-500">Maksimal 5 tagihan terakhir</p></div>
                        <Link :href="route('member.invoices.index')" class="shrink-0 rounded-lg px-3 py-2 text-sm font-semibold text-aski-700 hover:bg-aski-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">Semua tagihan <span aria-hidden="true">→</span></Link>
                    </div>
                    <div class="divide-y divide-slate-100 px-5 sm:px-6">
                        <div v-for="invoice in latestInvoices" :key="invoice.id" class="flex flex-col gap-1 py-4 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                            <div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-900">{{ invoice.number }}</p><p class="mt-1 text-xs text-slate-500">{{ formatDateOnly(invoice.issued_at) }}</p></div>
                            <div class="flex items-center justify-between gap-3 sm:justify-end"><span class="text-sm font-semibold text-slate-800">{{ money(invoice.amount) }}</span><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{{ statusLabels.invoices[invoice.status] ?? 'Status tidak dikenal' }}</span></div>
                        </div>
                        <p v-if="latestInvoices.length === 0" class="py-8 text-center text-sm text-slate-500">Belum ada tagihan.</p>
                    </div>
                </article>

                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                        <div><h2 class="text-base font-bold text-aski-900">Data HKI terbaru</h2><p class="mt-0.5 text-xs text-slate-500">Berdasarkan tanggal permohonan</p></div>
                        <Link :href="route('member.intellectual-properties.index')" class="shrink-0 rounded-lg px-3 py-2 text-sm font-semibold text-aski-700 hover:bg-aski-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700">Semua data <span aria-hidden="true">→</span></Link>
                    </div>
                    <div class="divide-y divide-slate-100 px-5 sm:px-6">
                        <div v-for="record in latestHki" :key="record.id" class="py-4">
                            <p class="break-words text-sm font-semibold text-slate-900">{{ record.title }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ record.ip_type }} · {{ formatDateOnly(record.application_date) }}</p>
                        </div>
                        <p v-if="latestHki.length === 0" class="py-8 text-center text-sm text-slate-500">Belum ada data HKI.</p>
                    </div>
                </article>
            </section>
        </div>
    </MemberLayout>
</template>
