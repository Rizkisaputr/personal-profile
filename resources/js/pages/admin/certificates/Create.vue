<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import CertificateForm from './CertificateForm.vue';
</script>
<template>
    <Head title="Add Certificate" /><PageHeader
        eyebrow="Certificates"
        title="Add certificate"
        description="Record a relevant credential without overloading the professional narrative."
    /><CertificateForm />
</template>
