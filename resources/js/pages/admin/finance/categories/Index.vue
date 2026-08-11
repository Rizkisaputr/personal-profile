<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import ConfirmDelete from '@/components/admin/ConfirmDelete.vue';
import EmptyState from '@/components/admin/EmptyState.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import { buttonVariants } from '@/components/ui/button';
import type { FinanceCategory } from '@/types';

type Pagination = {
    data: FinanceCategory[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
};

defineProps<{ categories: Pagination }>();
const deleting = ref<number | null>(null);

function remove(category: FinanceCategory): void {
    deleting.value = category.id;
    router.delete(`/admin/finance/categories/${category.id}`, {
        preserveScroll: true,
        onFinish: () => (deleting.value = null),
    });
}
</script>

<template>
    <Head title="Finance categories" />
    <PageHeader
        eyebrow="Personal · Finance"
        title="Categories"
        description="Manage income and expense categories used by finance transactions."
    >
        <template #actions>
            <Link
                href="/admin/finance/categories/create"
                :class="buttonVariants()"
            >
                <Plus class="size-4" />Add category
            </Link>
        </template>
    </PageHeader>

    <EmptyState
        v-if="!categories.data.length"
        class="mt-8"
        title="No finance categories"
        description="Add a category before recording income or expense transactions."
    >
        <Link href="/admin/finance/categories/create" :class="buttonVariants()">
            Add category
        </Link>
    </EmptyState>

    <div v-else class="surface-panel mt-8 overflow-x-auto">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead
                class="border-b border-border bg-muted/40 text-xs text-muted-foreground"
            >
                <tr>
                    <th class="px-5 py-3 font-medium">Name</th>
                    <th class="px-5 py-3 font-medium">Type</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Created</th>
                    <th class="px-5 py-3 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="category in categories.data"
                    :key="category.id"
                    class="border-b border-border last:border-0"
                >
                    <td class="px-5 py-4 font-medium" data-no-translate>
                        {{ category.name }}
                    </td>
                    <td class="px-5 py-4 capitalize">{{ category.type }}</td>
                    <td class="px-5 py-4">
                        <StatusBadge :active="category.is_active" />
                    </td>
                    <td class="px-5 py-4 text-muted-foreground">
                        {{ new Date(category.created_at).toLocaleDateString() }}
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex justify-end gap-1">
                            <Link
                                :href="`/admin/finance/categories/${category.id}/edit`"
                                :class="
                                    buttonVariants({
                                        variant: 'ghost',
                                        size: 'sm',
                                    })
                                "
                            >
                                Edit
                            </Link>
                            <ConfirmDelete
                                title="Delete category?"
                                description="Categories used by finance records will be deactivated to preserve history."
                                :processing="deleting === category.id"
                                @confirm="remove(category)"
                            />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div v-if="categories.last_page > 1" class="mt-5 flex justify-end gap-2">
        <Link
            v-if="categories.prev_page_url"
            :href="categories.prev_page_url"
            :class="buttonVariants({ variant: 'outline', size: 'sm' })"
            >Previous</Link
        >
        <Link
            v-if="categories.next_page_url"
            :href="categories.next_page_url"
            :class="buttonVariants({ variant: 'outline', size: 'sm' })"
            >Next</Link
        >
    </div>
</template>
