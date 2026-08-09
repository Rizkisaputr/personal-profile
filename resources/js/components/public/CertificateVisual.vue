<script setup lang="ts">
import { computed } from 'vue';
import type { Certificate } from '@/types';

const props = defineProps<{ certificate: Certificate }>();
const issuerInitials = computed(() =>
    (props.certificate.issuer || props.certificate.title)
        .split(/\s+/)
        .slice(0, 2)
        .map((word) => word[0])
        .join('')
        .toUpperCase(),
);
</script>

<template>
    <div
        class="aspect-[4/3] overflow-hidden rounded-xl border border-border bg-card"
    >
        <img
            v-if="certificate.image_url"
            :src="certificate.image_url"
            :alt="`${certificate.title} certificate`"
            loading="lazy"
            decoding="async"
            class="h-full w-full object-cover"
        />
        <div v-else class="relative flex h-full flex-col justify-between p-4">
            <div class="absolute top-0 right-4 h-8 w-px bg-primary" />
            <span
                class="text-[0.55rem] font-semibold tracking-[0.15em] text-muted-foreground uppercase"
                >Credential</span
            >
            <span
                class="text-2xl font-semibold tracking-[-0.05em] text-foreground/80"
                >{{ issuerInitials }}</span
            >
            <span class="h-px w-8 bg-primary" />
        </div>
    </div>
</template>
