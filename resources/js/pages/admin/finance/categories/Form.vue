<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import FormCheckbox from '@/components/admin/FormCheckbox.vue';
import FormField from '@/components/admin/FormField.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { FinanceCategory } from '@/types';

const props = defineProps<{ category?: FinanceCategory }>();
const form = useForm({
    name: props.category?.name ?? '',
    type: props.category?.type ?? 'expense',
    is_active: props.category?.is_active ?? true,
});

function submit(): void {
    if (props.category) {
        form.put(`/admin/finance/categories/${props.category.id}`);

        return;
    }

    form.post('/admin/finance/categories');
}
</script>

<template>
    <form class="surface-panel mt-8 p-5 sm:p-8" @submit.prevent="submit">
        <div class="grid gap-6 md:grid-cols-2">
            <FormField
                id="name"
                label="Category name"
                :error="form.errors.name"
                required
            >
                <Input
                    id="name"
                    v-model="form.name"
                    placeholder="Example: Food"
                />
            </FormField>
            <FormField
                id="type"
                label="Category type"
                :error="form.errors.type"
                required
            >
                <select
                    id="type"
                    v-model="form.type"
                    class="h-10 w-full rounded-md border border-input bg-transparent px-3 text-sm"
                >
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select>
            </FormField>
            <div class="md:col-span-2">
                <FormCheckbox
                    id="is_active"
                    v-model="form.is_active"
                    label="Active category"
                    description="Only active categories are available for new transactions."
                />
            </div>
        </div>
        <div class="mt-8 flex gap-3 border-t border-border pt-6">
            <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Saving…' : 'Save category' }}
            </Button>
            <Link
                href="/admin/finance/categories"
                :class="buttonVariants({ variant: 'outline' })"
            >
                Cancel
            </Link>
        </div>
    </form>
</template>
