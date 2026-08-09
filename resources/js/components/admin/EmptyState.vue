<script setup lang="ts">
import { FileText } from '@lucide/vue';

defineProps<{ title: string; description: string }>();
</script>

<template>
    <div
        class="surface-panel flex flex-col items-center px-6 py-14 text-center"
    >
        <div
            class="grid size-10 place-items-center rounded-xl bg-muted text-muted-foreground"
        >
            <FileText class="size-4" />
        </div>
        <h2 class="mt-5 text-base font-semibold tracking-[-0.015em]">
            {{ title }}
        </h2>
        <p class="mt-2 max-w-md text-sm leading-6 text-muted-foreground">
            {{ description }}
        </p>
        <div v-if="$slots.default" class="mt-6"><slot /></div>
    </div>
</template>
