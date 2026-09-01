<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import FormCheckbox from '@/components/admin/FormCheckbox.vue';
import FormField from '@/components/admin/FormField.vue';
import FormTextarea from '@/components/admin/FormTextarea.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { Training } from '@/types';

const props = defineProps<{ training?: Training }>();
const form = useForm({
    institution: props.training?.institution ?? '',
    program_name: props.training?.program_name ?? '',
    field_of_study: props.training?.field_of_study ?? '',
    location: props.training?.location ?? '',
    start_date: props.training?.start_date ?? '',
    end_date: props.training?.end_date ?? '',
    credential_url: props.training?.credential_url ?? '',
    description: props.training?.description ?? '',
    sort_order: props.training?.sort_order ?? 0,
    is_active: props.training?.is_active ?? true,
});

function submit(): void {
    if (props.training) {
        form.put(`/admin/trainings/${props.training.id}`);

        return;
    }

    form.post('/admin/trainings');
}
</script>

<template>
    <form class="surface-panel mt-8 p-5 sm:p-8" @submit.prevent="submit">
        <div class="grid gap-6 md:grid-cols-2">
            <FormField
                id="institution"
                label="Institution"
                :error="form.errors.institution"
                required
            >
                <Input
                    id="institution"
                    v-model="form.institution"
                    placeholder="Training provider or institution"
                />
            </FormField>
            <FormField
                id="program_name"
                label="Program name"
                :error="form.errors.program_name"
                required
            >
                <Input
                    id="program_name"
                    v-model="form.program_name"
                    placeholder="Program, course, or workshop"
                />
            </FormField>
            <FormField
                id="field_of_study"
                label="Field of study"
                :error="form.errors.field_of_study"
            >
                <Input
                    id="field_of_study"
                    v-model="form.field_of_study"
                    placeholder="Subject or specialization"
                />
            </FormField>
            <FormField
                id="location"
                label="Location"
                :error="form.errors.location"
            >
                <Input
                    id="location"
                    v-model="form.location"
                    placeholder="City, country, or online"
                />
            </FormField>
            <FormField
                id="start_date"
                label="Start date"
                :error="form.errors.start_date"
            >
                <Input id="start_date" v-model="form.start_date" type="date" />
            </FormField>
            <FormField
                id="end_date"
                label="End date"
                :error="form.errors.end_date"
            >
                <Input id="end_date" v-model="form.end_date" type="date" />
            </FormField>
            <FormField
                id="credential_url"
                label="Credential URL"
                :error="form.errors.credential_url"
            >
                <Input
                    id="credential_url"
                    v-model="form.credential_url"
                    type="url"
                    placeholder="https://"
                />
            </FormField>
            <FormField
                id="sort_order"
                label="Sort order"
                :error="form.errors.sort_order"
            >
                <Input
                    id="sort_order"
                    v-model="form.sort_order"
                    type="number"
                />
            </FormField>
            <div class="md:col-span-2">
                <FormField
                    id="description"
                    label="Description"
                    :error="form.errors.description"
                >
                    <FormTextarea
                        id="description"
                        v-model="form.description"
                        :rows="5"
                        placeholder="A concise summary of the learning experience."
                    />
                </FormField>
            </div>
            <div class="md:col-span-2">
                <FormCheckbox
                    id="is_active"
                    v-model="form.is_active"
                    label="Active"
                    description="Active training entries can appear on the public website."
                />
            </div>
        </div>
        <div class="mt-8 flex flex-wrap gap-3 border-t border-border pt-6">
            <Button type="submit" :disabled="form.processing">{{
                form.processing ? 'Saving…' : 'Save training'
            }}</Button>
            <Link
                href="/admin/trainings"
                :class="buttonVariants({ variant: 'outline' })"
                >Cancel</Link
            >
        </div>
    </form>
</template>
