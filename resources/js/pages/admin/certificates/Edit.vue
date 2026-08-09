<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import type { Certificate } from '@/types';
import CertificateForm from './CertificateForm.vue';
defineProps<{ certificate: Certificate }>();
</script>
<template>
    <Head title="Edit Certificate" /><PageHeader
        eyebrow="Certificates"
        title="Edit certificate"
        description="Keep credential details and visibility current."
    /><CertificateForm :certificate="certificate" />
</template>
