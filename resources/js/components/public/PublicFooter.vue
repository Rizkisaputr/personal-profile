<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { PublicProfile, PublicSettings } from '@/types';

const page = usePage();
const profile = computed(
    () => (page.props.profile ?? null) as PublicProfile | null,
);
const settings = computed(() => (page.props.settings ?? {}) as PublicSettings);
const name = computed(
    () =>
        profile.value?.full_name ||
        settings.value.website_title ||
        'Personal Profile',
);
</script>

<template>
    <footer class="border-t border-border bg-background-warm py-8 sm:py-10">
        <div
            class="content-container flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <p class="text-sm font-medium">{{ name }}</p>
                <p class="mt-1 text-xs text-muted-foreground">
                    {{ settings.footer_text || 'Personal portfolio' }}
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    © {{ new Date().getFullYear() }} {{ name }}
                </p>
            </div>
        </div>
    </footer>
</template>
