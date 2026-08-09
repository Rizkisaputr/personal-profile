<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import SkillForm from './SkillForm.vue';
</script>

<template>
    <Head title="Add Skill" />
    <PageHeader
        eyebrow="Skills"
        title="Add skill"
        description="Add a capability and place it in a clear, credible category."
    />
    <SkillForm />
</template>
