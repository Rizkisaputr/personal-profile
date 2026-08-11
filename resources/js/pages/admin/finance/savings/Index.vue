<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import { buttonVariants } from '@/components/ui/button';
import { formatIDR } from '@/lib/money';
type G = {
    id: number;
    name: string;
    saved_amount: string;
    target_amount: string;
    progress: number;
    current_month: { target: string; actual: string; progress: number };
    target_date: string | null;
    status: string;
};
defineProps<{ goals: G[] }>();
</script>
<template>
    <Head title="Savings" /><PageHeader
        eyebrow="Personal · Finance"
        title="Savings"
        description="Goals, monthly targets, contributions, and withdrawals."
        ><template #actions
            ><Link
                href="/admin/finance/savings/create"
                :class="buttonVariants()"
                >Add goal</Link
            ></template
        ></PageHeader
    >
    <div class="surface-panel mt-8">
        <div
            v-for="g in goals"
            :key="g.id"
            class="grid items-center gap-4 border-b p-5 lg:grid-cols-[1fr_1fr_1fr_8rem_auto]"
        >
            <div>
                <Link
                    :href="`/admin/finance/savings/${g.id}`"
                    class="font-medium hover:text-primary"
                    >{{ g.name }}</Link
                >
                <p class="text-xs text-muted-foreground">{{ g.status }}</p>
            </div>
            <div>
                <p class="text-sm">
                    {{ formatIDR(g.saved_amount) }} /
                    {{ formatIDR(g.target_amount) }}
                </p>
                <div class="mt-2 h-1.5 rounded bg-muted">
                    <div
                        class="h-full rounded bg-primary"
                        :style="{ width: `${g.progress}%` }"
                    />
                </div>
            </div>
            <div>
                <p class="text-sm">
                    This month {{ g.current_month.progress }}%
                </p>
                <p class="text-xs text-muted-foreground">
                    {{ formatIDR(g.current_month.actual) }} /
                    {{ formatIDR(g.current_month.target) }}
                </p>
            </div>
            <p class="text-sm text-muted-foreground">
                {{
                    g.target_date
                        ? new Date(g.target_date).toLocaleDateString('en-US')
                        : '—'
                }}
            </p>
            <Link
                :href="`/admin/finance/savings/${g.id}`"
                :class="buttonVariants({ variant: 'outline', size: 'sm' })"
            >
                Manage Savings
            </Link>
        </div>
        <p v-if="!goals.length" class="p-10 text-center text-muted-foreground">
            No savings goals.
        </p>
    </div>
</template>
