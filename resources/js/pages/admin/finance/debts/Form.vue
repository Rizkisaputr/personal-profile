<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import CurrencyInput from '@/components/admin/CurrencyInput.vue';
import FormField from '@/components/admin/FormField.vue';
import FormTextarea from '@/components/admin/FormTextarea.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
type Debt = {
    id: number;
    name: string;
    type: 'debt' | 'receivable';
    party_name: string | null;
    original_amount: string;
    start_date: string | null;
    notes: string | null;
    status: string;
};
const p = defineProps<{ debt?: Debt }>();
const f = useForm({
    name: p.debt?.name ?? '',
    type: p.debt?.type ?? 'debt',
    party_name: p.debt?.party_name ?? '',
    original_amount: p.debt?.original_amount ?? '',
    start_date: p.debt?.start_date?.slice(0, 10) ?? '',
    notes: p.debt?.notes ?? '',
    status: p.debt?.status ?? 'active',
});
function submit() {
    if (p.debt) {
        f.put(`/admin/finance/debts/${p.debt.id}`);
    } else {
        f.post('/admin/finance/debts');
    }
}
</script>
<template>
    <Head title="Debt" /><PageHeader
        eyebrow="Finance"
        :title="debt ? 'Edit debt or receivable' : 'Add debt or receivable'"
        description="Track an amount owed with history-derived progress."
    />
    <form
        class="surface-panel mt-8 grid gap-6 p-6 md:grid-cols-2"
        @submit.prevent="submit"
    >
        <FormField id="name" label="Name" :error="f.errors.name"
            ><Input id="name" v-model="f.name" /></FormField
        ><FormField id="type" label="Type" :error="f.errors.type"
            ><select
                id="type"
                v-model="f.type"
                class="h-10 rounded-md border px-3"
            >
                <option value="debt">Debt</option>
                <option value="receivable">Receivable</option>
            </select></FormField
        ><FormField id="party" label="Party"
            ><Input id="party" v-model="f.party_name" /></FormField
        ><FormField
            id="amount"
            label="Original amount"
            :error="f.errors.original_amount"
            ><CurrencyInput
                id="amount"
                v-model="f.original_amount"
                placeholder="Contoh: 1.000.000" /></FormField
        ><FormField id="start" label="Start date"
            ><Input id="start" v-model="f.start_date" type="date" /></FormField
        ><FormField id="status" label="Status"
            ><select
                id="status"
                v-model="f.status"
                class="h-10 rounded-md border px-3"
            >
                <option value="active">Active</option>
                <option value="paid">Paid</option>
                <option value="cancelled">Cancelled</option>
            </select></FormField
        >
        <div class="md:col-span-2">
            <FormField id="notes" label="Notes"
                ><FormTextarea id="notes" v-model="f.notes" :rows="3"
            /></FormField>
        </div>
        <div class="flex gap-3 md:col-span-2">
            <Button>Save</Button
            ><Link
                href="/admin/finance/debts"
                :class="buttonVariants({ variant: 'outline' })"
                >Cancel</Link
            >
        </div>
    </form>
</template>
