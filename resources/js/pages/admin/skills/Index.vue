<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import ConfirmDelete from '@/components/admin/ConfirmDelete.vue';
import EmptyState from '@/components/admin/EmptyState.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import { buttonVariants } from '@/components/ui/button';
import type { Skill } from '@/types';

defineProps<{ skills: Skill[] }>();
const deleting = ref<number | null>(null);

function remove(skill: Skill): void {
    deleting.value = skill.id;
    router.delete(`/admin/skills/${skill.id}`, {
        onFinish: () => (deleting.value = null),
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Skills" />
    <PageHeader
        eyebrow="Content"
        title="Skills"
        description="Organize capabilities into concise, natural groups."
    >
        <template #actions>
            <Link href="/admin/skills/create" :class="buttonVariants()"
                ><Plus class="size-4" />Add skill</Link
            >
        </template>
    </PageHeader>

    <EmptyState
        v-if="skills.length === 0"
        class="mt-8"
        title="No skills yet"
        description="Add your first skill to begin shaping the capabilities section."
    >
        <Link href="/admin/skills/create" :class="buttonVariants()"
            >Add skill</Link
        >
    </EmptyState>

    <div v-else class="surface-panel mt-8 overflow-hidden">
        <div
            class="hidden grid-cols-[1.3fr_1fr_7rem_6rem_8rem] gap-4 border-b border-border bg-muted/25 px-6 py-3 text-xs font-semibold tracking-[0.06em] text-muted-foreground uppercase md:grid"
        >
            <span>Name</span><span>Category</span><span>Proficiency</span
            ><span>Order</span><span class="text-right">Actions</span>
        </div>
        <div
            v-for="skill in skills"
            :key="skill.id"
            class="grid gap-4 border-b border-border px-5 py-5 last:border-b-0 md:grid-cols-[1.3fr_1fr_7rem_6rem_8rem] md:items-center md:px-6"
        >
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <p class="text-sm font-medium">{{ skill.name }}</p>
                    <StatusBadge :active="skill.is_active" />
                </div>
                <p class="mt-1 text-xs text-muted-foreground md:hidden">
                    {{ skill.category || 'Uncategorized' }}
                </p>
            </div>
            <span class="hidden text-sm text-muted-foreground md:block">{{
                skill.category || '—'
            }}</span>
            <span class="text-sm text-muted-foreground"
                ><span class="md:hidden">Proficiency: </span
                >{{
                    skill.proficiency === null ? '—' : `${skill.proficiency}%`
                }}</span
            >
            <span class="text-sm text-muted-foreground"
                ><span class="md:hidden">Order: </span
                >{{ skill.sort_order }}</span
            >
            <div class="flex items-center gap-1 md:justify-end">
                <Link
                    :href="`/admin/skills/${skill.id}/edit`"
                    :class="buttonVariants({ variant: 'ghost', size: 'sm' })"
                    >Edit</Link
                >
                <ConfirmDelete
                    title="Delete skill?"
                    :processing="deleting === skill.id"
                    @confirm="remove(skill)"
                />
            </div>
        </div>
    </div>
</template>
