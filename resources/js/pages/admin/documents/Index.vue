<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Download, Eye, Plus } from '@lucide/vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { buttonVariants } from '@/components/ui/button';
type Document = {
    id: number;
    name: string;
    category: string;
    masked_document_number: string | null;
    expiry_date: string | null;
    is_active: boolean;
    has_file: boolean;
};
defineProps<{ documents: Document[] }>();
function removeDocument(id: number): void {
    if (window.confirm('Delete this document?')) {
        router.delete(`/admin/documents/${id}`);
    }
}
</script>
<template>
    <Head title="Documents" /><PageHeader
        eyebrow="Personal"
        title="Documents"
        description="Private files available only to authenticated administrators."
        ><template #actions
            ><Link href="/admin/documents/create" :class="buttonVariants()"
                ><Plus class="size-4" />Add document</Link
            ></template
        ></PageHeader
    >
    <div class="surface-panel mt-8 overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b text-xs text-muted-foreground">
                <tr>
                    <th class="p-4">Document</th>
                    <th class="p-4">Category</th>
                    <th class="p-4">Number</th>
                    <th class="p-4">Expiry</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="item in documents"
                    :key="item.id"
                    class="border-b last:border-0"
                >
                    <td class="p-4 font-medium">{{ item.name }}</td>
                    <td class="p-4">{{ item.category }}</td>
                    <td class="p-4 text-muted-foreground">
                        {{ item.masked_document_number || '—' }}
                    </td>
                    <td class="p-4">
                        {{
                            item.expiry_date
                                ? new Date(item.expiry_date).toLocaleDateString(
                                      'en-US',
                                  )
                                : '—'
                        }}
                    </td>
                    <td class="p-4">
                        {{ item.is_active ? 'Active' : 'Inactive' }}
                    </td>
                    <td class="p-4">
                        <div class="flex justify-end gap-2">
                            <a
                                v-if="item.has_file"
                                :href="`/admin/documents/${item.id}/view`"
                                target="_blank"
                                :class="
                                    buttonVariants({
                                        variant: 'outline',
                                        size: 'sm',
                                    })
                                "
                                aria-label="View document"
                                ><Eye class="size-4" /></a
                            ><a
                                v-if="item.has_file"
                                :href="`/admin/documents/${item.id}/download`"
                                :class="
                                    buttonVariants({
                                        variant: 'outline',
                                        size: 'sm',
                                    })
                                "
                                aria-label="Download document"
                                ><Download class="size-4" /></a
                            ><Link
                                :href="`/admin/documents/${item.id}/edit`"
                                :class="
                                    buttonVariants({
                                        variant: 'outline',
                                        size: 'sm',
                                    })
                                "
                                >Edit</Link
                            ><button
                                class="text-xs text-destructive"
                                @click="removeDocument(item.id)"
                            >
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
                <tr v-if="!documents.length">
                    <td
                        colspan="6"
                        class="p-10 text-center text-muted-foreground"
                    >
                        No documents yet.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
