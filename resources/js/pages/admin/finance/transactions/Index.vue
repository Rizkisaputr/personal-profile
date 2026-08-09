<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Search } from '@lucide/vue';
import { reactive } from 'vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatIDR } from '@/lib/money';
import type { FinanceAccount, FinanceCategory, FinanceTransaction } from '@/types';
type Pagination = { data: FinanceTransaction[]; current_page: number; last_page: number; prev_page_url: string | null; next_page_url: string | null };
const props = defineProps<{ transactions: Pagination; accounts: Pick<FinanceAccount, 'id' | 'name'>[]; categories: FinanceCategory[]; filters: Record<string, string | undefined> }>();
const filters = reactive({ date_from: props.filters.date_from || '', date_to: props.filters.date_to || '', type: props.filters.type || '', account: props.filters.account || '', category: props.filters.category || '', search: props.filters.search || '' });
function applyFilters(): void {
 router.get('/admin/finance/transactions', filters, { preserveState: true, replace: true }); 
}
function accountLabel(item: FinanceTransaction): string {
 return item.type === 'transfer' ? `${item.from_account?.name || '—'} → ${item.to_account?.name || '—'}` : item.account?.name || '—'; 
}
function amountClass(item: FinanceTransaction): string {
 if (item.type === 'income') {
return 'text-green-600';
}

 if (item.type === 'expense') {
return 'text-red-600';
}

 return 'text-foreground'; 
}
function amountLabel(item: FinanceTransaction): string {
 if (item.type === 'income') {
return `+ ${formatIDR(item.amount)}`;
}

 if (item.type === 'expense') {
return `- ${formatIDR(item.amount)}`;
}

 if (item.type === 'adjustment') {
return `${item.adjustment_direction === 'decrease' ? '- ' : '+ '}${formatIDR(item.amount)}`;
}

 return formatIDR(item.amount); 
}
</script>
<template>
    <Head title="Finance transactions" /><PageHeader eyebrow="Personal · Finance" title="Transactions" description="Review and filter your private manual transaction ledger."><template #actions><Link href="/admin/finance/transactions/create" :class="buttonVariants()"><Plus class="size-4" />Add transaction</Link></template></PageHeader>
    <form class="surface-panel mt-8 grid gap-3 p-4 md:grid-cols-3 xl:grid-cols-6" @submit.prevent="applyFilters"><Input v-model="filters.date_from" type="date" aria-label="Date from" /><Input v-model="filters.date_to" type="date" aria-label="Date to" /><select v-model="filters.type" class="h-10 rounded-md border border-input bg-transparent px-3 text-sm"><option value="">All types</option><option value="income">Income</option><option value="expense">Expense</option><option value="transfer">Transfer</option><option value="adjustment">Adjustment</option></select><select v-model="filters.account" class="h-10 rounded-md border border-input bg-transparent px-3 text-sm"><option value="">All accounts</option><option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.name }}</option></select><select v-model="filters.category" class="h-10 rounded-md border border-input bg-transparent px-3 text-sm"><option value="">All categories</option><option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option></select><div class="flex gap-2"><Input v-model="filters.search" placeholder="Search…" /><button type="submit" class="grid size-10 shrink-0 place-items-center rounded-md bg-foreground text-background" aria-label="Apply filters"><Search class="size-4" /></button></div></form>
    <div class="surface-panel mt-6 overflow-x-auto"><table class="w-full min-w-[900px] text-left text-sm"><thead class="border-b border-border bg-muted/40 text-xs text-muted-foreground"><tr><th class="px-5 py-3 font-medium">Date</th><th class="px-5 py-3 font-medium">Description</th><th class="px-5 py-3 font-medium">Category</th><th class="px-5 py-3 font-medium">Account</th><th class="px-5 py-3 font-medium">Type</th><th class="px-5 py-3 text-right font-medium">Amount</th></tr></thead><tbody><tr v-if="!transactions.data.length"><td colspan="6" class="px-5 py-12 text-center text-muted-foreground">No transactions found.</td></tr><tr v-for="item in transactions.data" :key="item.id" class="border-b border-border last:border-0"><td class="px-5 py-4 text-muted-foreground">{{ new Date(item.transaction_date).toLocaleDateString('id-ID') }}</td><td class="max-w-xs px-5 py-4"><p class="truncate font-medium">{{ item.description || 'No description' }}</p></td><td class="px-5 py-4 text-muted-foreground">{{ item.category?.name || '—' }}</td><td class="px-5 py-4 text-muted-foreground">{{ accountLabel(item) }}</td><td class="px-5 py-4 capitalize">{{ item.type }}</td><td class="px-5 py-4 text-right font-semibold tabular-nums" :class="amountClass(item)">{{ amountLabel(item) }}</td></tr></tbody></table></div>
    <div v-if="transactions.last_page > 1" class="mt-5 flex justify-end gap-2"><Link v-if="transactions.prev_page_url" :href="transactions.prev_page_url" :class="buttonVariants({ variant: 'outline', size: 'sm' })">Previous</Link><Link v-if="transactions.next_page_url" :href="transactions.next_page_url" :class="buttonVariants({ variant: 'outline', size: 'sm' })">Next</Link></div>
</template>
