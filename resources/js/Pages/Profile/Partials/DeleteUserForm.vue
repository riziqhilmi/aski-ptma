<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-semibold text-aski-900">Hapus akun</h2>

            <p class="mt-1 text-sm leading-6 text-slate-600">
                Penghapusan akun bersifat permanen. Pastikan Anda telah menyimpan informasi yang masih diperlukan.
            </p>
        </header>

        <DangerButton class="rounded-lg px-4 py-2.5 text-sm normal-case tracking-normal" @click="confirmUserDeletion">Hapus akun</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <form class="overflow-hidden rounded-2xl" @submit.prevent="deleteUser">
                <div class="p-5 sm:p-6">
                    <div class="flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-700" aria-hidden="true">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.72-1.36 3.486 0l6.518 11.59c.75 1.333-.213 2.99-1.742 2.99H3.48c-1.53 0-2.492-1.657-1.743-2.99l6.52-11.59ZM10 7a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 7Zm0 7.5a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" /></svg>
                        </span>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Konfirmasi penghapusan akun</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-600">Seluruh data akun akan dihapus permanen. Masukkan kata sandi Anda untuk memastikan tindakan ini memang disengaja.</p>
                        </div>
                    </div>

                    <div class="mt-5">
                        <InputLabel for="password" value="Kata sandi saat ini" />

                        <TextInput
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            class="mt-2 block min-h-11 w-full rounded-lg"
                            autocomplete="current-password"
                        />

                        <InputError :message="form.errors.password" class="mt-2" />
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
                    <SecondaryButton class="min-h-11 justify-center rounded-lg px-4" :disabled="form.processing" @click="closeModal">Batal</SecondaryButton>

                    <DangerButton
                        type="submit"
                        class="min-h-11 justify-center rounded-lg px-4 text-sm normal-case tracking-normal"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Menghapus…' : 'Hapus akun permanen' }}
                    </DangerButton>
                </div>
            </form>
        </Modal>
    </section>
</template>
