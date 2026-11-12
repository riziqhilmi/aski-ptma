<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ links: Array });
</script>

<template>
    <nav v-if="links?.length > 3" class="mt-5 flex flex-wrap gap-2" aria-label="Paginasi">
        <template v-for="link in links" :key="link.label">
            <Link v-if="link.url" :href="link.url" :aria-current="link.active ? 'page' : undefined" class="rounded-lg border px-3 py-2 text-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aski-700" :class="link.active ? 'border-aski-700 bg-aski-700 text-white' : 'border-aski-100 bg-white text-aski-700'" v-html="link.label" />
            <span v-else aria-disabled="true" class="rounded-lg border border-slate-100 px-3 py-2 text-sm text-slate-400" v-html="link.label" />
        </template>
    </nav>
</template>
