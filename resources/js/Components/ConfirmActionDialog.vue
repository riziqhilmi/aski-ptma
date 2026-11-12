<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: 'Konfirmasi tindakan' },
    description: { type: String, default: 'Tindakan ini tidak dapat dibatalkan.' },
    item: { type: String, default: '' },
    actionLabel: { type: String, default: 'Hapus' },
    phrase: { type: String, default: 'HAPUS' },
    processing: { type: Boolean, default: false },
});

const emit = defineEmits(['confirm', 'close']);
const confirmation = ref('');
const confirmationInput = ref(null);
const canConfirm = computed(() => confirmation.value.trim() === props.phrase && !props.processing);

watch(() => props.show, (isOpen) => {
    if (isOpen) {
        confirmation.value = '';
        nextTick(() => confirmationInput.value?.focus());
    }
});

const close = () => {
    if (!props.processing) emit('close');
};
</script>

<template>
    <Modal :show="show" max-width="md" :closeable="!processing" @close="close">
        <form class="overflow-hidden rounded-2xl" @submit.prevent="canConfirm && emit('confirm')">
            <div class="p-5 sm:p-6">
                <div class="flex items-start gap-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-700" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.72-1.36 3.486 0l6.518 11.59c.75 1.333-.213 2.99-1.742 2.99H3.48c-1.53 0-2.492-1.657-1.743-2.99l6.52-11.59ZM10 7a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 7Zm0 7.5a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" /></svg>
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-lg font-bold text-slate-900">{{ title }}</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-600">{{ description }}</p>
                        <p v-if="item" class="mt-3 break-words rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-800">{{ item }}</p>
                    </div>
                </div>

                <div class="mt-5">
                    <label for="confirm-action-phrase" class="block text-sm font-semibold text-slate-700">
                        Ketik <span class="font-bold text-red-700">{{ phrase }}</span> untuk melanjutkan
                    </label>
                    <input
                        id="confirm-action-phrase"
                        ref="confirmationInput"
                        v-model="confirmation"
                        type="text"
                        autocomplete="off"
                        :disabled="processing"
                        class="mt-2 min-h-11 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-aski-700 focus:ring-aski-700 disabled:bg-slate-100"
                        :aria-invalid="confirmation.length > 0 && !canConfirm"
                    />
                </div>
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
                <button type="button" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700 disabled:opacity-60" :disabled="processing" @click="close">Batal</button>
                <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-red-700 px-4 text-sm font-semibold text-white hover:bg-red-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-700 disabled:cursor-not-allowed disabled:opacity-50" :disabled="!canConfirm">
                    {{ processing ? 'Memproses…' : actionLabel }}
                </button>
            </div>
        </form>
    </Modal>
</template>
