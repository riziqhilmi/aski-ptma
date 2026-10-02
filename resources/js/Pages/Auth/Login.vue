<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk" />

    <div class="min-h-screen bg-white lg:grid lg:grid-cols-[1fr_520px]">
        <section class="relative hidden overflow-hidden bg-aski-900 px-12 py-10 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="absolute inset-x-0 bottom-0 h-48 bg-gradient-to-t from-aski-800 to-transparent"></div>
            <div class="relative">
                <Link :href="route('home')" class="inline-flex items-center gap-3">
                    <span class="grid h-12 w-12 place-items-center rounded-lg bg-gold-400 text-xl font-black text-aski-900">A</span>
                    <span>
                        <span class="block text-xl font-bold">ASKI-PTMA</span>
                        <span class="text-sm font-medium text-aski-100">Sistem Informasi Keanggotaan dan HKI</span>
                    </span>
                </Link>
            </div>

            <div class="relative max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-gold-400">Portal Terpadu</p>
                <h1 class="mt-4 text-5xl font-bold leading-tight">Kelola keanggotaan, HKI, tagihan, dan pembayaran dalam satu tempat.</h1>
                <p class="mt-5 max-w-xl text-lg leading-8 text-aski-100">Masuk sebagai Admin untuk verifikasi dan laporan, atau sebagai Member untuk memperbarui profil, memantau tagihan, dan mengelola data HKI.</p>
            </div>

            <div class="relative grid grid-cols-3 gap-4">
                <div class="rounded-lg border border-white/10 bg-white/10 p-4">
                    <p class="text-2xl font-bold text-gold-400">01</p>
                    <p class="mt-2 text-sm text-aski-100">Profil anggota</p>
                </div>
                <div class="rounded-lg border border-white/10 bg-white/10 p-4">
                    <p class="text-2xl font-bold text-gold-400">02</p>
                    <p class="mt-2 text-sm text-aski-100">Data HKI</p>
                </div>
                <div class="rounded-lg border border-white/10 bg-white/10 p-4">
                    <p class="text-2xl font-bold text-gold-400">03</p>
                    <p class="mt-2 text-sm text-aski-100">Pembayaran</p>
                </div>
            </div>
        </section>

        <main class="flex min-h-screen items-center justify-center bg-slate-50 px-5 py-10 sm:px-8">
            <div class="w-full max-w-md">
                <div class="mb-8 lg:hidden">
                    <Link :href="route('home')" class="inline-flex items-center gap-3">
                        <span class="grid h-11 w-11 place-items-center rounded-lg bg-aski-900 text-lg font-black text-gold-400">A</span>
                        <span>
                            <span class="block text-lg font-bold text-aski-900">ASKI-PTMA</span>
                            <span class="text-xs font-semibold uppercase tracking-widest text-gold-600">Portal Member</span>
                        </span>
                    </Link>
                </div>

                <div class="rounded-lg border border-aski-100 bg-white p-6 shadow-sm sm:p-8">
                    <p class="text-sm font-semibold text-aski-700">MASUK AKUN</p>
                    <h2 class="mt-2 text-3xl font-bold text-aski-900">Selamat datang kembali</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Gunakan email dan kata sandi yang terdaftar untuk melanjutkan ke dashboard.</p>

                    <div v-if="status" class="mt-5 rounded-lg border border-aski-100 bg-aski-50 px-4 py-3 text-sm font-medium text-aski-700">
                        {{ status }}
                    </div>

                    <form class="mt-7 space-y-5" @submit.prevent="submit">
                        <div>
                            <label for="email" class="text-sm font-semibold text-aski-900">Email</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autofocus
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
                                autocomplete="current-password"
                                class="mt-2 block w-full rounded-lg border-slate-300 focus:border-aski-700 focus:ring-aski-700"
                                placeholder="Masukkan kata sandi"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <label class="flex items-center">
                                <Checkbox name="remember" v-model:checked="form.remember" />
                                <span class="ms-2 text-sm text-slate-600">Ingat saya</span>
                            </label>

                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-sm font-semibold text-aski-700 hover:text-aski-900"
                            >
                                Lupa kata sandi?
                            </Link>
                        </div>

                        <button
                            type="submit"
                            class="w-full rounded-lg bg-aski-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-aski-800 disabled:opacity-60"
                            :disabled="form.processing"
                        >
                            Masuk
                        </button>
                    </form>

                    <p class="mt-6 text-center text-sm text-slate-500">
                        Belum punya akun?
                        <Link :href="route('register')" class="font-semibold text-gold-600 hover:text-gold-700">Daftar sebagai Member</Link>
                    </p>
                </div>
            </div>
        </main>
    </div>
</template>
