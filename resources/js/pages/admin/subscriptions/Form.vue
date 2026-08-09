<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import CurrencyInput from '@/components/admin/CurrencyInput.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
type Subscription = {
    id: number;
    service_name: string;
    cost: string;
    billing_cycle: string;
    custom_interval_days: number | null;
    start_date: string | null;
    next_renewal_date: string;
    payment_account_id: number | null;
    is_active: boolean;
    notes: string | null;
};
const props = defineProps<{
    subscription?: Subscription;
    accounts: { id: number; name: string }[];
}>();
const form = useForm({
    service_name: props.subscription?.service_name ?? '',
    cost: props.subscription?.cost ?? '',
    billing_cycle: props.subscription?.billing_cycle ?? 'monthly',
    custom_interval_days: props.subscription?.custom_interval_days ?? '',
    start_date: props.subscription?.start_date?.slice(0, 10) ?? '',
    next_renewal_date:
        props.subscription?.next_renewal_date?.slice(0, 10) ?? '',
    payment_account_id: props.subscription?.payment_account_id ?? '',
    is_active: props.subscription?.is_active ?? true,
    notes: props.subscription?.notes ?? '',
});
function submit() {
    if (props.subscription) {
form.put(`/admin/subscriptions/${props.subscription.id}`);
} else {
form.post('/admin/subscriptions');
}
}
</script>
<template>
    <Head title="Subscription" /><PageHeader
        eyebrow="Personal"
        :title="subscription ? 'Edit subscription' : 'Add subscription'"
        description="Track recurring services and manual payments."
    />
    <form
        class="surface-panel mt-8 grid gap-5 p-6 md:grid-cols-2"
        @submit.prevent="submit"
    >
        <label class="grid gap-2 text-sm"
            >Service name<Input v-model="form.service_name" /></label
        ><label class="grid gap-2 text-sm"
            >Cost<CurrencyInput v-model="form.cost" placeholder="Contoh: 150.000" /></label
        ><label class="grid gap-2 text-sm"
            >Billing cycle<select
                v-model="form.billing_cycle"
                class="h-10 rounded-md border bg-background px-3"
            >
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
                <option value="custom">Custom</option>
            </select></label
        ><label
            v-if="form.billing_cycle === 'custom'"
            class="grid gap-2 text-sm"
            >Interval days<Input
                v-model="form.custom_interval_days"
                type="number"
                min="1" /></label
        ><label class="grid gap-2 text-sm"
            >Start date<Input v-model="form.start_date" type="date" /></label
        ><label class="grid gap-2 text-sm"
            >Next renewal<Input
                v-model="form.next_renewal_date"
                type="date" /></label
        ><label class="grid gap-2 text-sm"
            >Payment account<select
                v-model="form.payment_account_id"
                class="h-10 rounded-md border bg-background px-3"
            >
                <option value="">None</option>
                <option
                    v-for="account in accounts"
                    :key="account.id"
                    :value="account.id"
                >
                    {{ account.name }}
                </option>
            </select></label
        ><label class="grid gap-2 text-sm md:col-span-2"
            >Notes<textarea
                v-model="form.notes"
                class="min-h-24 rounded-md border bg-background p-3"
            /></label
        ><label class="flex items-center gap-2 text-sm"
            ><input v-model="form.is_active" type="checkbox" /> Active</label
        >
        <div class="flex gap-3 md:col-span-2">
            <Button>Save</Button
            ><Link
                href="/admin/subscriptions"
                :class="buttonVariants({ variant: 'outline' })"
                >Cancel</Link
            >
        </div>
    </form>
</template>
