<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import type { Training } from '@/types';
import TrainingForm from './TrainingForm.vue';

defineProps<{ training: Training }>();
</script>

<template>
    <Head title="Edit Training" />
    <PageHeader eyebrow="Training & Courses" title="Edit training" description="Keep program, institution, and timeline details accurate." />
    <TrainingForm :training="training" />
</template>
