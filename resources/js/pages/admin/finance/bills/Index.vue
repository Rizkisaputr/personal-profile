<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import { buttonVariants } from '@/components/ui/button';
import { formatIDR } from '@/lib/money';
type B = {
    id: number;
    name: string;
    amount: string;
    frequency: string;
    next_due_date: string;
    is_active: boolean;
    category: { name: string } | null;
    default_account: { name: string } | null;
};
defineProps<{ bills: B[] }>();
</script>
<template>
    <Head title="Recurring Bills" /><PageHeader
        eyebrow="Personal · Finance"
        title="Recurring Bills"
        description="Track upcoming manually paid recurring expenses."
        ><template #actions
            ><Link href="/admin/finance/bills/create" :class="buttonVariants()"
                >Add bill</Link
            ></template
        ></PageHeader
    >
    <div class="surface-panel mt-8">
        <div
            v-for="b in bills"
            :key="b.id"
            class="grid gap-3 border-b p-5 sm:grid-cols-[1fr_9rem_10rem_8rem]"
        >
            <div>
                <Link
                    :href="`/admin/finance/bills/${b.id}`"
                    class="font-medium hover:text-primary"
                    >{{ b.name }}</Link
                >
                <p class="text-xs text-muted-foreground">
                    {{ b.category?.name || 'Uncategorized' }} ·
                    {{ b.frequency }}
                </p>
            </div>
            <p>{{ formatIDR(b.amount) }}</p>
            <p
                :class="
                    new Date(b.next_due_date) < new Date() ? 'text-red-600' : ''
                "
            >
                {{ new Date(b.next_due_date).toLocaleDateString('id-ID') }}
            </p>
            <p>{{ b.is_active ? 'Active' : 'Inactive' }}</p>
        </div>
        <p v-if="!bills.length" class="p-10 text-center text-muted-foreground">
            No recurring bills.
        </p>
    </div>
</template>
