<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { PublicSettings, SeoData } from '@/types';

withDefaults(
    defineProps<{
        seo: SeoData;
        browserTitle?: string;
        type?: 'website' | 'article';
    }>(),
    { browserTitle: undefined, type: 'website' },
);

const page = usePage();
const favicon = computed(
    () => ((page.props.settings ?? {}) as PublicSettings).favicon_url,
);
</script>

<template>
    <Head :title="browserTitle ?? seo.title">
        <link v-if="favicon" head-key="favicon" rel="icon" :href="favicon" />
        <meta
            v-if="seo.description"
            head-key="description"
            name="description"
            :content="seo.description"
        />
        <link head-key="canonical" rel="canonical" :href="seo.canonical" />
        <meta head-key="og:title" property="og:title" :content="seo.title" />
        <meta
            v-if="seo.description"
            head-key="og:description"
            property="og:description"
            :content="seo.description"
        />
        <meta head-key="og:type" property="og:type" :content="type" />
        <meta head-key="og:url" property="og:url" :content="seo.canonical" />
        <meta
            v-if="seo.image"
            head-key="og:image"
            property="og:image"
            :content="seo.image"
        />
    </Head>
</template>
