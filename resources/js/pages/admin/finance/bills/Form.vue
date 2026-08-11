<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import CurrencyInput from '@/components/admin/CurrencyInput.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
type B = {
    id: number;
    name: string;
    category_id: number | null;
    amount: string;
    frequency: string;
    due_day: number | null;
    custom_interval_days: number | null;
    next_due_date: string;
    default_account_id: number | null;
    is_active: boolean;
    notes: string | null;
};
const p = defineProps<{
    bill?: B;
    categories: { id: number; name: string }[];
    accounts: { id: number; name: string }[];
}>();
const f = useForm({
    name: p.bill?.name ?? '',
    category_id: p.bill?.category_id ?? '',
    amount: p.bill?.amount ?? '',
    frequency: p.bill?.frequency ?? 'monthly',
    due_day: p.bill?.due_day ?? '',
    custom_interval_days: p.bill?.custom_interval_days ?? '',
    next_due_date: p.bill?.next_due_date?.slice(0, 10) ?? '',
    default_account_id: p.bill?.default_account_id ?? '',
    is_active: p.bill?.is_active ?? true,
    notes: p.bill?.notes ?? '',
});
function submit() {
    if (p.bill) {
        f.put(`/admin/finance/bills/${p.bill.id}`);
    } else {
        f.post('/admin/finance/bills');
    }
}
</script>
<template>
    <Head title="Recurring bill" /><PageHeader
        eyebrow="Finance"
        :title="bill ? 'Edit recurring bill' : 'Add recurring bill'"
        description="Configure a manually paid recurring obligation."
    />
    <form
        class="surface-panel mt-8 grid gap-5 p-6 md:grid-cols-2"
        @submit.prevent="submit"
    >
        <Input v-model="f.name" placeholder="Bill name" /><CurrencyInput
            v-model="f.amount"
            placeholder="Example: 500,000"
        /><select v-model="f.category_id" class="h-10 rounded-md border px-3">
            <option value="">Category</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">
                {{ c.name }}
            </option></select
        ><select v-model="f.frequency" class="h-10 rounded-md border px-3">
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
            <option value="custom">Custom</option></select
        ><Input
            v-if="f.frequency === 'custom'"
            v-model="f.custom_interval_days"
            type="number"
            placeholder="Custom interval days"
        /><Input
            v-else
            v-model="f.due_day"
            type="number"
            min="1"
            max="31"
            placeholder="Due day"
        /><Input v-model="f.next_due_date" type="date" /><select
            v-model="f.default_account_id"
            class="h-10 rounded-md border px-3"
        >
            <option value="">Default account</option>
            <option v-for="a in accounts" :key="a.id" :value="a.id">
                {{ a.name }}
            </option></select
        ><label class="flex items-center gap-2"
            ><input v-model="f.is_active" type="checkbox" /> Active</label
        ><Input v-model="f.notes" placeholder="Notes" />
        <div class="flex gap-3 md:col-span-2">
            <Button>Save</Button
            ><Link
                href="/admin/finance/bills"
                :class="buttonVariants({ variant: 'outline' })"
                >Cancel</Link
            >
        </div>
    </form>
</template>
