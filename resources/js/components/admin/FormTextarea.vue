<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

defineProps<{
    modelValue?: string | null;
    class?: HTMLAttributes['class'];
    rows?: number;
}>();
const emit = defineEmits<{ 'update:modelValue': [value: string] }>();
</script>

<template>
    <textarea
        :value="modelValue ?? ''"
        :rows="rows ?? 5"
        :class="
            cn(
                'w-full resize-y rounded-[10px] border border-input bg-card px-3.5 py-3 text-sm leading-6 transition-[border-color,box-shadow] duration-200 outline-none placeholder:text-[#a3a3a3] focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/15',
                $props.class,
            )
        "
        @input="
            emit(
                'update:modelValue',
                ($event.target as HTMLTextAreaElement).value,
            )
        "
    />
</template>
