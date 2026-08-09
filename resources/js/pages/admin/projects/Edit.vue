<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import type { Project, Technology } from '@/types';
import ProjectForm from './ProjectForm.vue';
defineProps<{ project: Project; technologies: Technology[] }>();
</script>
<template>
    <Head title="Edit Project" /><PageHeader
        eyebrow="Projects"
        title="Edit project"
        description="Refine the case study, media, technology, and publishing details."
    /><ProjectForm :project="project" :technologies="technologies" />
</template>
