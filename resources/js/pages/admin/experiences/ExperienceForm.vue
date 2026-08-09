<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import FormCheckbox from '@/components/admin/FormCheckbox.vue';
import FormField from '@/components/admin/FormField.vue';
import FormTextarea from '@/components/admin/FormTextarea.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { Experience } from '@/types';

const props = defineProps<{ experience?: Experience }>();
const dateValue = (value: string | null | undefined): string =>
    value?.slice(0, 10) ?? '';

const form = useForm({
    company_name: props.experience?.company_name ?? '',
    position: props.experience?.position ?? '',
    employment_type: props.experience?.employment_type ?? '',
    location: props.experience?.location ?? '',
    start_date: dateValue(props.experience?.start_date),
    end_date: dateValue(props.experience?.end_date),
    is_current: props.experience?.is_current ?? false,
    description: props.experience?.description ?? '',
    sort_order: props.experience?.sort_order ?? 0,
    is_active: props.experience?.is_active ?? true,
});

function submit(): void {
    if (props.experience) {
        form.put(`/admin/experiences/${props.experience.id}`);

        return;
    }

    form.post('/admin/experiences');
}
</script>

<template>
    <form class="surface-panel mt-8 p-5 sm:p-8" @submit.prevent="submit">
        <div class="grid gap-6 md:grid-cols-2">
            <FormField
                id="position"
                label="Position"
                :error="form.errors.position"
                required
                ><Input
                    id="position"
                    v-model="form.position"
                    placeholder="Software Developer"
            /></FormField>
            <FormField
                id="company_name"
                label="Company"
                :error="form.errors.company_name"
                required
                ><Input
                    id="company_name"
                    v-model="form.company_name"
                    placeholder="Company name"
            /></FormField>
            <FormField
                id="employment_type"
                label="Employment type"
                :error="form.errors.employment_type"
                ><Input
                    id="employment_type"
                    v-model="form.employment_type"
                    placeholder="Full-time"
            /></FormField>
            <FormField
                id="location"
                label="Location"
                :error="form.errors.location"
                ><Input
                    id="location"
                    v-model="form.location"
                    placeholder="Indonesia · Remote"
            /></FormField>
            <FormField
                id="start_date"
                label="Start date"
                :error="form.errors.start_date"
                required
                ><Input id="start_date" v-model="form.start_date" type="date"
            /></FormField>
            <FormField
                id="end_date"
                label="End date"
                :error="form.errors.end_date"
                :hint="
                    form.is_current
                        ? 'Not required for a current position.'
                        : undefined
                "
            >
                <Input
                    id="end_date"
                    v-model="form.end_date"
                    type="date"
                    :disabled="form.is_current"
                />
            </FormField>
            <div class="md:col-span-2">
                <FormCheckbox
                    id="is_current"
                    v-model="form.is_current"
                    label="Current position"
                    description="Leave the end date empty while this role is ongoing."
                />
            </div>
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
                        :rows="7"
                        placeholder="Describe responsibilities, decisions, and meaningful outcomes."
                /></FormField>
            </div>
            <div class="md:col-span-2">
                <FormCheckbox
                    id="is_active"
                    v-model="form.is_active"
                    label="Active"
                    description="Active experiences can be displayed on the public website."
                />
            </div>
        </div>
        <div class="mt-8 flex flex-wrap gap-3 border-t border-border pt-6">
            <Button type="submit" :disabled="form.processing">{{
                form.processing ? 'Saving…' : 'Save experience'
            }}</Button>
            <Link
                href="/admin/experiences"
                :class="buttonVariants({ variant: 'outline' })"
                >Cancel</Link
            >
        </div>
    </form>
</template>
