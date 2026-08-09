<script setup lang="ts">
defineProps<{
    id: string;
    modelValue: boolean;
    label: string;
    description?: string;
}>();
const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>();
</script>

<template>
    <label
        :for="id"
        class="flex cursor-pointer items-start gap-3 rounded-xl border border-border bg-card p-4"
    >
        <input
            :id="id"
            type="checkbox"
            :checked="modelValue"
            class="mt-0.5 size-4 rounded border-input accent-[#315cff] focus-visible:ring-2 focus-visible:ring-ring/25"
            @change="
                emit(
                    'update:modelValue',
                    ($event.target as HTMLInputElement).checked,
                )
            "
        />
        <span>
            <span class="block text-sm font-medium">{{ label }}</span>
            <span
                v-if="description"
                class="mt-1 block text-xs leading-5 text-muted-foreground"
                >{{ description }}</span
            >
        </span>
    </label>
</template>
