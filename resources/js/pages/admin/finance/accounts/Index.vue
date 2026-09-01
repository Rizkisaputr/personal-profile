<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import ConfirmDelete from '@/components/admin/ConfirmDelete.vue';
import EmptyState from '@/components/admin/EmptyState.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import { buttonVariants } from '@/components/ui/button';
import { formatIDR } from '@/lib/money';
import type { FinanceAccount } from '@/types';
defineProps<{ accounts: FinanceAccount[] }>();
const deleting = ref<number | null>(null);
function remove(account: FinanceAccount): void {
    deleting.value = account.id;
    router.delete(`/admin/finance/accounts/${account.id}`, {
        preserveScroll: true,
        onFinish: () => (deleting.value = null),
    });
}
const typeLabel = (type: FinanceAccount['type']) =>
    ({ bank: 'Bank', cash: 'Cash', e_wallet: 'E-wallet', other: 'Other' })[
        type
    ];
</script>
<template>
    <Head title="Finance accounts" /><PageHeader
        eyebrow="Personal · Finance"
        title="Accounts"
        description="Manage the accounts that make up your private finance ledger."
        ><template #actions
            ><Link
                href="/admin/finance/accounts/create"
                :class="buttonVariants()"
                ><Plus class="size-4" />Add account</Link
            ></template
        ></PageHeader
    >
    <EmptyState
        v-if="!accounts.length"
        class="mt-8"
        title="No finance accounts"
        description="Add an account before recording transactions."
        ><Link href="/admin/finance/accounts/create" :class="buttonVariants()"
            >Add account</Link
        ></EmptyState
    >
    <div v-else class="surface-panel mt-8 overflow-x-auto">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead
                class="border-b border-border bg-muted/40 text-xs text-muted-foreground"
            >
                <tr>
                    <th class="px-5 py-3 font-medium">Account</th>
                    <th class="px-5 py-3 font-medium">Type</th>
                    <th class="px-5 py-3 text-right font-medium">
                        Current balance
                    </th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="account in accounts"
                    :key="account.id"
                    class="border-b border-border last:border-0"
                >
                    <td class="px-5 py-4">
                        <p class="font-medium">{{ account.name }}</p>
                        <p class="text-xs text-muted-foreground">
                            {{ account.currency }}
                        </p>
                    </td>
                    <td class="px-5 py-4 text-muted-foreground">
                        {{ typeLabel(account.type) }}
                    </td>
                    <td class="px-5 py-4 text-right font-semibold tabular-nums">
                        {{ formatIDR(account.current_balance || '0') }}
                    </td>
                    <td class="px-5 py-4">
                        <StatusBadge :active="account.is_active" />
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex justify-end gap-1">
                            <Link
                                :href="`/admin/finance/accounts/${account.id}/adjust`"
                                :class="
                                    buttonVariants({
                                        variant: 'ghost',
                                        size: 'sm',
                                    })
                                "
                                >Adjust</Link
                            ><Link
                                :href="`/admin/finance/accounts/${account.id}/edit`"
                                :class="
                                    buttonVariants({
                                        variant: 'ghost',
                                        size: 'sm',
                                    })
                                "
                                >Edit</Link
                            ><ConfirmDelete
                                :title="
                                    account.has_transactions
                                        ? 'Deactivate account?'
                                        : 'Delete account?'
                                "
                                :description="
                                    account.has_transactions
                                        ? 'Transaction history will be preserved.'
                                        : 'This account has no transaction history.'
                                "
                                :processing="deleting === account.id"
                                @confirm="remove(account)"
                            />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
