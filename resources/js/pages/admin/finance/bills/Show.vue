<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import CurrencyInput from '@/components/admin/CurrencyInput.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatIDR } from '@/lib/money';
type Bill = {
    id: number;
    name: string;
    amount: string;
    next_due_date: string;
    default_account_id: number | null;
    payments: {
        id: number;
        amount: string;
        paid_at: string;
        notes: string | null;
    }[];
};
const p = defineProps<{
    bill: Bill;
    accounts: { id: number; name: string }[];
}>();
const f = useForm({
    amount: p.bill.amount,
    paid_at: new Date().toISOString().slice(0, 10),
    account_id: p.bill.default_account_id ?? '',
    notes: '',
});
</script>
<template>
    <Head :title="bill.name" /><PageHeader
        eyebrow="Finance · Bills"
        :title="bill.name"
        :description="`Jatuh tempo berikutnya ${new Date(bill.next_due_date).toLocaleDateString('id-ID')}`"
        ><template #actions
            ><Link
                :href="`/admin/finance/bills/${bill.id}/edit`"
                :class="buttonVariants({ variant: 'outline' })"
                >Edit</Link
            ></template
        ></PageHeader
    >
    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <form
            class="surface-panel p-6"
            @submit.prevent="
                f.post(`/admin/finance/bills/${bill.id}/payments`, {
                    onSuccess: () => f.reset('notes'),
                })
            "
        >
            <h2 class="font-semibold">Mark as Paid</h2>
            <div class="mt-4 grid gap-3">
                <CurrencyInput v-model="f.amount" placeholder="Contoh: 500.000" /><Input
                    v-model="f.paid_at"
                    type="date"
                /><select
                    v-model="f.account_id"
                    class="h-10 rounded-md border px-3"
                >
                    <option value="">Select account</option>
                    <option v-for="a in accounts" :key="a.id" :value="a.id">
                        {{ a.name }}
                    </option></select
                ><Input v-model="f.notes" placeholder="Notes" /><Button
                    >Mark as paid</Button
                >
            </div>
        </form>
        <div class="surface-panel p-6">
            <h2 class="font-semibold">Payment history</h2>
            <div
                v-for="pmt in bill.payments"
                :key="pmt.id"
                class="flex justify-between border-b py-3 text-sm"
            >
                <span>{{
                    new Date(pmt.paid_at).toLocaleDateString('id-ID')
                }}</span
                ><span>{{ formatIDR(pmt.amount) }}</span>
            </div>
        </div>
    </div>
</template>
