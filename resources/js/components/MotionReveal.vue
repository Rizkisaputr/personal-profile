<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, useTemplateRef } from 'vue';

withDefaults(defineProps<{ delay?: number }>(), { delay: 0 });

const element = useTemplateRef<HTMLElement>('element');
const visible = ref(false);
let observer: IntersectionObserver | undefined;

onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        visible.value = true;

        return;
    }

    observer = new IntersectionObserver(
        ([entry]) => {
            if (entry.isIntersecting) {
                visible.value = true;
                observer?.disconnect();
            }
        },
        { threshold: 0.12 },
    );

    if (element.value) {
        observer.observe(element.value);
    }
});

onBeforeUnmount(() => observer?.disconnect());
</script>

<template>
    <div
        ref="element"
        class="motion-reveal"
        :data-visible="visible"
        :style="{ transitionDelay: `${delay}ms` }"
    >
        <slot />
    </div>
</template>
