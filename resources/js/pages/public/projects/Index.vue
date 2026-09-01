<script setup lang="ts">
import { computed, ref } from 'vue';
import MotionReveal from '@/components/MotionReveal.vue';
import ProjectCard from '@/components/public/ProjectCard.vue';
import SeoHead from '@/components/public/SeoHead.vue';
import type {
    PublicProject,
    PublicSettings,
    SeoData,
    SocialLink,
} from '@/types';

const props = defineProps<{
    projects: PublicProject[];
    categories: string[];
    settings: PublicSettings;
    socialLinks: SocialLink[];
    seo: SeoData;
}>();
const category = ref('');
const visibleProjects = computed(() =>
    category.value
        ? props.projects.filter(
              (project) => project.category === category.value,
          )
        : props.projects,
);
</script>

<template>
    <SeoHead :seo="seo" browser-title="Projects" />
    <header class="content-container py-10 sm:py-12 lg:py-14">
        <MotionReveal
            ><p class="editorial-kicker mb-3">Portfolio</p>
            <h1
                class="text-3xl leading-tight font-semibold tracking-[-0.045em] sm:text-4xl"
            >
                Projects
            </h1>
            <p
                class="mt-3 max-w-2xl text-sm leading-6 text-muted-foreground sm:text-base"
            >
                A collection of projects and systems I have built.
            </p></MotionReveal
        >
    </header>

    <section
        class="border-t border-border bg-background-warm py-10 sm:py-12 lg:py-14"
    >
        <div class="content-container">
            <div
                v-if="categories.length > 1"
                class="mb-6 flex flex-wrap gap-2"
                aria-label="Filter projects by category"
            >
                <button
                    type="button"
                    class="min-h-10 rounded-lg px-3 text-sm transition-colors"
                    :class="
                        !category
                            ? 'bg-foreground text-background'
                            : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                    "
                    @click="category = ''"
                >
                    All</button
                ><button
                    v-for="item in categories"
                    :key="item"
                    type="button"
                    class="min-h-10 rounded-lg px-3 text-sm transition-colors"
                    :class="
                        category === item
                            ? 'bg-foreground text-background'
                            : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                    "
                    @click="category = item"
                >
                    {{ item }}
                </button>
            </div>

            <div
                v-if="visibleProjects.length"
                class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"
            >
                <MotionReveal
                    v-for="(project, index) in visibleProjects"
                    :key="project.id"
                    :delay="(index % 3) * 55"
                    ><ProjectCard :project="project" :index="index"
                /></MotionReveal>
            </div>
            <div v-else class="border-y border-border py-16">
                <p class="text-lg font-medium">No projects yet.</p>
                <p class="mt-2 text-sm text-muted-foreground">
                    Projects will appear here once they are published.
                </p>
            </div>
        </div>
    </section>
</template>
