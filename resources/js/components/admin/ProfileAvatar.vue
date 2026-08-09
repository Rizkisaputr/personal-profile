<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        name: string;
        imageUrl?: string | null;
        alt?: string;
    }>(),
    { imageUrl: null, alt: '' },
);

const initials = computed(() =>
    props.name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase(),
);
</script>

<template>
    <span class="relative grid shrink-0 place-items-center overflow-hidden rounded-full border border-border bg-background-warm font-semibold text-foreground">
        <img v-if="imageUrl" :src="imageUrl" :alt="alt" class="h-full w-full object-cover object-center" decoding="async" />
        <span v-else aria-hidden="true">{{ initials || 'PP' }}</span>
    </span>
</template>
