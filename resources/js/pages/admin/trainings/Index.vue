<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import ConfirmDelete from '@/components/admin/ConfirmDelete.vue';
import EmptyState from '@/components/admin/EmptyState.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import { buttonVariants } from '@/components/ui/button';
import type { Training } from '@/types';

defineProps<{ trainings: Training[] }>();
const deleting = ref<number | null>(null);

function period(item: Training): string {
    const year = (value: string | null): string | null =>
        value ? String(new Date(`${value}T00:00:00`).getFullYear()) : null;
    const start = year(item.start_date);
    const end = year(item.end_date);

    return [start, end].filter(Boolean).join(' — ') || 'Dates not specified';
}

function remove(item: Training): void {
    deleting.value = item.id;
    router.delete(`/admin/trainings/${item.id}`, {
        onFinish: () => (deleting.value = null),
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Training & Courses" />
    <PageHeader
        eyebrow="Content"
        title="Training & Courses"
        description="Maintain non-formal learning, courses, workshops, and professional development."
    >
        <template #actions
            ><Link href="/admin/trainings/create" :class="buttonVariants()"
                ><Plus class="size-4" />Add training</Link
            ></template
        >
    </PageHeader>
    <EmptyState
        v-if="trainings.length === 0"
        class="mt-8"
        title="No training yet"
        description="Add your first course or professional development entry."
    >
        <Link href="/admin/trainings/create" :class="buttonVariants()"
            >Add training</Link
        >
    </EmptyState>
    <div v-else class="surface-panel mt-8 overflow-hidden">
        <div
            v-for="item in trainings"
            :key="item.id"
            class="grid gap-4 border-b border-border px-5 py-5 last:border-b-0 lg:grid-cols-[1.3fr_1fr_1fr_9rem_auto] lg:items-center lg:px-6"
        >
            <div>
                <p class="text-sm font-medium">{{ item.program_name }}</p>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ item.institution }}
                </p>
            </div>
            <p class="text-sm text-muted-foreground">
                {{ item.field_of_study || 'No field specified' }}
            </p>
            <p class="text-sm text-muted-foreground">{{ period(item) }}</p>
            <StatusBadge :active="item.is_active" />
            <div class="flex gap-1 lg:justify-end">
                <Link
                    :href="`/admin/trainings/${item.id}/edit`"
                    :class="buttonVariants({ variant: 'ghost', size: 'sm' })"
                    >Edit</Link
                >
                <ConfirmDelete
                    title="Delete training?"
                    :processing="deleting === item.id"
                    @confirm="remove(item)"
                />
            </div>
        </div>
    </div>
</template>
