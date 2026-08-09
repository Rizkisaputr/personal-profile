<script setup lang="ts">
defineProps<{
    eyebrow?: string;
    title: string;
    description?: string;
}>();
</script>

<template>
    <header
        class="flex flex-col gap-5 border-b border-border pb-7 sm:flex-row sm:items-end sm:justify-between"
    >
        <div>
            <p v-if="eyebrow" class="editorial-kicker mb-3">{{ eyebrow }}</p>
            <h1 class="editorial-heading">{{ title }}</h1>
            <p v-if="description" class="editorial-body mt-3 max-w-2xl">
                {{ description }}
            </p>
        </div>
        <div
            v-if="$slots.actions"
            class="flex shrink-0 flex-wrap items-center gap-3"
        >
            <slot name="actions" />
        </div>
    </header>
</template>
