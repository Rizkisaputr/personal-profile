<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { buttonVariants } from '@/components/ui/button';
import { formatIDR } from '@/lib/money';
type A = {
    id: number;
    name: string;
    category: string;
    purchase_price: string | null;
    purchase_date: string | null;
    warranty_expiry: string | null;
    serial_number: string | null;
    status: string;
};
defineProps<{ assets: A[] }>();
function removeAsset(id: number): void {
    if (window.confirm('Delete this asset?')) {
router.delete(`/admin/assets/${id}`);
}
}
</script>
<template>
    <Head title="Assets" /><PageHeader
        eyebrow="Personal"
        title="Assets"
        description="A compact inventory of personal property and equipment."
        ><template #actions
            ><Link href="/admin/assets/create" :class="buttonVariants()"
                ><Plus class="size-4" />Add asset</Link
            ></template
        ></PageHeader
    >
    <div class="surface-panel mt-8 overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b text-xs text-muted-foreground">
                <tr>
                    <th class="p-4">Asset</th>
                    <th class="p-4">Category</th>
                    <th class="p-4">Price</th>
                    <th class="p-4">Warranty</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="item in assets"
                    :key="item.id"
                    class="border-b last:border-0"
                >
                    <td class="p-4 font-medium">{{ item.name }}</td>
                    <td class="p-4">{{ item.category }}</td>
                    <td class="p-4">
                        {{
                            item.purchase_price
                                ? formatIDR(item.purchase_price)
                                : '—'
                        }}
                    </td>
                    <td class="p-4">
                        {{
                            item.warranty_expiry
                                ? new Date(
                                      item.warranty_expiry,
                                  ).toLocaleDateString('id-ID')
                                : '—'
                        }}
                    </td>
                    <td class="p-4 capitalize">{{ item.status }}</td>
                    <td class="p-4">
                        <div class="flex justify-end gap-3">
                            <Link
                                :href="`/admin/assets/${item.id}/edit`"
                                :class="
                                    buttonVariants({
                                        variant: 'outline',
                                        size: 'sm',
                                    })
                                "
                                >Edit</Link
                            ><button
                                class="text-xs text-destructive"
                            @click="removeAsset(item.id)"
                            >
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
                <tr v-if="!assets.length">
                    <td
                        colspan="6"
                        class="p-10 text-center text-muted-foreground"
                    >
                        No assets yet.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
