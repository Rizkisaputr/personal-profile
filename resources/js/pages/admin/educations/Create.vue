<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import EducationForm from './EducationForm.vue';
</script>
<template>
    <Head title="Add Education" /><PageHeader
        eyebrow="Education"
        title="Add education"
        description="Add a concise academic entry that supports your professional story."
    /><EducationForm />
</template>
