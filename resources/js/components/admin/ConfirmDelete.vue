<script setup lang="ts">
import { useTemplateRef } from 'vue';
import { Button } from '@/components/ui/button';

withDefaults(
    defineProps<{
        title: string;
        description?: string;
        processing?: boolean;
        triggerLabel?: string;
    }>(),
    {
        description: 'This action cannot be undone.',
        processing: false,
        triggerLabel: 'Delete',
    },
);

const emit = defineEmits<{ confirm: [] }>();
const dialog = useTemplateRef<HTMLDialogElement>('dialog');

function confirm(): void {
    emit('confirm');
    dialog.value?.close();
}
</script>

<template>
    <Button
        variant="ghost"
        size="sm"
        class="text-destructive hover:bg-destructive/5 hover:text-destructive"
        @click="dialog?.showModal()"
    >
        {{ triggerLabel }}
    </Button>
    <dialog
        ref="dialog"
        class="m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl border border-border bg-card p-0 text-foreground shadow-xl backdrop:bg-black/25"
    >
        <div class="p-6 sm:p-7">
            <h2 class="text-lg font-semibold tracking-[-0.02em]">
                {{ title }}
            </h2>
            <p class="mt-2 text-sm leading-6 text-muted-foreground">
                {{ description }}
            </p>
            <div class="mt-7 flex justify-end gap-3">
                <Button
                    variant="outline"
                    :disabled="processing"
                    @click="dialog?.close()"
                    >Cancel</Button
                >
                <Button
                    variant="destructive"
                    :disabled="processing"
                    @click="confirm"
                    >{{ processing ? 'Deleting…' : 'Delete' }}</Button
                >
            </div>
        </div>
    </dialog>
</template>
