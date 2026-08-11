<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import type { FinanceCategory } from '@/types';
import Form from './Form.vue';

defineProps<{ category: FinanceCategory }>();
</script>

<template>
    <Head title="Edit finance category" />
    <PageHeader
        eyebrow="Finance · Categories"
        title="Edit category"
        description="Update the category name, transaction type, or status."
    />
    <Form :category="category" />
</template>
