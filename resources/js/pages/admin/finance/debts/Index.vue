<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import { buttonVariants } from '@/components/ui/button';
import { formatIDR } from '@/lib/money';
type D = {
    id: number;
    name: string;
    type: string;
    party_name: string | null;
    original_amount: string;
    paid_amount: string;
    remaining_amount: string;
    progress: number;
    status: string;
    next_due: { due_date: string; amount: string } | null;
};
defineProps<{ debts: D[] }>();
</script>
<template>
    <Head title="Debts & Receivables" /><PageHeader
        eyebrow="Personal · Finance"
        title="Debts & Receivables"
        description="Track obligations and money owed to you."
        ><template #actions
            ><Link href="/admin/finance/debts/create" :class="buttonVariants()"
                >Add record</Link
            ></template
        ></PageHeader
    >
    <div class="surface-panel mt-8 overflow-x-auto">
        <table class="w-full min-w-[900px] text-sm">
            <thead>
                <tr class="border-b text-left text-muted-foreground">
                    <th class="p-4">Name</th>
                    <th>Type / Party</th>
                    <th>Original</th>
                    <th>Paid</th>
                    <th>Remaining</th>
                    <th>Next due</th>
                    <th>Progress</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="d in debts"
                    :key="d.id"
                    class="border-b last:border-0"
                >
                    <td class="p-4 font-medium">{{ d.name }}</td>
                    <td class="capitalize">
                        {{ d.type
                        }}<small class="block text-muted-foreground">{{
                            d.party_name || '—'
                        }}</small>
                    </td>
                    <td>{{ formatIDR(d.original_amount) }}</td>
                    <td>{{ formatIDR(d.paid_amount) }}</td>
                    <td class="font-semibold">
                        {{ formatIDR(d.remaining_amount) }}
                    </td>
                    <td>
                        {{
                            d.next_due
                                ? new Date(
                                      d.next_due.due_date,
                                  ).toLocaleDateString('id-ID')
                                : '—'
                        }}
                    </td>
                    <td>{{ d.progress }}%</td>
                    <td>
                        <Link
                            :href="`/admin/finance/debts/${d.id}`"
                            class="text-primary"
                            >Details</Link
                        >
                    </td>
                </tr>
                <tr v-if="!debts.length">
                    <td
                        colspan="8"
                        class="p-10 text-center text-muted-foreground"
                    >
                        No records yet.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
