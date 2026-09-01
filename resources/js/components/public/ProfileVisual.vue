<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    name: string;
    imageUrl?: string | null;
    professionalTitle?: string | null;
}>();
const initials = computed(() =>
    props.name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase(),
);
</script>

<template>
    <div
        class="relative aspect-[4/5] overflow-hidden rounded-2xl border border-border bg-background-warm"
    >
        <img
            v-if="imageUrl"
            :src="imageUrl"
            :alt="[name, professionalTitle].filter(Boolean).join(' — ')"
            fetchpriority="high"
            decoding="async"
            class="h-full w-full object-cover"
        />
        <div
            v-else
            class="hero-editorial-grid relative flex h-full flex-col justify-between p-7 sm:p-9"
        >
            <div class="flex items-center justify-between">
                <span class="editorial-kicker">Profile</span>
                <span class="size-2 rounded-full bg-primary" />
            </div>
            <div class="relative">
                <div class="absolute -top-20 right-0 h-px w-2/3 bg-primary" />
                <p
                    class="text-[clamp(5rem,16vw,9rem)] leading-none font-medium tracking-[-0.09em] text-foreground/90"
                >
                    {{ initials || 'PP' }}
                </p>
                <p
                    class="mt-5 max-w-[13rem] text-sm leading-6 text-muted-foreground"
                >
                    {{ name
                    }}<template v-if="professionalTitle"
                        ><br />{{ professionalTitle }}</template
                    >
                </p>
            </div>
        </div>
    </div>
</template>
