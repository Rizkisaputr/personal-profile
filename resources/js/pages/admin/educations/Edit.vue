<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import type { Education } from '@/types';
import EducationForm from './EducationForm.vue';
defineProps<{ education: Education }>();
</script>
<template>
    <Head title="Edit Education" /><PageHeader
        eyebrow="Education"
        title="Edit education"
        description="Keep institution, study, and timeline details accurate."
    /><EducationForm :education="education" />
</template>
