<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Daftar Member" />

    <div class="min-h-screen bg-white lg:grid lg:grid-cols-[520px_1fr]">
        <main class="flex min-h-screen items-center justify-center bg-slate-50 px-5 py-10 sm:px-8">
            <div class="w-full max-w-md">
                <div class="mb-8">
                    <Link :href="route('home')" class="inline-flex items-center gap-3">
                        <span class="grid h-11 w-11 place-items-center rounded-lg bg-aski-900 text-lg font-black text-gold-400">A</span>
                        <span>
                            <span class="block text-lg font-bold text-aski-900">ASKI-PTMA</span>
                            <span class="text-xs font-semibold uppercase tracking-widest text-gold-600">Pendaftaran Member</span>
                        </span>
                    </Link>
                </div>

                <div class="rounded-lg border border-aski-100 bg-white p-6 shadow-sm sm:p-8">
                    <p class="text-sm font-semibold text-aski-700">BUAT AKUN MEMBER</p>
                    <h1 class="mt-2 text-3xl font-bold text-aski-900">Mulai bergabung dengan ASKI-PTMA</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Setelah akun dibuat, Anda akan diarahkan ke dashboard untuk melengkapi profil individu atau institusi.</p>

                    <form class="mt-7 space-y-5" @submit.prevent="submit">
                        <div>
                            <label for="name" class="text-sm font-semibold text-aski-900">Nama Lengkap</label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                autofocus
                                autocomplete="name"
                                class="mt-2 block w-full rounded-lg border-slate-300 focus:border-aski-700 focus:ring-aski-700"
                                placeholder="Nama sesuai identitas"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <label for="email" class="text-sm font-semibold text-aski-900">Email</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autocomplete="username"
                                class="mt-2 block w-full rounded-lg border-slate-300 focus:border-aski-700 focus:ring-aski-700"
                                placeholder="nama@email.com"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div>
                            <label for="password" class="text-sm font-semibold text-aski-900">Kata Sandi</label>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                required
                                autocomplete="new-password"
                                class="mt-2 block w-full rounded-lg border-slate-300 focus:border-aski-700 focus:ring-aski-700"
                                placeholder="Minimal 8 karakter"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div>
                            <label for="password_confirmation" class="text-sm font-semibold text-aski-900">Konfirmasi Kata Sandi</label>
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                required
                                autocomplete="new-password"
                                class="mt-2 block w-full rounded-lg border-slate-300 focus:border-aski-700 focus:ring-aski-700"
                                placeholder="Ulangi kata sandi"
                            />
                            <InputError class="mt-2" :message="form.errors.password_confirmation" />
                        </div>

                        <button
                            type="submit"
                            class="w-full rounded-lg bg-aski-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-aski-800 disabled:opacity-60"
                            :disabled="form.processing"
                        >
                            Daftar sebagai Member
                        </button>
                    </form>

                    <p class="mt-6 text-center text-sm text-slate-500">
                        Sudah punya akun?
                        <Link :href="route('login')" class="font-semibold text-gold-600 hover:text-gold-700">Masuk sekarang</Link>
                    </p>
                </div>
            </div>
        </main>

        <section class="relative hidden overflow-hidden bg-aski-900 px-12 py-10 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="absolute inset-x-0 bottom-0 h-48 bg-gradient-to-t from-aski-800 to-transparent"></div>
            <div class="relative flex items-center justify-between">
                <Link :href="route('home')" class="inline-flex items-center gap-3">
                    <span class="grid h-12 w-12 place-items-center rounded-lg bg-gold-400 text-xl font-black text-aski-900">A</span>
                    <span>
                        <span class="block text-xl font-bold">ASKI-PTMA</span>
                        <span class="text-sm font-medium text-aski-100">Asosiasi Sentra KI PTMA</span>
                    </span>
                </Link>
                <Link :href="route('login')" class="rounded-lg border border-white/20 px-4 py-2 text-sm font-semibold text-white hover:bg-white/10">Masuk</Link>
            </div>

            <div class="relative max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-gold-400">Alur Member</p>
                <h2 class="mt-4 text-5xl font-bold leading-tight">Daftar akun, lengkapi profil, lalu ajukan keanggotaan.</h2>
                <p class="mt-5 max-w-xl text-lg leading-8 text-aski-100">Portal member membantu Anda mengelola profil, permohonan keanggotaan, tagihan, pembayaran, dan data HKI secara tertata.</p>
            </div>

            <div class="relative grid grid-cols-3 gap-4">
                <div class="rounded-lg border border-white/10 bg-white/10 p-4">
                    <p class="text-2xl font-bold text-gold-400">1</p>
                    <p class="mt-2 text-sm text-aski-100">Buat akun</p>
                </div>
                <div class="rounded-lg border border-white/10 bg-white/10 p-4">
                    <p class="text-2xl font-bold text-gold-400">2</p>
                    <p class="mt-2 text-sm text-aski-100">Lengkapi profil</p>
                </div>
                <div class="rounded-lg border border-white/10 bg-white/10 p-4">
                    <p class="text-2xl font-bold text-gold-400">3</p>
                    <p class="mt-2 text-sm text-aski-100">Ajukan keanggotaan</p>
                </div>
            </div>
        </section>
    </div>
</template>
