<script setup lang="ts">
import { FileUp } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';

defineProps<{
    id: string;
    label: string;
    accept: string;
    hint: string;
    error?: string;
    currentName?: string | null;
}>();

const emit = defineEmits<{ change: [file: File | null] }>();

function onChange(event: Event): void {
    emit('change', (event.target as HTMLInputElement).files?.[0] ?? null);
}
</script>

<template>
    <div class="grid min-w-0 gap-2">
        <Label :for="id">{{ label }}</Label>
        <p
            v-if="currentName"
            class="max-w-full truncate text-xs text-muted-foreground"
            :title="currentName"
        >
            Berkas saat ini: {{ currentName }}
        </p>
        <p class="text-xs leading-5 text-muted-foreground">{{ hint }}</p>
        <label
            :for="id"
            class="flex cursor-pointer items-center gap-3 rounded-xl border border-dashed border-input bg-background-alt px-4 py-4 transition-colors hover:border-primary/50"
        >
            <span
                class="grid size-9 shrink-0 place-items-center rounded-lg bg-muted text-muted-foreground"
                ><FileUp class="size-4"
            /></span>
            <span class="min-w-0 text-sm font-medium">
                {{ currentName ? 'Ganti berkas' : 'Pilih berkas' }}
            </span>
            <input
                :id="id"
                type="file"
                :accept="accept"
                class="sr-only"
                @change="onChange"
            />
        </label>
        <InputError :message="error" />
    </div>
</template>
