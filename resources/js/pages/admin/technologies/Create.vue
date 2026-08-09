<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import TechnologyForm from './TechnologyForm.vue';
</script>
<template>
    <Head title="Add Technology" /><PageHeader
        eyebrow="Technologies"
        title="Add technology"
        description="Add a reusable technology identifier for project case studies."
    /><TechnologyForm />
</template>
