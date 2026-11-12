<script setup>
import { computed } from 'vue';

const props = defineProps({ status: String, label: String });
const tone = computed(() => {
    if (['paid', 'verified', 'completed', 'active', 'answered', 'published', 'complete'].includes(props.status)) return 'bg-aski-50 text-aski-800';
    if (['rejected', 'closed', 'expired'].includes(props.status)) return 'bg-red-50 text-red-700';
    if (['unpaid', 'submitted', 'approved_waiting_payment', 'revision', 'open', 'draft', 'incomplete', 'inactive', 'upcoming'].includes(props.status)) return 'bg-gold-100 text-aski-900';
    return 'bg-slate-100 text-slate-700';
});
</script>

<template>
    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="tone">{{ label || 'Status tidak dikenal' }}</span>
</template>
