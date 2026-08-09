<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import TrainingForm from './TrainingForm.vue';
</script>

<template>
    <Head title="Add Training" />
    <PageHeader eyebrow="Training & Courses" title="Add training" description="Add a course, workshop, bootcamp, or professional development program." />
    <TrainingForm />
</template>
