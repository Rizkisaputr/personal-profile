<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { buttonVariants } from '@/components/ui/button';
import { formatIDR } from '@/lib/money';
type S = {
    id: number;
    service_name: string;
    cost: string;
    billing_cycle: string;
    next_renewal_date: string;
    is_active: boolean;
    payment_account: { name: string } | null;
};
defineProps<{ subscriptions: S[] }>();
</script>
<template>
    <Head title="Subscriptions" /><PageHeader
        eyebrow="Personal"
        title="Subscriptions"
        description="Recurring services with finance-ledger payment history."
        ><template #actions
            ><Link href="/admin/subscriptions/create" :class="buttonVariants()"
                ><Plus class="size-4" />Add subscription</Link
            ></template
        ></PageHeader
    >
    <div class="surface-panel mt-8 overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b text-xs text-muted-foreground">
                <tr>
                    <th class="p-4">Service</th>
                    <th class="p-4">Cost</th>
                    <th class="p-4">Cycle</th>
                    <th class="p-4">Renewal</th>
                    <th class="p-4">Account</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="item in subscriptions"
                    :key="item.id"
                    class="border-b last:border-0"
                >
                    <td class="p-4 font-medium">{{ item.service_name }}</td>
                    <td class="p-4">{{ formatIDR(item.cost) }}</td>
                    <td class="p-4 capitalize">{{ item.billing_cycle }}</td>
                    <td class="p-4">
                        {{
                            new Date(item.next_renewal_date).toLocaleDateString(
                                'id-ID',
                            )
                        }}
                    </td>
                    <td class="p-4">{{ item.payment_account?.name || '—' }}</td>
                    <td class="p-4 text-right">
                        <Link
                            :href="`/admin/subscriptions/${item.id}`"
                            :class="
                                buttonVariants({
                                    variant: 'outline',
                                    size: 'sm',
                                })
                            "
                            >Manage</Link
                        >
                    </td>
                </tr>
                <tr v-if="!subscriptions.length">
                    <td
                        colspan="6"
                        class="p-10 text-center text-muted-foreground"
                    >
                        No subscriptions yet.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
