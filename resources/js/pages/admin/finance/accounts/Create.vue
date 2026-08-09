<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import AccountForm from './AccountForm.vue';
</script>
<template><Head title="Add finance account" /><PageHeader eyebrow="Finance" title="Add account" description="Create a manually managed account for your private finance ledger." /><AccountForm /></template>
