<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import type { FinanceAccount } from '@/types';
import AccountForm from './AccountForm.vue';
defineProps<{ account: FinanceAccount }>();
</script>
<template><Head title="Edit finance account" /><PageHeader eyebrow="Finance" title="Edit account" description="Update account metadata and opening balance." /><AccountForm :account="account" /></template>
