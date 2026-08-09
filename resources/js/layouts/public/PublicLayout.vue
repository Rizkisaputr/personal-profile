<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import PublicFooter from '@/components/public/PublicFooter.vue';
import PublicNavbar from '@/components/public/PublicNavbar.vue';

const page = usePage();
</script>

<template>
    <div class="min-h-svh bg-background text-foreground md:pl-[240px]">
        <PublicNavbar />
        <Transition name="page" mode="out-in">
            <main :key="page.url" class="pt-16 md:pt-0"><slot /></main>
        </Transition>
        <PublicFooter />
    </div>
</template>
