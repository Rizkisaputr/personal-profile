<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import CurrencyInput from '@/components/admin/CurrencyInput.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatIDR } from '@/lib/money';
type S = {
    id: number;
    service_name: string;
    cost: string;
    next_renewal_date: string;
    payments: {
        id: number;
        amount: string;
        paid_at: string;
        notes: string | null;
    }[];
};
const props = defineProps<{
    subscription: S;
    accounts: { id: number; name: string }[];
}>();
const form = useForm({
    amount: props.subscription.cost,
    paid_at: new Date().toISOString().slice(0, 10),
    account_id: '',
    notes: '',
});
</script>
<template>
    <Head :title="subscription.service_name" /><PageHeader
        eyebrow="Personal · Subscription"
        :title="subscription.service_name"
        :description="`Next renewal ${new Date(subscription.next_renewal_date).toLocaleDateString('en-US')}`"
        ><template #actions
            ><Link
                :href="`/admin/subscriptions/${subscription.id}/edit`"
                :class="buttonVariants({ variant: 'outline' })"
                >Edit</Link
            ></template
        ></PageHeader
    >
    <div class="mt-8 grid gap-6 lg:grid-cols-[.8fr_1.2fr]">
        <form
            class="surface-panel p-5"
            @submit.prevent="
                form.post(`/admin/subscriptions/${subscription.id}/payments`, {
                    onSuccess: () => form.reset('notes'),
                })
            "
        >
            <h2 class="font-semibold">Mark as paid</h2>
            <div class="mt-4 grid gap-3">
                <CurrencyInput
                    v-model="form.amount"
                    placeholder="Example: 150,000"
                /><Input v-model="form.paid_at" type="date" /><select
                    v-model="form.account_id"
                    class="h-10 rounded-md border bg-background px-3"
                >
                    <option value="">Select account</option>
                    <option
                        v-for="account in accounts"
                        :key="account.id"
                        :value="account.id"
                    >
                        {{ account.name }}
                    </option></select
                ><Input
                    v-model="form.notes"
                    placeholder="Notes (optional)"
                /><Button>Save payment</Button>
            </div>
        </form>
        <section class="surface-panel p-5">
            <h2 class="font-semibold">Payment history</h2>
            <div
                v-for="payment in subscription.payments"
                :key="payment.id"
                class="flex justify-between border-b py-3 text-sm"
            >
                <span>{{
                    new Date(payment.paid_at).toLocaleDateString('en-US')
                }}</span
                ><span>{{ formatIDR(payment.amount) }}</span>
            </div>
            <p
                v-if="!subscription.payments.length"
                class="mt-4 text-sm text-muted-foreground"
            >
                No payments yet.
            </p>
        </section>
    </div>
</template>
