<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import CurrencyInput from '@/components/admin/CurrencyInput.vue';
import FormField from '@/components/admin/FormField.vue';
import FormTextarea from '@/components/admin/FormTextarea.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatIDR, moneyDifference } from '@/lib/money';
import type { FinanceAccount } from '@/types';
const props = defineProps<{ account: FinanceAccount }>();
const form = useForm({
    actual_balance: props.account.current_balance || '0',
    transaction_date: new Date().toISOString().slice(0, 10),
    description: 'Account balance adjustment.',
});
const difference = computed(() =>
    moneyDifference(
        form.actual_balance || '0',
        props.account.current_balance || '0',
    ),
);
function submit(): void {
    form.post(`/admin/finance/accounts/${props.account.id}/adjust`);
}
</script>
<template>
    <Head title="Adjust account balance" /><PageHeader
        eyebrow="Finance · Accounts"
        :title="`Adjust ${account.name}`"
        description="Record a ledger adjustment to match the actual account balance."
    />
    <div class="mt-8 grid gap-6 lg:grid-cols-[0.7fr_1.3fr]">
        <div class="surface-panel p-5">
            <p class="text-xs text-muted-foreground">Current system balance</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums">
                {{ formatIDR(account.current_balance || '0') }}
            </p>
            <div class="mt-6 border-t border-border pt-5">
                <p class="text-xs text-muted-foreground">Difference</p>
                <p
                    class="mt-2 text-lg font-semibold tabular-nums"
                    :class="
                        difference.startsWith('-')
                            ? 'text-red-600'
                            : difference !== '0.00'
                              ? 'text-green-600'
                              : ''
                    "
                >
                    {{ formatIDR(difference) }}
                </p>
            </div>
        </div>
        <form class="surface-panel p-5 sm:p-8" @submit.prevent="submit">
            <div class="grid gap-6">
                <FormField
                    id="actual_balance"
                    label="Actual balance"
                    :error="form.errors.actual_balance"
                    required
                    ><CurrencyInput
                        id="actual_balance"
                        v-model="form.actual_balance"
                        allow-negative
                        placeholder="Example: 1,000,000" /></FormField
                ><FormField
                    id="transaction_date"
                    label="Date"
                    :error="form.errors.transaction_date"
                    required
                    ><Input
                        id="transaction_date"
                        v-model="form.transaction_date"
                        type="date" /></FormField
                ><FormField
                    id="description"
                    label="Reason"
                    :error="form.errors.description"
                    required
                    ><FormTextarea
                        id="description"
                        v-model="form.description"
                        :rows="4"
                /></FormField>
            </div>
            <div class="mt-8 flex gap-3 border-t border-border pt-6">
                <Button type="submit" :disabled="form.processing"
                    >Create adjustment</Button
                ><Link
                    href="/admin/finance/accounts"
                    :class="buttonVariants({ variant: 'outline' })"
                    >Cancel</Link
                >
            </div>
        </form>
    </div>
</template>
