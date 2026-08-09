<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import ExperienceForm from './ExperienceForm.vue';
</script>
<template>
    <Head title="Add Experience" /><PageHeader
        eyebrow="Experience"
        title="Add experience"
        description="Capture the role, period, and work that best explains your professional path."
    /><ExperienceForm />
</template>
