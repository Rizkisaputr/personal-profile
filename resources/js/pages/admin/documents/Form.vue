<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
type Document = {
    id: number;
    name: string;
    category: string;
    document_number: string | null;
    issue_date: string | null;
    expiry_date: string | null;
    notes: string | null;
    is_active: boolean;
    has_file: boolean;
    original_filename: string | null;
};
const props = defineProps<{ document?: Document; categories: string[] }>();
const form = useForm({
    name: props.document?.name ?? '',
    category: props.document?.category ?? 'Other',
    document_number: props.document?.document_number ?? '',
    issue_date: props.document?.issue_date?.slice(0, 10) ?? '',
    expiry_date: props.document?.expiry_date?.slice(0, 10) ?? '',
    file: null as File | null,
    notes: props.document?.notes ?? '',
    is_active: props.document?.is_active ?? true,
    _method: props.document ? 'put' : 'post',
});
function submit() {
    form.post(
        props.document
            ? `/admin/documents/${props.document.id}`
            : '/admin/documents',
        { forceFormData: true },
    );
}
</script>
<template>
    <Head title="Document" /><PageHeader
        eyebrow="Personal"
        :title="document ? 'Edit document' : 'Add document'"
        description="Private document metadata and file storage."
    />
    <form
        class="surface-panel mt-8 grid gap-5 p-6 md:grid-cols-2"
        @submit.prevent="submit"
    >
        <label class="grid gap-2 text-sm"
            >Name<Input v-model="form.name" /><span
                class="text-xs text-destructive"
                >{{ form.errors.name }}</span
            ></label
        ><label class="grid gap-2 text-sm"
            >Category<select
                v-model="form.category"
                class="h-10 rounded-md border bg-background px-3"
            >
                <option v-for="category in categories" :key="category">
                    {{ category }}
                </option>
            </select></label
        ><label class="grid gap-2 text-sm"
            >Document number<Input v-model="form.document_number" /></label
        ><label class="grid gap-2 text-sm"
            >Private file<input
                type="file"
                accept=".pdf,.jpg,.jpeg,.png,.webp"
                class="text-sm"
                @change="
                    form.file =
                        ($event.target as HTMLInputElement).files?.[0] ?? null
                "
            /><span
                v-if="document?.has_file"
                class="text-xs text-muted-foreground"
                >Current: {{ document.original_filename }}</span
            ><span class="text-xs text-destructive">{{
                form.errors.file
            }}</span></label
        ><label class="grid gap-2 text-sm"
            >Issue date<Input v-model="form.issue_date" type="date" /></label
        ><label class="grid gap-2 text-sm"
            >Expiry date<Input v-model="form.expiry_date" type="date" /></label
        ><label class="grid gap-2 text-sm md:col-span-2"
            >Notes<textarea
                v-model="form.notes"
                class="min-h-24 rounded-md border bg-background p-3"
            /></label
        ><label class="flex items-center gap-2 text-sm"
            ><input v-model="form.is_active" type="checkbox" /> Active</label
        >
        <div class="flex gap-3 md:col-span-2">
            <Button :disabled="form.processing">Save</Button
            ><Link
                href="/admin/documents"
                :class="buttonVariants({ variant: 'outline' })"
                >Cancel</Link
            >
        </div>
    </form>
</template>
