<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import CurrencyInput from '@/components/admin/CurrencyInput.vue';
import FormCheckbox from '@/components/admin/FormCheckbox.vue';
import FormField from '@/components/admin/FormField.vue';
import FormTextarea from '@/components/admin/FormTextarea.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { FinanceAccount } from '@/types';

const props = defineProps<{ account?: FinanceAccount }>();
const form = useForm({ name: props.account?.name ?? '', type: props.account?.type ?? 'bank', opening_balance: props.account?.opening_balance ?? '0', currency: props.account?.currency ?? 'IDR', notes: props.account?.notes ?? '', is_active: props.account?.is_active ?? true });
function submit(): void {
    if (props.account) {
        form.put(`/admin/finance/accounts/${props.account.id}`);

        return;
    }

    form.post('/admin/finance/accounts');
}
</script>

<template>
    <form class="surface-panel mt-8 p-5 sm:p-8" @submit.prevent="submit">
        <div class="grid gap-6 md:grid-cols-2">
            <FormField id="name" label="Account name" :error="form.errors.name" required><Input id="name" v-model="form.name" placeholder="BCA, Cash, GoPay" /></FormField>
            <FormField id="type" label="Account type" :error="form.errors.type" required><select id="type" v-model="form.type" class="h-10 w-full rounded-md border border-input bg-transparent px-3 text-sm"><option value="bank">Bank</option><option value="cash">Cash</option><option value="e_wallet">E-wallet</option><option value="other">Other</option></select></FormField>
            <FormField id="opening_balance" label="Opening balance" hint="Initial ledger balance. Current balance cannot be edited directly." :error="form.errors.opening_balance" required><CurrencyInput id="opening_balance" v-model="form.opening_balance" allow-negative placeholder="Contoh: 1.000.000" /></FormField>
            <FormField id="currency" label="Currency" :error="form.errors.currency" required><Input id="currency" v-model="form.currency" maxlength="3" /></FormField>
            <div class="md:col-span-2"><FormField id="notes" label="Notes" :error="form.errors.notes"><FormTextarea id="notes" v-model="form.notes" :rows="4" /></FormField></div>
            <div class="md:col-span-2"><FormCheckbox id="is_active" v-model="form.is_active" label="Active account" description="Inactive accounts remain available in transaction history." /></div>
        </div>
        <div class="mt-8 flex gap-3 border-t border-border pt-6"><Button type="submit" :disabled="form.processing">{{ form.processing ? 'Saving…' : 'Save account' }}</Button><Link href="/admin/finance/accounts" :class="buttonVariants({ variant: 'outline' })">Cancel</Link></div>
    </form>
</template>
