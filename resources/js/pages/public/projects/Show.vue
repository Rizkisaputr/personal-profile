<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowUpRight } from '@lucide/vue';
import { computed } from 'vue';
import MotionReveal from '@/components/MotionReveal.vue';
import ProjectVisual from '@/components/public/ProjectVisual.vue';
import SeoHead from '@/components/public/SeoHead.vue';
import type {
    PublicProject,
    PublicSettings,
    SeoData,
    SocialLink,
} from '@/types';

const props = defineProps<{
    project: PublicProject;
    nextProject: PublicProject | null;
    settings: PublicSettings;
    socialLinks: SocialLink[];
    seo: SeoData;
}>();

const sectionHeadings = new Set([
    'Project Overview',
    'Problem',
    'My Role',
    'Solution',
    'Technology',
    'Key Features',
    'Result / Impact',
]);
const caseStudyBlocks = computed(() =>
    (props.project.description ?? '')
        .split(/\r?\n/)
        .map((line) => line.trim())
        .filter(Boolean)
        .map((content) => ({
            content: content.replace(/^-\s+/, ''),
            type: sectionHeadings.has(content)
                ? 'heading'
                : content.startsWith('- ')
                  ? 'item'
                  : 'paragraph',
        })),
);
const year = (date: string | null): string =>
    date ? new Date(date).getFullYear().toString() : '—';
</script>

<template>
    <SeoHead :seo="seo" :browser-title="project.title" type="article" />

    <article>
        <header
            class="content-container py-14 sm:py-16 lg:py-20"
        >
            <MotionReveal
                ><Link
                    href="/projects"
                    class="mb-10 inline-flex min-h-10 items-center gap-2 text-sm text-muted-foreground hover:text-foreground"
                    ><ArrowLeft class="size-4" />All Projects</Link
                >
                <p class="editorial-kicker mb-5">
                    {{ project.category || 'Case study' }}
                </p>
                <h1
                    class="max-w-5xl text-[clamp(2.75rem,6vw,6rem)] leading-[0.93] font-semibold tracking-[-0.06em]"
                >
                    {{ project.title }}
                </h1>
                <p
                    v-if="project.short_description"
                    class="mt-7 max-w-3xl text-lg leading-8 text-muted-foreground sm:text-xl"
                >
                    {{ project.short_description }}
                </p></MotionReveal
            >

            <MotionReveal :delay="100"
                ><dl
                    class="mt-12 grid gap-6 border-t border-border pt-6 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <div v-if="project.client_name">
                        <dt class="editorial-kicker">Client</dt>
                        <dd class="mt-2 text-sm">{{ project.client_name }}</dd>
                    </div>
                    <div>
                        <dt class="editorial-kicker">Year</dt>
                        <dd class="mt-2 text-sm">
                            {{ year(project.project_date) }}
                        </dd>
                    </div>
                    <div v-if="project.technologies.length">
                        <dt class="editorial-kicker">Technology</dt>
                        <dd class="mt-2 text-sm leading-6">
                            {{
                                project.technologies
                                    .map((item) => item.name)
                                    .join(' · ')
                            }}
                        </dd>
                    </div>
                    <div v-if="project.demo_url || project.github_url">
                        <dt class="editorial-kicker">Links</dt>
                        <dd class="mt-2 flex flex-wrap gap-4">
                            <a
                                v-if="project.demo_url"
                                :href="project.demo_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 text-sm font-medium text-primary"
                                >Live Project<ArrowUpRight
                                    class="size-3.5" /></a
                            ><a
                                v-if="project.github_url"
                                :href="project.github_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 text-sm font-medium"
                                >GitHub<ArrowUpRight class="size-3.5"
                            /></a>
                        </dd>
                    </div></dl
            ></MotionReveal>
        </header>

        <MotionReveal :delay="120"
            ><div
                class="case-study-media content-container aspect-[16/10] overflow-hidden rounded-2xl border border-border bg-muted sm:aspect-[16/9]"
            >
                <img
                    v-if="project.thumbnail_url"
                    :src="project.thumbnail_url"
                    :alt="`${project.title} main project image`"
                    fetchpriority="high"
                    decoding="async"
                    class="h-full w-full object-cover"
                />
                <ProjectVisual v-else :project="project" eager /></div
        ></MotionReveal>

        <section
            v-if="project.description"
            class="content-container py-14 sm:py-16 lg:py-20"
        >
            <MotionReveal
                ><div class="mx-auto max-w-[46rem]">
                    <p class="editorial-kicker mb-8">Case study</p>
                    <div class="space-y-5">
                        <template
                            v-for="(block, index) in caseStudyBlocks"
                            :key="`${block.type}-${index}`"
                        >
                            <h2
                                v-if="block.type === 'heading'"
                                class="pt-8 text-xl font-semibold tracking-[-0.025em] first:pt-0 sm:text-2xl"
                            >
                                {{ block.content }}
                            </h2>
                            <div
                                v-else-if="block.type === 'item'"
                                class="flex gap-4 text-[1.05rem] leading-8 text-foreground/85 sm:text-lg"
                            >
                                <span
                                    class="mt-[0.85rem] size-1.5 shrink-0 rounded-full bg-primary"
                                />
                                <span>{{ block.content }}</span>
                            </div>
                            <p
                                v-else
                                class="text-[1.05rem] leading-8 text-foreground/85 sm:text-lg sm:leading-9"
                            >
                                {{ block.content }}
                            </p>
                        </template>
                    </div>
                </div></MotionReveal
            >
        </section>

        <section
            v-if="project.images?.length"
            class="border-t border-border py-14 sm:py-16 lg:py-20"
        >
            <div class="content-container">
                <MotionReveal
                    ><div class="mb-12">
                        <p class="editorial-kicker mb-3">Gallery</p>
                        <h2 class="editorial-heading">Inside the work.</h2>
                    </div></MotionReveal
                >
                <div class="grid gap-6 md:grid-cols-2">
                    <MotionReveal
                        v-for="(image, index) in project.images"
                        :key="image.id"
                        :delay="(index % 2) * 70"
                        :class="index % 3 === 0 ? 'md:col-span-2' : ''"
                        ><figure>
                            <div
                                class="overflow-hidden rounded-xl border border-border bg-muted"
                                :class="
                                    index % 3 === 0
                                        ? 'aspect-[16/9]'
                                        : 'aspect-[4/3]'
                                "
                            >
                                <img
                                    :src="image.url"
                                    :alt="
                                        image.caption ||
                                        `${project.title} gallery image ${index + 1}`
                                    "
                                    loading="lazy"
                                    decoding="async"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                            <figcaption
                                v-if="image.caption"
                                class="mt-3 text-xs text-muted-foreground"
                            >
                                {{ image.caption }}
                            </figcaption>
                        </figure></MotionReveal
                    >
                </div>
            </div>
        </section>

        <nav
            class="border-t border-border py-14 sm:py-16 lg:py-20"
            aria-label="Project navigation"
        >
            <div
                class="content-container flex flex-col gap-10 sm:flex-row sm:items-end sm:justify-between"
            >
                <Link
                    href="/projects"
                    class="inline-flex items-center gap-2 text-sm font-medium"
                    ><ArrowLeft class="size-4" />Back to all projects</Link
                ><Link
                    v-if="nextProject"
                    :href="nextProject.url"
                    class="group max-w-xl sm:text-right"
                    ><span class="editorial-kicker">Next project</span
                    ><span
                        class="mt-3 flex items-center gap-3 text-2xl font-semibold tracking-[-0.035em] sm:justify-end sm:text-4xl"
                        >{{ nextProject.title
                        }}<ArrowUpRight
                            class="size-6 transition-transform group-hover:translate-x-1 group-hover:-translate-y-1" /></span
                ></Link>
            </div>
        </nav>
    </article>
</template>
