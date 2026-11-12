<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);
const confirmationInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            } else if (form.errors.password_confirmation) {
                confirmationInput.value?.focus();
            } else if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
            <h2 class="text-lg font-bold text-aski-900">Ubah password</h2>
            <p class="mt-1 text-sm text-slate-500">Gunakan password saat ini untuk mengatur password baru.</p>
        </div>

        <form class="space-y-5 p-5 sm:p-6" @submit.prevent="updatePassword">
            <div>
                <label for="current_password" class="block text-sm font-semibold text-slate-800">Password saat ini <span class="text-red-600" aria-hidden="true">*</span></label>
                <input id="current_password" ref="currentPasswordInput" v-model="form.current_password" type="password" required aria-required="true" autocomplete="current-password" :aria-invalid="Boolean(form.errors.current_password)" :aria-describedby="form.errors.current_password ? 'current-password-error' : undefined" class="mt-1.5 min-h-11 w-full rounded-lg border-slate-300 text-sm focus:border-aski-500 focus:ring-aski-500" />
                <p v-if="form.errors.current_password" id="current-password-error" class="mt-1.5 text-xs text-red-600">{{ form.errors.current_password }}</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="new_password" class="block text-sm font-semibold text-slate-800">Password baru <span class="text-red-600" aria-hidden="true">*</span></label>
                    <input id="new_password" ref="passwordInput" v-model="form.password" type="password" required aria-required="true" autocomplete="new-password" :aria-invalid="Boolean(form.errors.password)" :aria-describedby="form.errors.password ? 'new-password-error' : 'password-help'" class="mt-1.5 min-h-11 w-full rounded-lg border-slate-300 text-sm focus:border-aski-500 focus:ring-aski-500" />
                    <p v-if="form.errors.password" id="new-password-error" class="mt-1.5 text-xs text-red-600">{{ form.errors.password }}</p>
                    <p v-else id="password-help" class="mt-1.5 text-xs text-slate-500">Ikuti ketentuan keamanan password aplikasi.</p>
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-slate-800">Konfirmasi password baru <span class="text-red-600" aria-hidden="true">*</span></label>
                    <input id="password_confirmation" ref="confirmationInput" v-model="form.password_confirmation" type="password" required aria-required="true" autocomplete="new-password" :aria-invalid="Boolean(form.errors.password_confirmation)" :aria-describedby="form.errors.password_confirmation ? 'password-confirmation-error' : undefined" class="mt-1.5 min-h-11 w-full rounded-lg border-slate-300 text-sm focus:border-aski-500 focus:ring-aski-500" />
                    <p v-if="form.errors.password_confirmation" id="password-confirmation-error" class="mt-1.5 text-xs text-red-600">{{ form.errors.password_confirmation }}</p>
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500"><span class="font-bold text-red-600">*</span> Semua kolom wajib diisi.</p>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <p v-if="form.recentlySuccessful" role="status" class="text-sm font-medium text-aski-800">Password berhasil diperbarui.</p>
                    <button type="submit" class="btn-primary inline-flex min-h-11 items-center justify-center" :disabled="form.processing">{{ form.processing ? 'Memperbarui…' : 'Perbarui password' }}</button>
                </div>
            </div>
        </form>
    </section>
</template>
