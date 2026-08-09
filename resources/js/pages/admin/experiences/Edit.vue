<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import type { Experience } from '@/types';
import ExperienceForm from './ExperienceForm.vue';
defineProps<{ experience: Experience }>();
</script>
<template>
    <Head title="Edit Experience" /><PageHeader
        eyebrow="Experience"
        title="Edit experience"
        description="Keep this professional chapter concise, accurate, and current."
    /><ExperienceForm :experience="experience" />
</template>
