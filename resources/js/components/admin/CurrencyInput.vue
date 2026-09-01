<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        modelValue?: string | number | null;
        allowNegative?: boolean;
        class?: HTMLAttributes['class'];
    }>(),
    {
        modelValue: '',
        allowNegative: false,
    },
);

const emit = defineEmits<{
    (event: 'update:modelValue', value: string): void;
}>();

function integerPart(value: string | number | null | undefined): string {
    const raw = String(value ?? '').trim();
    const backendDecimal = raw.match(/^(-?\d+)[.,]\d{1,2}$/);

    if (backendDecimal) {
        return backendDecimal[1];
    }

    const negative = props.allowNegative && raw.startsWith('-');
    const digits = raw.replace(/\D/g, '');

    return `${negative ? '-' : ''}${digits}`;
}

function format(value: string | number | null | undefined): string {
    const normalized = integerPart(value);
    const negative = normalized.startsWith('-');
    const digits = normalized.replace(/\D/g, '');

    if (!digits) {
        return '';
    }

    return `${negative ? '-' : ''}${digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.')}`;
}

const displayValue = computed(() => format(props.modelValue));

function update(event: Event): void {
    const input = event.target as HTMLInputElement;
    const negative = props.allowNegative && input.value.trim().startsWith('-');
    const digits = input.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '');
    const value = `${negative && digits ? '-' : ''}${digits}`;

    input.value = format(value);
    emit('update:modelValue', value);
}
</script>

<template>
    <input
        :value="displayValue"
        type="text"
        inputmode="numeric"
        autocomplete="off"
        data-slot="input"
        :class="
            cn(
                'h-11 w-full min-w-0 rounded-[10px] border border-input bg-card px-3.5 py-2 text-base tabular-nums shadow-none transition-[border-color,box-shadow] duration-200 outline-none selection:bg-primary selection:text-primary-foreground placeholder:text-[#a3a3a3] disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                'focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/15',
                'aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40',
                props.class,
            )
        "
        @input="update"
    />
</template>
