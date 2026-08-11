<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import Form from './Form.vue';
</script>

<template>
    <Head title="Add finance category" />
    <PageHeader
        eyebrow="Finance · Categories"
        title="Add category"
        description="Create an income or expense category for finance transactions."
    />
    <Form />
</template>
