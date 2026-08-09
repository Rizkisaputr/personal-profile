<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';

defineProps<{
    id: string;
    label: string;
    error?: string;
    hint?: string;
    required?: boolean;
}>();
</script>

<template>
    <div class="grid gap-2">
        <Label :for="id">
            {{ label }}
            <span v-if="required" class="text-primary" aria-hidden="true"
                >*</span
            >
        </Label>
        <slot />
        <p
            v-if="hint && !error"
            class="text-xs leading-5 text-muted-foreground"
        >
            {{ hint }}
        </p>
        <InputError :message="error" />
    </div>
</template>
