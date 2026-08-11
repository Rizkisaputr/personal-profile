<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Plus } from '@lucide/vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { buttonVariants } from '@/components/ui/button';
import { formatIDR } from '@/lib/money';
import type { FinanceTransaction } from '@/types';
defineProps<{
    summary: {
        total_balance: string;
        income: string;
        expense: string;
        net_cash_flow: string;
        remaining_debt: string;
        receivables: string;
        total_savings: string;
    };
    recentTransactions: FinanceTransaction[];
}>();
function amount(transaction: FinanceTransaction): string {
    if (transaction.type === 'income') {
        return `+ ${formatIDR(transaction.amount)}`;
    }

    if (transaction.type === 'expense') {
        return `- ${formatIDR(transaction.amount)}`;
    }

    return formatIDR(transaction.amount);
}
</script>
<template>
    <Head title="Finance overview" /><PageHeader
        eyebrow="Personal"
        title="Finance overview"
        description="A private snapshot of your manually managed accounts and cash flow."
        ><template #actions
            ><Link
                href="/admin/finance/transactions/create"
                :class="buttonVariants()"
                ><Plus class="size-4" />Add transaction</Link
            ></template
        ></PageHeader
    >
    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article
            v-for="item in [
                { label: 'Total Balance', value: summary.total_balance },
                { label: 'Income This Month', value: summary.income },
                { label: 'Expense This Month', value: summary.expense },
                { label: 'Net Cash Flow', value: summary.net_cash_flow },
                { label: 'Remaining Debt', value: summary.remaining_debt },
                { label: 'Receivables', value: summary.receivables },
                { label: 'Total Savings', value: summary.total_savings },
            ]"
            :key="item.label"
            class="surface-panel p-5"
        >
            <p class="text-xs font-medium text-muted-foreground">
                {{ item.label }}
            </p>
            <p
                class="mt-3 text-xl font-semibold tracking-[-0.025em] tabular-nums"
            >
                {{ formatIDR(item.value) }}
            </p>
        </article>
    </div>
    <section class="surface-panel mt-8 overflow-hidden">
        <div
            class="flex items-center justify-between border-b border-border px-5 py-4"
        >
            <div>
                <h2 class="font-semibold">Recent transactions</h2>
                <p class="mt-1 text-xs text-muted-foreground">
                    Latest ledger activity
                </p>
            </div>
            <Link
                href="/admin/finance/transactions"
                class="inline-flex items-center gap-2 text-sm font-medium"
                >View all <ArrowRight class="size-4"
            /></Link>
        </div>
        <div
            v-if="!recentTransactions.length"
            class="px-5 py-12 text-center text-sm text-muted-foreground"
        >
            No transactions recorded yet.
        </div>
        <div v-else>
            <div
                v-for="transaction in recentTransactions"
                :key="transaction.id"
                class="flex items-center justify-between gap-5 border-b border-border px-5 py-4 last:border-0"
            >
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium">
                        {{ transaction.description || transaction.type }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        {{
                            new Date(
                                transaction.transaction_date,
                            ).toLocaleDateString('en-US')
                        }}
                        ·
                        {{
                            transaction.category?.name ||
                            transaction.account?.name ||
                            transaction.from_account?.name ||
                            'Transfer'
                        }}
                    </p>
                </div>
                <p
                    class="shrink-0 text-sm font-semibold tabular-nums"
                    :class="
                        transaction.type === 'income'
                            ? 'text-green-600'
                            : transaction.type === 'expense'
                              ? 'text-red-600'
                              : ''
                    "
                >
                    {{ amount(transaction) }}
                </p>
            </div>
        </div>
    </section>
</template>
