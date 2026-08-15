<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDelete from '@/components/admin/ConfirmDelete.vue';
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
const deleting = ref<number | null>(null);

function remove(debt: D): void {
    deleting.value = debt.id;
    router.delete(`/admin/finance/debts/${debt.id}`, {
        preserveScroll: true,
        onFinish: () => (deleting.value = null),
    });
}
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
                                  ).toLocaleDateString('en-US')
                                : '—'
                        }}
                    </td>
                    <td>{{ d.progress }}%</td>
                    <td class="p-4">
                        <div class="flex justify-end gap-1">
                            <Link
                                :href="`/admin/finance/debts/${d.id}`"
                                :class="
                                    buttonVariants({
                                        variant: 'ghost',
                                        size: 'sm',
                                    })
                                "
                                >Details</Link
                            >
                            <ConfirmDelete
                                title="Delete debt or receivable?"
                                description="This record, its schedules, payments, and related finance transactions will be permanently deleted."
                                :processing="deleting === d.id"
                                @confirm="remove(d)"
                            />
                        </div>
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
