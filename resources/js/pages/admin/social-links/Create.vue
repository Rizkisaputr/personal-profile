<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import SocialLinkForm from './SocialLinkForm.vue';
</script>
<template>
    <Head title="Add Social Link" /><PageHeader
        eyebrow="Social links"
        title="Add social link"
        description="Add a professional channel or contact destination."
    /><SocialLinkForm />
</template>
