<script setup lang="ts">
import {
    Camera,
    Code2,
    ExternalLink,
    Mail,
    MessageCircle,
    Send,
    Users,
    Video,
} from '@lucide/vue';
import type { Component } from 'vue';
import type { SocialLink } from '@/types';

defineProps<{ links: SocialLink[]; compact?: boolean }>();

const icons: Record<string, Component> = {
    github: Code2,
    linkedin: Users,
    instagram: Camera,
    whatsapp: MessageCircle,
    email: Mail,
    x: Send,
    twitter: Send,
    youtube: Video,
};

function iconFor(link: SocialLink): Component {
    return icons[(link.icon || link.platform).toLowerCase()] ?? ExternalLink;
}
</script>

<template>
    <div
        class="flex flex-wrap items-center"
        :class="compact ? 'gap-1' : 'gap-x-5 gap-y-3'"
    >
        <a
            v-for="link in links"
            :key="link.id"
            :href="link.url"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex min-h-10 items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
            :class="
                compact
                    ? 'min-w-10 justify-center rounded-lg hover:bg-muted'
                    : ''
            "
            :aria-label="compact ? link.label || link.platform : undefined"
        >
            <component :is="iconFor(link)" class="size-4" aria-hidden="true" />
            <span :class="compact ? 'sr-only' : ''">{{
                link.label || link.platform
            }}</span>
        </a>
    </div>
</template>
