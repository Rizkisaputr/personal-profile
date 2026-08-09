<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import CurrencyInput from '@/components/admin/CurrencyInput.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatIDR } from '@/lib/money';
type Account = { id: number; name: string };
type Schedule = {
    id: number;
    due_date: string;
    amount: string;
    status: string;
    notes: string | null;
    is_overdue?: boolean;
};
type Payment = {
    id: number;
    payment_date: string;
    amount: string;
    notes: string | null;
};
type Debt = {
    id: number;
    name: string;
    type: string;
    original_amount: string;
    paid_amount: string;
    remaining_amount: string;
    progress: number;
    schedules: Schedule[];
    payments: Payment[];
};
defineProps<{ debt: Debt; accounts: Account[] }>();
const schedule = useForm({ due_date: '', amount: '', notes: '' });
const pay = useForm({
    amount: '',
    payment_date: new Date().toISOString().slice(0, 10),
    account_id: '',
    schedule_id: '',
    notes: '',
});
</script>
<template>
    <Head :title="debt.name" /><PageHeader
        eyebrow="Finance"
        :title="debt.name"
        :description="
            debt.type === 'debt' ? 'Debt details' : 'Receivable details'
        "
        ><template #actions
            ><Link
                :href="`/admin/finance/debts/${debt.id}/edit`"
                :class="buttonVariants({ variant: 'outline' })"
                >Edit</Link
            ></template
        ></PageHeader
    >
    <div class="mt-8 grid gap-4 sm:grid-cols-4">
        <div
            v-for="x in [
                ['Original', debt.original_amount],
                ['Paid', debt.paid_amount],
                ['Remaining', debt.remaining_amount],
            ]"
            :key="x[0]"
            class="surface-panel p-4"
        >
            <p class="text-xs text-muted-foreground">{{ x[0] }}</p>
            <p class="mt-2 font-semibold">{{ formatIDR(x[1]) }}</p>
        </div>
        <div class="surface-panel p-4">
            <p class="text-xs text-muted-foreground">Progress</p>
            <p class="mt-2 font-semibold">{{ debt.progress }}%</p>
        </div>
    </div>
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <form
            class="surface-panel p-5"
            @submit.prevent="
                schedule.post(`/admin/finance/debts/${debt.id}/schedules`, {
                    onSuccess: () => schedule.reset(),
                })
            "
        >
            <h2 class="font-semibold">Add schedule</h2>
            <div class="mt-4 grid gap-3">
                <Input v-model="schedule.due_date" type="date" /><CurrencyInput
                    v-model="schedule.amount"
                    placeholder="Contoh: 500.000"
                /><Input v-model="schedule.notes" placeholder="Notes" /><Button
                    >Add schedule</Button
                >
            </div>
        </form>
        <form
            class="surface-panel p-5"
            @submit.prevent="
                pay.post(`/admin/finance/debts/${debt.id}/payments`, {
                    onSuccess: () => pay.reset('amount', 'notes'),
                })
            "
        >
            <h2 class="font-semibold">
                {{ debt.type === 'debt' ? 'Pay Debt' : 'Receive Payment' }}
            </h2>
            <div class="mt-4 grid gap-3">
                <CurrencyInput
                    v-model="pay.amount"
                    placeholder="Contoh: 500.000"
                /><Input v-model="pay.payment_date" type="date" /><select
                    v-model="pay.account_id"
                    class="h-10 rounded-md border px-3"
                >
                    <option value="">Select account</option>
                    <option v-for="a in accounts" :key="a.id" :value="a.id">
                        {{ a.name }}
                    </option></select
                ><select
                    v-model="pay.schedule_id"
                    class="h-10 rounded-md border px-3"
                >
                    <option value="">No schedule</option>
                    <option
                        v-for="s in debt.schedules.filter(
                            (x) => x.status === 'upcoming',
                        )"
                        :key="s.id"
                        :value="s.id"
                    >
                        {{ new Date(s.due_date).toLocaleDateString('id-ID') }} ·
                        {{ formatIDR(s.amount) }}
                    </option></select
                ><Input v-model="pay.notes" placeholder="Notes" /><Button
                    >Save payment</Button
                >
            </div>
        </form>
    </div>
    <div class="surface-panel mt-6 p-5">
        <h2 class="font-semibold">Schedules</h2>
        <div
            v-for="s in debt.schedules"
            :key="s.id"
            class="flex justify-between border-b py-3 text-sm"
        >
            <span
                >{{ new Date(s.due_date).toLocaleDateString('id-ID') }}
                <b v-if="s.is_overdue" class="text-red-600">Overdue</b></span
            ><span>{{ formatIDR(s.amount) }} · {{ s.status }}</span>
        </div>
        <h2 class="mt-6 font-semibold">Payment history</h2>
        <div
            v-for="pmt in debt.payments"
            :key="pmt.id"
            class="flex justify-between border-b py-3 text-sm"
        >
            <span>{{
                new Date(pmt.payment_date).toLocaleDateString('id-ID')
            }}</span
            ><span>{{ formatIDR(pmt.amount) }}</span>
        </div>
    </div>
</template>
