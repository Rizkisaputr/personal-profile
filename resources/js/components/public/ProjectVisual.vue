<script setup lang="ts">
import { computed } from 'vue';
import type { Project } from '@/types';
import ParallaxImage from './ParallaxImage.vue';

const props = withDefaults(
    defineProps<{
        project: Project;
        index?: number;
        parallax?: boolean;
        eager?: boolean;
        compact?: boolean;
    }>(),
    { index: 0, parallax: false, eager: false, compact: false },
);

const variant = computed(() => (props.index + props.project.id) % 4);
const number = computed(() => String(props.index + 1).padStart(2, '0'));
const technology = computed(() => props.project.technologies[0]?.name);
</script>

<template>
    <div class="relative h-full w-full overflow-hidden bg-background-warm">
        <ParallaxImage
            v-if="project.thumbnail_url && parallax"
            :src="project.thumbnail_url"
            :alt="`${project.title} project preview`"
            :eager="eager"
        />
        <img
            v-else-if="project.thumbnail_url"
            :src="project.thumbnail_url"
            :alt="`${project.title} project preview`"
            :loading="eager ? 'eager' : 'lazy'"
            decoding="async"
            class="h-full w-full object-cover transition-transform duration-500 ease-[var(--ease-editorial)] group-hover:scale-[1.035]"
        />
        <div
            v-else
            class="project-fallback relative flex h-full flex-col justify-between overflow-hidden p-5 transition-transform duration-500 ease-[var(--ease-editorial)] group-hover:scale-[1.018] sm:p-8"
            :class="[
                variant === 0 && 'bg-[#e9e9e4]',
                variant === 1 && 'bg-[#f5f5f1]',
                variant === 2 && 'bg-[#edf0f7]',
                variant === 3 && 'bg-[#ecece8]',
            ]"
        >
            <div
                class="absolute inset-0 opacity-45"
                :class="variant === 3 ? 'hero-editorial-grid' : ''"
            />
            <div
                class="absolute bg-primary"
                :class="
                    variant % 2 === 0
                        ? 'top-0 right-[18%] h-full w-px'
                        : 'top-[24%] right-0 h-px w-[58%]'
                "
            />
            <div
                class="absolute rounded-full border border-foreground/10"
                :class="
                    variant === 2
                        ? '-right-[8%] -bottom-[22%] size-[65%]'
                        : 'right-[8%] bottom-[12%] size-[28%]'
                "
            />

            <div class="relative flex items-start justify-between gap-4">
                <span
                    class="text-[0.625rem] font-semibold tracking-[0.14em] text-foreground/60 uppercase sm:text-xs"
                    >{{ project.category || 'Selected work' }}</span
                >
                <span class="text-xs font-medium text-primary">{{
                    number
                }}</span>
            </div>
            <div class="relative max-w-[82%]">
                <p
                    class="font-semibold tracking-[-0.05em] text-balance"
                    :class="
                        compact
                            ? 'text-sm leading-4'
                            : 'text-2xl leading-[1.02] sm:text-4xl lg:text-5xl'
                    "
                >
                    {{ project.title }}
                </p>
                <p
                    v-if="technology && !compact"
                    class="mt-4 text-xs font-medium text-foreground/55"
                >
                    {{ technology }}
                </p>
            </div>
        </div>
    </div>
</template>
