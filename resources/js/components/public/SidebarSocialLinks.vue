<script setup lang="ts">
import { computed } from 'vue';
import type { SocialLink } from '@/types';

const props = defineProps<{ links: SocialLink[] }>();
type Platform = 'github' | 'instagram' | 'tiktok' | 'whatsapp' | 'facebook';
const order: Platform[] = ['github', 'instagram', 'tiktok', 'whatsapp', 'facebook'];
const labels: Record<Platform, string> = { github: 'GitHub', instagram: 'Instagram', tiktok: 'TikTok', whatsapp: 'WhatsApp', facebook: 'Facebook' };
function normalize(value: string): Platform | null {
    const platform = value.toLowerCase().replace(/[\s_-]+/g, '');

    if (platform === 'fb') {
return 'facebook';
}

    return order.includes(platform as Platform) ? platform as Platform : null;
}
const visibleLinks = computed(() => order.flatMap((platform) => {
    const link = props.links.find((item) => item.is_active && item.url?.trim() && normalize(item.platform) === platform);

    return link ? [{ ...link, normalizedPlatform: platform }] : [];
}));
</script>

<template>
    <div v-if="visibleLinks.length" class="flex items-center gap-1.5" aria-label="Social links">
        <a v-for="link in visibleLinks" :key="link.id" :href="link.url" target="_blank" rel="noopener noreferrer" class="grid size-9 place-items-center text-[var(--public-sidebar-social)] transition-colors duration-200 hover:text-[var(--public-sidebar-active)]" :aria-label="labels[link.normalizedPlatform]">
            <svg v-if="link.normalizedPlatform === 'github'" class="size-[18px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 .7a11.5 11.5 0 0 0-3.64 22.41c.58.11.79-.25.79-.56v-2.23c-3.24.7-3.92-1.37-3.92-1.37-.53-1.35-1.29-1.71-1.29-1.71-1.06-.72.08-.71.08-.71 1.17.08 1.79 1.2 1.79 1.2 1.04 1.79 2.73 1.27 3.4.97.1-.76.4-1.27.74-1.56-2.58-.29-5.3-1.29-5.3-5.69 0-1.26.45-2.28 1.19-3.09-.12-.29-.52-1.47.11-3.05 0 0 .97-.31 3.16 1.18a10.9 10.9 0 0 1 5.76 0c2.2-1.49 3.16-1.18 3.16-1.18.63 1.58.23 2.76.11 3.05.74.81 1.19 1.83 1.19 3.09 0 4.41-2.72 5.39-5.31 5.68.42.36.79 1.07.79 2.16v3.21c0 .31.21.68.8.56A11.5 11.5 0 0 0 12 .7Z"/></svg>
            <svg v-else-if="link.normalizedPlatform === 'instagram'" class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
            <svg v-else-if="link.normalizedPlatform === 'tiktok'" class="size-[18px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M15.6 3c.2 1.7 1.2 3.2 2.7 4.1a6.6 6.6 0 0 0 2.7.8v3.3a9.6 9.6 0 0 1-5.3-1.6v6.2a6.2 6.2 0 1 1-5.4-6.1v3.4a2.9 2.9 0 1 0 2.1 2.7V3h3.2Z"/></svg>
            <svg v-else-if="link.normalizedPlatform === 'whatsapp'" class="size-[18px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 0a12 12 0 0 0-10.3 18.1L.1 24l6-1.6A12 12 0 1 0 12 0Zm0 21.8a9.8 9.8 0 0 1-5-1.4l-.4-.2-3.6 1 1-3.5-.2-.4A9.8 9.8 0 1 1 12 21.8Zm5.4-7.4c-.3-.1-1.7-.8-2-.9-.3-.1-.5-.1-.7.2l-1 1.2c-.2.2-.4.2-.7.1a8 8 0 0 1-2.4-1.5A9 9 0 0 1 9 11.4c-.2-.3 0-.5.1-.6l.8-1.1c.1-.2 0-.4 0-.6L9 7c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.3-1.2 1.2-1.2 2.9s1.3 3.4 1.5 3.6c.2.2 2.5 3.8 6 5.3.8.4 1.5.6 2 .7.8.3 1.6.2 2.2.1.7-.1 1.7-.7 1.9-1.3.2-.6.2-1.2.2-1.3-.1-.1-.3-.2-.6-.4Z"/></svg>
            <svg v-else class="size-[18px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.7 24V13.1h3.7l.5-4.3h-4.2V6.1c0-1.3.4-2.1 2.2-2.1h2.3V.2A31 31 0 0 0 14.8 0c-3.4 0-5.7 2.1-5.7 5.8v3H5.3v4.3h3.8V24h4.6Z"/></svg>
        </a>
    </div>
</template>
