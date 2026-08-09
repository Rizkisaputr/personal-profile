<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import ConfirmDelete from '@/components/admin/ConfirmDelete.vue';
import EmptyState from '@/components/admin/EmptyState.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import { buttonVariants } from '@/components/ui/button';
import type { Experience } from '@/types';

defineProps<{ experiences: Experience[] }>();
const deleting = ref<number | null>(null);
const year = (date: string | null): string =>
    date ? new Date(date).getFullYear().toString() : 'Present';
function remove(item: Experience): void {
    deleting.value = item.id;
    router.delete(`/admin/experiences/${item.id}`, {
        onFinish: () => (deleting.value = null),
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Experience" />
    <PageHeader
        eyebrow="Content"
        title="Experience"
        description="Maintain a clear timeline of roles and meaningful professional work."
    >
        <template #actions
            ><Link href="/admin/experiences/create" :class="buttonVariants()"
                ><Plus class="size-4" />Add experience</Link
            ></template
        >
    </PageHeader>
    <EmptyState
        v-if="experiences.length === 0"
        class="mt-8"
        title="No experience yet"
        description="Add the first role in your professional timeline."
        ><Link href="/admin/experiences/create" :class="buttonVariants()"
            >Add experience</Link
        ></EmptyState
    >
    <div v-else class="surface-panel mt-8 overflow-hidden">
        <div
            v-for="item in experiences"
            :key="item.id"
            class="grid gap-4 border-b border-border px-5 py-5 last:border-b-0 lg:grid-cols-[1.4fr_1fr_9rem_8rem] lg:items-center lg:px-6"
        >
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <p class="text-sm font-medium">{{ item.position }}</p>
                    <StatusBadge :active="item.is_active" />
                </div>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ item.company_name }}
                </p>
            </div>
            <p class="text-sm text-muted-foreground">
                {{ year(item.start_date) }} —
                {{ item.is_current ? 'Present' : year(item.end_date) }}
            </p>
            <StatusBadge
                :active="item.is_current"
                active-label="Current"
                inactive-label="Completed"
            />
            <div class="flex gap-1 lg:justify-end">
                <Link
                    :href="`/admin/experiences/${item.id}/edit`"
                    :class="buttonVariants({ variant: 'ghost', size: 'sm' })"
                    >Edit</Link
                ><ConfirmDelete
                    title="Delete experience?"
                    :processing="deleting === item.id"
                    @confirm="remove(item)"
                />
            </div>
        </div>
    </div>
</template>
