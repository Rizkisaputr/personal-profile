<script setup lang="ts">
withDefaults(
    defineProps<{
        active: boolean;
        activeLabel?: string;
        inactiveLabel?: string;
    }>(),
    {
        activeLabel: 'Active',
        inactiveLabel: 'Inactive',
    },
);
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 rounded-md border px-2 py-1 text-xs font-medium"
        :class="
            active
                ? 'border-primary/15 bg-primary/6 text-primary'
                : 'border-border bg-muted/60 text-muted-foreground'
        "
    >
        <span
            class="size-1.5 rounded-full"
            :class="active ? 'bg-primary' : 'bg-[#a3a3a3]'"
        />
        {{ active ? activeLabel : inactiveLabel }}
    </span>
</template>
