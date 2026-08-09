<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import type { Skill } from '@/types';
import SkillForm from './SkillForm.vue';

defineProps<{ skill: Skill }>();
</script>

<template>
    <Head title="Edit Skill" />
    <PageHeader
        eyebrow="Skills"
        title="Edit skill"
        description="Keep the skill name, grouping, and visibility accurate."
    />
    <SkillForm :skill="skill" />
</template>
