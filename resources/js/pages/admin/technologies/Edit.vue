<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import type { Technology } from '@/types';
import TechnologyForm from './TechnologyForm.vue';
defineProps<{ technology: Technology }>();
</script>
<template>
    <Head title="Edit Technology" /><PageHeader
        eyebrow="Technologies"
        title="Edit technology"
        description="Keep the name, slug, and icon identifier consistent."
    /><TechnologyForm :technology="technology" />
</template>
