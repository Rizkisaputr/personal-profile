<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import ConfirmDelete from '@/components/admin/ConfirmDelete.vue';
import EmptyState from '@/components/admin/EmptyState.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import { buttonVariants } from '@/components/ui/button';
import type { Education } from '@/types';

defineProps<{ educations: Education[] }>();
const deleting = ref<number | null>(null);
function remove(item: Education): void {
    deleting.value = item.id;
    router.delete(`/admin/educations/${item.id}`, {
        onFinish: () => (deleting.value = null),
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Education" />
    <PageHeader
        eyebrow="Content"
        title="Education"
        description="Maintain the academic background that supports your professional profile."
    >
        <template #actions
            ><Link href="/admin/educations/create" :class="buttonVariants()"
                ><Plus class="size-4" />Add education</Link
            ></template
        >
    </PageHeader>
    <EmptyState
        v-if="educations.length === 0"
        class="mt-8"
        title="No education yet"
        description="Add your first academic entry."
        ><Link href="/admin/educations/create" :class="buttonVariants()"
            >Add education</Link
        ></EmptyState
    >
    <div v-else class="surface-panel mt-8 overflow-hidden">
        <div
            v-for="item in educations"
            :key="item.id"
            class="grid gap-4 border-b border-border px-5 py-5 last:border-b-0 lg:grid-cols-[1.4fr_1fr_10rem_8rem] lg:items-center lg:px-6"
        >
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <p class="text-sm font-medium">{{ item.institution }}</p>
                    <StatusBadge :active="item.is_active" />
                </div>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{
                        [item.degree, item.field_of_study]
                            .filter(Boolean)
                            .join(' · ') || 'No degree details'
                    }}
                </p>
            </div>
            <p class="text-sm text-muted-foreground">
                {{ item.location || '—' }}
            </p>
            <p class="text-sm text-muted-foreground">
                {{ item.start_year || '—' }} — {{ item.end_year || 'Present' }}
            </p>
            <div class="flex gap-1 lg:justify-end">
                <Link
                    :href="`/admin/educations/${item.id}/edit`"
                    :class="buttonVariants({ variant: 'ghost', size: 'sm' })"
                    >Edit</Link
                ><ConfirmDelete
                    title="Delete education?"
                    :processing="deleting === item.id"
                    @confirm="remove(item)"
                />
            </div>
        </div>
    </div>
</template>
