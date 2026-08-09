<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Search } from '@lucide/vue';
import { reactive, ref } from 'vue';
import ConfirmDelete from '@/components/admin/ConfirmDelete.vue';
import EmptyState from '@/components/admin/EmptyState.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import ProjectVisual from '@/components/public/ProjectVisual.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { Project } from '@/types';

const props = defineProps<{
    projects: Project[];
    filters: { search?: string; published?: string; featured?: string };
}>();
const filters = reactive({
    search: props.filters.search ?? '',
    published: props.filters.published ?? '',
    featured: props.filters.featured ?? '',
});
const deleting = ref<number | null>(null);

function applyFilters(): void {
    router.get('/admin/projects', filters, {
        preserveState: true,
        replace: true,
    });
}

function clearFilters(): void {
    filters.search = '';
    filters.published = '';
    filters.featured = '';
    applyFilters();
}

function remove(project: Project): void {
    deleting.value = project.id;
    router.delete(`/admin/projects/${project.id}`, {
        onFinish: () => (deleting.value = null),
    });
}

const date = (value: string | null): string =>
    value
        ? new Intl.DateTimeFormat('en', {
              month: 'short',
              year: 'numeric',
          }).format(new Date(value))
        : '—';
const workStatusLabel: Record<Project['work_status'], string> = {
    waiting: 'Waiting',
    running: 'Running',
    on_hold: 'On Hold',
    done: 'Done',
};

const workStatusClass: Record<Project['work_status'], string> = {
    waiting: 'bg-amber-50 text-amber-700 ring-amber-200',
    running: 'bg-blue-50 text-blue-700 ring-blue-200',
    on_hold: 'bg-slate-100 text-slate-600 ring-slate-200',
    done: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
};
</script>

<template>
    <Head title="Projects" />
    <PageHeader
        eyebrow="Portfolio"
        title="Projects"
        description="Manage selected work and professional case studies."
    >
        <template #actions
            ><Link href="/admin/projects/create" :class="buttonVariants()"
                ><Plus class="size-4" />Add project</Link
            ></template
        >
    </PageHeader>

    <form
        class="mt-8 grid gap-3 rounded-xl border border-border bg-card p-4 md:grid-cols-[1fr_11rem_10rem_auto]"
        @submit.prevent="applyFilters"
    >
        <div class="relative">
            <Search
                class="absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-muted-foreground"
            /><Input
                v-model="filters.search"
                class="pl-10"
                placeholder="Search title or category"
                aria-label="Search projects"
            />
        </div>
        <select
            v-model="filters.published"
            class="h-11 rounded-[10px] border border-input bg-card px-3 text-sm"
            aria-label="Published filter"
        >
            <option value="">All publishing</option>
            <option value="published">Published</option>
            <option value="draft">Draft</option>
        </select>
        <select
            v-model="filters.featured"
            class="h-11 rounded-[10px] border border-input bg-card px-3 text-sm"
            aria-label="Featured filter"
        >
            <option value="">All projects</option>
            <option value="featured">Featured</option>
        </select>
        <div class="flex gap-2">
            <Button type="submit">Filter</Button
            ><Button type="button" variant="ghost" @click="clearFilters"
                >Clear</Button
            >
        </div>
    </form>

    <EmptyState
        v-if="projects.length === 0"
        class="mt-8"
        title="No projects yet"
        description="Create your first project to start building your portfolio."
        ><Link href="/admin/projects/create" :class="buttonVariants()"
            >Add project</Link
        ></EmptyState
    >

    <div v-else class="surface-panel mt-8 overflow-hidden">
        <article
            v-for="project in projects"
            :key="project.id"
            class="grid gap-5 border-b border-border p-5 last:border-b-0 lg:grid-cols-[8rem_1.4fr_1fr_8rem_8rem] lg:items-center lg:p-6"
        >
            <div
                class="aspect-[16/10] overflow-hidden rounded-lg border border-border bg-muted"
            >
                <ProjectVisual :project="project" compact />
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-sm font-semibold">{{ project.title }}</h2>
                    <StatusBadge
                        :active="project.is_published"
                        active-label="Published"
                        inactive-label="Draft"
                    /><StatusBadge
                        v-if="project.is_featured"
                        :active="true"
                        active-label="Featured"
                    />
                    <span
                        class="rounded-full px-2 py-0.5 text-[0.68rem] font-medium ring-1 ring-inset"
                        :class="workStatusClass[project.work_status]"
                        >{{ workStatusLabel[project.work_status] }}</span
                    >
                </div>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ project.category || 'Uncategorized' }} ·
                    {{ date(project.project_date) }}
                </p>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    <span
                        v-for="technology in project.technologies.slice(0, 4)"
                        :key="technology.id"
                        class="rounded-md bg-muted px-2 py-0.5 text-xs text-muted-foreground"
                        >{{ technology.name }}</span
                    >
                </div>
            </div>
            <p class="line-clamp-2 text-sm leading-6 text-muted-foreground">
                {{ project.short_description || 'No short description.' }}
            </p>
            <p class="text-sm text-muted-foreground">
                <span class="lg:hidden">Order: </span>{{ project.sort_order }}
            </p>
            <div class="flex gap-1 lg:justify-end">
                <Link
                    :href="`/admin/projects/${project.id}/edit`"
                    :class="buttonVariants({ variant: 'ghost', size: 'sm' })"
                    >Edit</Link
                ><ConfirmDelete
                    title="Delete project?"
                    :processing="deleting === project.id"
                    description="The thumbnail and all gallery images will also be deleted."
                    @confirm="remove(project)"
                />
            </div>
        </article>
    </div>
</template>
