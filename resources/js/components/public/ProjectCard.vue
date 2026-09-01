<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight } from '@lucide/vue';
import type { PublicProject } from '@/types';
import ProjectVisual from './ProjectVisual.vue';

defineProps<{ project: PublicProject; index?: number }>();
const year = (date: string | null): string =>
    date ? new Date(date).getFullYear().toString() : '';
const remainingTechnologies = (project: PublicProject): number =>
    Math.max(0, project.technologies.length - 3);
</script>

<template>
    <article class="group h-full">
        <Link
            :href="project.url"
            class="flex h-full flex-col overflow-hidden rounded-xl border border-border bg-white transition-colors duration-200 hover:border-primary/45 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
        >
            <div
                class="aspect-[16/10] overflow-hidden border-b border-border bg-muted [&_img]:transition-transform [&_img]:duration-300 group-hover:[&_img]:scale-[1.02]"
            >
                <ProjectVisual :project="project" :index="index" compact />
            </div>
            <div class="flex flex-1 flex-col p-4 sm:p-[1.125rem]">
                <h3
                    class="text-[1.075rem] leading-6 font-semibold tracking-[-0.02em] transition-colors group-hover:text-primary sm:text-lg"
                >
                    {{ project.title }}
                </h3>
                <p class="mt-1 text-xs text-muted-foreground">
                    {{ project.category
                    }}<template
                        v-if="project.category && year(project.project_date)"
                    >
                        · </template
                    >{{ year(project.project_date) }}
                </p>
                <div class="mt-3 min-h-11">
                    <p
                        v-if="project.short_description"
                        class="line-clamp-2 text-sm leading-[1.375rem] text-muted-foreground"
                    >
                        {{ project.short_description }}
                    </p>
                </div>
                <div
                    v-if="project.technologies.length"
                    class="mt-3 flex min-h-6 flex-wrap items-center gap-x-2.5 gap-y-1"
                >
                    <span
                        v-for="technology in project.technologies.slice(0, 3)"
                        :key="technology.id"
                        class="text-[0.7rem] font-medium text-muted-foreground"
                    >
                        {{ technology.name }}
                    </span>
                    <span
                        v-if="remainingTechnologies(project)"
                        class="text-[0.7rem] font-semibold text-foreground/65"
                    >
                        +{{ remainingTechnologies(project) }}
                    </span>
                </div>
                <span
                    class="mt-auto inline-flex min-h-9 items-center gap-1.5 self-start pt-4 text-xs font-semibold text-foreground/75 transition-colors group-hover:text-primary"
                >
                    View Project <ArrowUpRight class="size-3.5" />
                </span>
            </div>
        </Link>
    </article>
</template>
