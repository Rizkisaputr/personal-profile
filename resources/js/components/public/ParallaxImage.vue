<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, useTemplateRef } from 'vue';

withDefaults(
    defineProps<{
        src: string;
        alt: string;
        eager?: boolean;
        imageClass?: string;
    }>(),
    { eager: false, imageClass: '' },
);

const container = useTemplateRef<HTMLElement>('container');
const offset = ref(0);
let frame = 0;
let active = false;

function update(): void {
    frame = 0;

    if (!active || !container.value) {
        return;
    }

    const rect = container.value.getBoundingClientRect();
    const center = rect.top + rect.height / 2 - window.innerHeight / 2;
    offset.value = Math.max(-10, Math.min(10, center * -0.02));
}

function onScroll(): void {
    if (!frame) {
        frame = requestAnimationFrame(update);
    }
}

let observer: IntersectionObserver | undefined;
onMounted(() => {
    if (
        window.matchMedia('(prefers-reduced-motion: reduce)').matches ||
        !window.matchMedia('(pointer: fine)').matches
    ) {
        return;
    }

    observer = new IntersectionObserver(([entry]) => {
        active = entry.isIntersecting;

        if (active) {
            update();
        }
    });

    if (container.value) {
        observer.observe(container.value);
    }

    window.addEventListener('scroll', onScroll, { passive: true });
});

onBeforeUnmount(() => {
    observer?.disconnect();
    window.removeEventListener('scroll', onScroll);

    if (frame) {
        cancelAnimationFrame(frame);
    }
});
</script>

<template>
    <div ref="container" class="h-full w-full overflow-hidden">
        <div
            class="h-[calc(100%+28px)] w-full transition-transform duration-500 ease-[var(--ease-editorial)] group-hover:scale-[1.035]"
            :style="{ transform: `translate3d(0, ${offset - 14}px, 0)` }"
        >
            <img
                :src="src"
                :alt="alt"
                :loading="eager ? 'eager' : 'lazy'"
                :fetchpriority="eager ? 'high' : 'auto'"
                decoding="async"
                class="h-full w-full object-cover"
                :class="imageClass"
            />
        </div>
    </div>
</template>
