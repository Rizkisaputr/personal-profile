<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import ConfirmDelete from '@/components/admin/ConfirmDelete.vue';
import EmptyState from '@/components/admin/EmptyState.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { buttonVariants } from '@/components/ui/button';
import type { Technology } from '@/types';
defineProps<{ technologies: Technology[] }>();
const deleting = ref<number | null>(null);
function remove(item: Technology): void {
    deleting.value = item.id;
    router.delete(`/admin/technologies/${item.id}`, {
        onFinish: () => (deleting.value = null),
    });
}
</script>
<template>
    <Head title="Technologies" /><PageHeader
        eyebrow="Portfolio"
        title="Technologies"
        description="Maintain the concise technology vocabulary used across projects."
        ><template #actions
            ><Link href="/admin/technologies/create" :class="buttonVariants()"
                ><Plus class="size-4" />Add technology</Link
            ></template
        ></PageHeader
    ><EmptyState
        v-if="technologies.length === 0"
        class="mt-8"
        title="No technologies yet"
        description="Add the first technology before connecting it to projects."
        ><Link href="/admin/technologies/create" :class="buttonVariants()"
            >Add technology</Link
        ></EmptyState
    >
    <div v-else class="surface-panel mt-8 overflow-hidden">
        <div
            v-for="item in technologies"
            :key="item.id"
            class="grid gap-3 border-b border-border px-5 py-5 last:border-0 sm:grid-cols-[1fr_1fr_9rem_8rem] sm:items-center sm:px-6"
        >
            <p class="text-sm font-medium">{{ item.name }}</p>
            <code class="text-xs text-muted-foreground">{{ item.slug }}</code>
            <p class="text-sm text-muted-foreground">
                {{ item.projects_count ?? 0 }} projects
            </p>
            <div class="flex gap-1 sm:justify-end">
                <Link
                    :href="`/admin/technologies/${item.id}/edit`"
                    :class="buttonVariants({ variant: 'ghost', size: 'sm' })"
                    >Edit</Link
                ><ConfirmDelete
                    title="Delete technology?"
                    :processing="deleting === item.id"
                    description="Project relationships will be detached."
                    @confirm="remove(item)"
                />
            </div>
        </div>
    </div>
</template>
