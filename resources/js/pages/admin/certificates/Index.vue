<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import ConfirmDelete from '@/components/admin/ConfirmDelete.vue';
import EmptyState from '@/components/admin/EmptyState.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import { buttonVariants } from '@/components/ui/button';
import type { Certificate } from '@/types';
defineProps<{ certificates: Certificate[] }>();
const deleting = ref<number | null>(null);
const date = (value: string | null): string =>
    value
        ? new Intl.DateTimeFormat('en', {
              month: 'short',
              year: 'numeric',
          }).format(new Date(value))
        : '—';
function remove(item: Certificate): void {
    deleting.value = item.id;
    router.delete(`/admin/certificates/${item.id}`, {
        onFinish: () => (deleting.value = null),
    });
}
</script>
<template>
    <Head title="Certificates" /><PageHeader
        eyebrow="Content"
        title="Certificates"
        description="Manage credentials that add meaningful context to your profile."
        ><template #actions
            ><Link href="/admin/certificates/create" :class="buttonVariants()"
                ><Plus class="size-4" />Add certificate</Link
            ></template
        ></PageHeader
    ><EmptyState
        v-if="certificates.length === 0"
        class="mt-8"
        title="No certificates yet"
        description="Add a relevant certificate when it supports your professional profile."
        ><Link href="/admin/certificates/create" :class="buttonVariants()"
            >Add certificate</Link
        ></EmptyState
    >
    <div v-else class="surface-panel mt-8 overflow-hidden">
        <div
            v-for="item in certificates"
            :key="item.id"
            class="grid gap-3 border-b border-border px-5 py-5 last:border-0 md:grid-cols-[1.4fr_1fr_8rem_7rem] md:items-center md:px-6"
        >
            <div>
                <div class="flex items-center gap-2">
                    <p class="text-sm font-medium">{{ item.title }}</p>
                    <StatusBadge :active="item.is_active" />
                </div>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ item.issuer || 'Unknown issuer' }}
                </p>
            </div>
            <p class="text-sm text-muted-foreground">
                {{ date(item.issue_date) }}
            </p>
            <p class="text-sm text-muted-foreground">
                Order {{ item.sort_order }}
            </p>
            <div class="flex gap-1 md:justify-end">
                <Link
                    :href="`/admin/certificates/${item.id}/edit`"
                    :class="buttonVariants({ variant: 'ghost', size: 'sm' })"
                    >Edit</Link
                ><ConfirmDelete
                    title="Delete certificate?"
                    :processing="deleting === item.id"
                    @confirm="remove(item)"
                />
            </div>
        </div>
    </div>
</template>
