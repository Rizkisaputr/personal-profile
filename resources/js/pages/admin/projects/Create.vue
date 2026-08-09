<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import type { Technology } from '@/types';
import ProjectForm from './ProjectForm.vue';
defineProps<{ technologies: Technology[] }>();
</script>
<template>
    <Head title="Add Project" /><PageHeader
        eyebrow="Projects"
        title="Add project"
        description="Build a clear case study around the problem, decisions, and result."
    /><ProjectForm :technologies="technologies" />
</template>
