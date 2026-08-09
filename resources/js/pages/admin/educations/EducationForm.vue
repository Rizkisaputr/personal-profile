<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import FormCheckbox from '@/components/admin/FormCheckbox.vue';
import FormField from '@/components/admin/FormField.vue';
import FormTextarea from '@/components/admin/FormTextarea.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { Education } from '@/types';

const props = defineProps<{ education?: Education }>();
const form = useForm({
    institution: props.education?.institution ?? '',
    degree: props.education?.degree ?? '',
    field_of_study: props.education?.field_of_study ?? '',
    location: props.education?.location ?? '',
    start_year: props.education?.start_year ?? '',
    end_year: props.education?.end_year ?? '',
    description: props.education?.description ?? '',
    sort_order: props.education?.sort_order ?? 0,
    is_active: props.education?.is_active ?? true,
});

function submit(): void {
    if (props.education) {
        form.put(`/admin/educations/${props.education.id}`);

        return;
    }

    form.post('/admin/educations');
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
                ><Input
                    id="institution"
                    v-model="form.institution"
                    placeholder="University or institution"
            /></FormField>
            <FormField id="degree" label="Degree" :error="form.errors.degree"
                ><Input
                    id="degree"
                    v-model="form.degree"
                    placeholder="Bachelor Degree"
            /></FormField>
            <FormField
                id="field_of_study"
                label="Field of study"
                :error="form.errors.field_of_study"
                ><Input
                    id="field_of_study"
                    v-model="form.field_of_study"
                    placeholder="Information Technology"
            /></FormField>
            <FormField
                id="location"
                label="Location"
                :error="form.errors.location"
                ><Input
                    id="location"
                    v-model="form.location"
                    placeholder="Indonesia"
            /></FormField>
            <FormField
                id="start_year"
                label="Start year"
                :error="form.errors.start_year"
                ><Input
                    id="start_year"
                    v-model="form.start_year"
                    type="number"
                    min="1900"
                    max="2100"
                    placeholder="2018"
            /></FormField>
            <FormField
                id="end_year"
                label="End year"
                :error="form.errors.end_year"
                ><Input
                    id="end_year"
                    v-model="form.end_year"
                    type="number"
                    min="1900"
                    max="2100"
                    placeholder="2022"
            /></FormField>
            <FormField
                id="sort_order"
                label="Sort order"
                :error="form.errors.sort_order"
                ><Input id="sort_order" v-model="form.sort_order" type="number"
            /></FormField>
            <div class="md:col-span-2">
                <FormField
                    id="description"
                    label="Description"
                    :error="form.errors.description"
                    ><FormTextarea
                        id="description"
                        v-model="form.description"
                        :rows="6"
                        placeholder="Optional context about the program or focus."
                /></FormField>
            </div>
            <div class="md:col-span-2">
                <FormCheckbox
                    id="is_active"
                    v-model="form.is_active"
                    label="Active"
                    description="Active education entries can appear on the public website."
                />
            </div>
        </div>
        <div class="mt-8 flex flex-wrap gap-3 border-t border-border pt-6">
            <Button type="submit" :disabled="form.processing">{{
                form.processing ? 'Saving…' : 'Save education'
            }}</Button>
            <Link
                href="/admin/educations"
                :class="buttonVariants({ variant: 'outline' })"
                >Cancel</Link
            >
        </div>
    </form>
</template>
