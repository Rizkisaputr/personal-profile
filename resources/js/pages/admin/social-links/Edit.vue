<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import type { SocialLink } from '@/types';
import SocialLinkForm from './SocialLinkForm.vue';
defineProps<{ socialLink: SocialLink }>();
</script>
<template>
    <Head title="Edit Social Link" /><PageHeader
        eyebrow="Social links"
        title="Edit social link"
        description="Keep the destination, label, and visibility accurate."
    /><SocialLinkForm :social-link="socialLink" />
</template>
