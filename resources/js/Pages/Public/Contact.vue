<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({ contact: Object });
const metadata = computed(() => props.contact?.metadata || {});
const phoneHref = computed(() => metadata.value.phone ? `tel:${String(metadata.value.phone).replace(/[^\d+]/g, '')}` : null);
const websiteHref = computed(() => {
    const value = String(metadata.value.website || '').trim();
    if (!value) return null;
    return /^https?:\/\//i.test(value) ? value : `https://${value}`;
});
const instagramHref = computed(() => {
    const value = String(metadata.value.instagram || '').trim();
    if (!value) return null;
    return /^https?:\/\//i.test(value) ? value : `https://instagram.com/${value.replace(/^@/, '')}`;
});
</script>

<template>
    <Head title="Kontak ASKI-PTMA" />
    <PublicLayout>
        <section class="bg-aski-900 text-white">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 sm:py-16 lg:grid-cols-[1fr_auto] lg:items-end lg:px-8 lg:py-20">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-gold-400">Terhubung dengan kami</p>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">{{ contact.title }}</h1>
                    <p class="mt-4 max-w-2xl text-base leading-7 text-aski-100">{{ contact.body }}</p>
                </div>
                <div class="hidden rounded-2xl border border-white/15 bg-white/10 p-4 sm:block"><img src="/images/logo-aski.png" alt="Logo ASKI-PTMA" class="h-16 w-16 object-contain" /></div>
            </div>
        </section>

        <section class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[0.8fr_1.2fr] lg:px-8 lg:py-14">
            <aside class="rounded-2xl bg-aski-50 p-6 sm:p-8">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-aski-700">Kami siap membantu</p>
                <h2 class="mt-2 text-2xl font-bold tracking-tight text-aski-900">Mari berdiskusi</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">Pilih kanal komunikasi yang paling sesuai dengan kebutuhan Anda.</p>
                <div class="mt-6 flex items-start gap-3 border-t border-aski-100 pt-5">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-white text-aski-700" aria-hidden="true"><svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M2.94 5.44A2.75 2.75 0 0 1 5.5 3.75h9a2.75 2.75 0 0 1 2.56 1.69L10 10.17 2.94 5.44Z"/><path d="M2.75 7.2v7.3A1.75 1.75 0 0 0 4.5 16.25h11a1.75 1.75 0 0 0 1.75-1.75V7.2l-6.83 4.57a.75.75 0 0 1-.84 0L2.75 7.2Z"/></svg></span>
                    <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">ASKI-PTMA</p><p class="mt-1 text-sm leading-6 text-slate-700">Informasi kontak resmi organisasi tersedia pada halaman ini.</p></div>
                </div>
            </aside>

            <div class="grid gap-4 sm:grid-cols-2">
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-aski-200 hover:shadow-md sm:p-6">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-aski-700">Email</p>
                    <a v-if="metadata.email" :href="`mailto:${metadata.email}`" class="mt-3 inline-flex break-all text-base font-semibold text-aski-900 hover:text-aski-700">{{ metadata.email }}</a>
                    <p v-else class="mt-3 text-sm text-slate-500">Belum tersedia</p>
                    <p class="mt-2 text-sm text-slate-500">Kirim pertanyaan atau korespondensi.</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-aski-200 hover:shadow-md sm:p-6">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-aski-700">Telepon</p>
                    <a v-if="phoneHref" :href="phoneHref" class="mt-3 inline-flex break-all text-base font-semibold text-aski-900 hover:text-aski-700">{{ metadata.phone }}</a>
                    <p v-else class="mt-3 text-sm text-slate-500">Belum tersedia</p>
                    <p class="mt-2 text-sm text-slate-500">Hubungi melalui telepon.</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-aski-200 hover:shadow-md sm:col-span-2 sm:p-6">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-aski-700">Alamat</p>
                    <p class="mt-3 whitespace-pre-line text-base font-semibold leading-7 text-aski-900">{{ metadata.address || 'Belum tersedia' }}</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-aski-200 hover:shadow-md sm:p-6">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-aski-700">Instagram</p>
                    <a v-if="instagramHref" :href="instagramHref" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex break-all text-base font-semibold text-aski-900 hover:text-aski-700">{{ metadata.instagram }}</a>
                    <p v-else class="mt-3 text-sm text-slate-500">Belum tersedia</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-aski-200 hover:shadow-md sm:p-6">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-aski-700">Website</p>
                    <a v-if="websiteHref" :href="websiteHref" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex break-all text-base font-semibold text-aski-900 hover:text-aski-700">{{ metadata.website }}</a>
                    <p v-else class="mt-3 text-sm text-slate-500">Belum tersedia</p>
                </article>
            </div>
        </section>
    </PublicLayout>
</template>
