<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import CurrencyInput from '@/components/admin/CurrencyInput.vue';
import FormField from '@/components/admin/FormField.vue';
import FormTextarea from '@/components/admin/FormTextarea.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type {
    FinanceAccount,
    FinanceCategory,
    FinanceTransaction,
} from '@/types';

const props = defineProps<{
    accounts: Pick<FinanceAccount, 'id' | 'name' | 'type'>[];
    categories: FinanceCategory[];
    transaction?: FinanceTransaction;
}>();
const form = useForm({
    type: props.transaction?.type ?? 'expense',
    transaction_date:
        props.transaction?.transaction_date?.slice(0, 10) ??
        new Date().toISOString().slice(0, 10),
    amount: props.transaction?.amount ?? '',
    category_id: props.transaction?.category_id ?? '',
    account_id: props.transaction?.account_id ?? '',
    from_account_id: props.transaction?.from_account_id ?? '',
    to_account_id: props.transaction?.to_account_id ?? '',
    adjustment_direction: props.transaction?.adjustment_direction ?? 'increase',
    description: props.transaction?.description ?? '',
});
const visibleCategories = computed(() =>
    props.categories.filter((category) => category.type === form.type),
);
watch(
    () => form.type,
    () => {
        form.category_id = '';
        form.account_id = '';
        form.from_account_id = '';
        form.to_account_id = '';
        form.adjustment_direction = 'increase';
        form.clearErrors();
    },
);
function submit(): void {
    if (props.transaction) {
        form.put(`/admin/finance/transactions/${props.transaction.id}`);

        return;
    }

    form.post('/admin/finance/transactions');
}
</script>

<template>
    <Head
        :title="transaction ? 'Edit transaction' : 'Add transaction'"
    /><PageHeader
        eyebrow="Personal · Finance"
        :title="transaction ? 'Edit transaction' : 'Add transaction'"
        description="Record income, expense, transfer, or a balance adjustment manually."
    />
    <form class="surface-panel mt-8 p-5 sm:p-8" @submit.prevent="submit">
        <div class="grid gap-6 md:grid-cols-2">
            <FormField id="type" label="Type" :error="form.errors.type" required
                ><select
                    id="type"
                    v-model="form.type"
                    class="h-10 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                >
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                    <option value="transfer">Transfer</option>
                    <option value="adjustment">Adjustment</option>
                </select></FormField
            >
            <FormField
                id="transaction_date"
                label="Date"
                :error="form.errors.transaction_date"
                required
                ><Input
                    id="transaction_date"
                    v-model="form.transaction_date"
                    type="date"
            /></FormField>
            <template v-if="form.type === 'income' || form.type === 'expense'"
                ><FormField
                    id="category_id"
                    label="Category"
                    :error="form.errors.category_id"
                    required
                    ><select
                        id="category_id"
                        v-model="form.category_id"
                        class="h-10 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                    >
                        <option value="" disabled>Select category</option>
                        <option
                            v-for="category in visibleCategories"
                            :key="category.id"
                            :value="category.id"
                        >
                            {{ category.name }}
                        </option>
                    </select></FormField
                ><FormField
                    id="account_id"
                    label="Account"
                    :error="form.errors.account_id"
                    required
                    ><select
                        id="account_id"
                        v-model="form.account_id"
                        class="h-10 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                    >
                        <option value="" disabled>Select account</option>
                        <option
                            v-for="account in accounts"
                            :key="account.id"
                            :value="account.id"
                        >
                            {{ account.name }}
                        </option>
                    </select></FormField
                ></template
            >
            <template v-else-if="form.type === 'transfer'"
                ><FormField
                    id="from_account_id"
                    label="From account"
                    :error="form.errors.from_account_id"
                    required
                    ><select
                        id="from_account_id"
                        v-model="form.from_account_id"
                        class="h-10 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                    >
                        <option value="" disabled>Select source</option>
                        <option
                            v-for="account in accounts"
                            :key="account.id"
                            :value="account.id"
                        >
                            {{ account.name }}
                        </option>
                    </select></FormField
                ><FormField
                    id="to_account_id"
                    label="To account"
                    :error="form.errors.to_account_id"
                    required
                    ><select
                        id="to_account_id"
                        v-model="form.to_account_id"
                        class="h-10 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                    >
                        <option value="" disabled>Select destination</option>
                        <option
                            v-for="account in accounts"
                            :key="account.id"
                            :value="account.id"
                            :disabled="
                                String(account.id) ===
                                String(form.from_account_id)
                            "
                        >
                            {{ account.name }}
                        </option>
                    </select></FormField
                ></template
            >
            <template v-else
                ><FormField
                    id="account_id"
                    label="Account"
                    :error="form.errors.account_id"
                    required
                    ><select
                        id="account_id"
                        v-model="form.account_id"
                        class="h-10 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                    >
                        <option value="" disabled>Select account</option>
                        <option
                            v-for="account in accounts"
                            :key="account.id"
                            :value="account.id"
                        >
                            {{ account.name }}
                        </option>
                    </select></FormField
                ><FormField
                    id="adjustment_direction"
                    label="Direction"
                    :error="form.errors.adjustment_direction"
                    required
                    ><select
                        id="adjustment_direction"
                        v-model="form.adjustment_direction"
                        class="h-10 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                    >
                        <option value="increase">Increase</option>
                        <option value="decrease">Decrease</option>
                    </select></FormField
                ></template
            >
            <FormField
                id="amount"
                label="Amount"
                :error="form.errors.amount"
                required
                ><CurrencyInput
                    id="amount"
                    v-model="form.amount"
                    placeholder="Example: 1,000,000"
            /></FormField>
            <div class="md:col-span-2">
                <FormField
                    id="description"
                    :label="
                        form.type === 'adjustment' ? 'Reason' : 'Description'
                    "
                    :error="form.errors.description"
                    ><FormTextarea
                        id="description"
                        v-model="form.description"
                        :rows="4"
                /></FormField>
            </div>
        </div>
        <div class="mt-8 flex gap-3 border-t border-border pt-6">
            <Button type="submit" :disabled="form.processing">{{
                form.processing ? 'Saving…' : 'Save transaction'
            }}</Button
            ><Link
                href="/admin/finance/transactions"
                :class="buttonVariants({ variant: 'outline' })"
                >Cancel</Link
            >
        </div>
    </form>
</template>
