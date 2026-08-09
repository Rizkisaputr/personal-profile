<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ExternalLink, Plus } from '@lucide/vue';
import { ref } from 'vue';
import ConfirmDelete from '@/components/admin/ConfirmDelete.vue';
import EmptyState from '@/components/admin/EmptyState.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import { buttonVariants } from '@/components/ui/button';
import type { SocialLink } from '@/types';
defineProps<{ socialLinks: SocialLink[] }>();
const deleting = ref<number | null>(null);
function remove(item: SocialLink): void {
    deleting.value = item.id;
    router.delete(`/admin/social-links/${item.id}`, {
        onFinish: () => (deleting.value = null),
    });
}
</script>
<template>
    <Head title="Social Links" /><PageHeader
        eyebrow="Contact"
        title="Social links"
        description="Manage professional channels and direct contact destinations."
        ><template #actions
            ><Link href="/admin/social-links/create" :class="buttonVariants()"
                ><Plus class="size-4" />Add social link</Link
            ></template
        ></PageHeader
    ><EmptyState
        v-if="socialLinks.length === 0"
        class="mt-8"
        title="No social links yet"
        description="Add a channel where visitors can learn more or get in touch."
        ><Link href="/admin/social-links/create" :class="buttonVariants()"
            >Add social link</Link
        ></EmptyState
    >
    <div v-else class="surface-panel mt-8 overflow-hidden">
        <div
            v-for="item in socialLinks"
            :key="item.id"
            class="grid gap-3 border-b border-border px-5 py-5 last:border-0 lg:grid-cols-[1fr_1fr_1.5fr_6rem_8rem] lg:items-center lg:px-6"
        >
            <div class="flex items-center gap-2">
                <p class="text-sm font-medium">{{ item.platform }}</p>
                <StatusBadge :active="item.is_active" />
            </div>
            <p class="text-sm text-muted-foreground">{{ item.label || '—' }}</p>
            <a
                :href="item.url"
                target="_blank"
                rel="noopener noreferrer"
                class="flex min-w-0 items-center gap-2 text-sm text-primary"
                ><span class="truncate">{{ item.url }}</span
                ><ExternalLink class="size-3.5 shrink-0"
            /></a>
            <p class="text-sm text-muted-foreground">{{ item.sort_order }}</p>
            <div class="flex gap-1 lg:justify-end">
                <Link
                    :href="`/admin/social-links/${item.id}/edit`"
                    :class="buttonVariants({ variant: 'ghost', size: 'sm' })"
                    >Edit</Link
                ><ConfirmDelete
                    title="Delete social link?"
                    :processing="deleting === item.id"
                    @confirm="remove(item)"
                />
            </div>
        </div>
    </div>
</template>
