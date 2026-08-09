<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import FormCheckbox from '@/components/admin/FormCheckbox.vue';
import FormField from '@/components/admin/FormField.vue';
import FormTextarea from '@/components/admin/FormTextarea.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { Skill } from '@/types';

const props = defineProps<{ skill?: Skill }>();

const form = useForm({
    name: props.skill?.name ?? '',
    category: props.skill?.category ?? '',
    icon: props.skill?.icon ?? '',
    proficiency: props.skill?.proficiency ?? '',
    description: props.skill?.description ?? '',
    sort_order: props.skill?.sort_order ?? 0,
    is_active: props.skill?.is_active ?? true,
});

function submit(): void {
    if (props.skill) {
        form.put(`/admin/skills/${props.skill.id}`);

        return;
    }

    form.post('/admin/skills');
}
</script>

<template>
    <form class="surface-panel mt-8 p-5 sm:p-8" @submit.prevent="submit">
        <div class="grid gap-6 md:grid-cols-2">
            <FormField
                id="name"
                label="Name"
                :error="form.errors.name"
                required
            >
                <Input
                    id="name"
                    v-model="form.name"
                    autocomplete="off"
                    placeholder="Laravel"
                />
            </FormField>
            <FormField
                id="category"
                label="Category"
                :error="form.errors.category"
                hint="Use natural groupings such as Engineering or Infrastructure."
            >
                <Input
                    id="category"
                    v-model="form.category"
                    placeholder="Engineering"
                />
            </FormField>
            <FormField
                id="icon"
                label="Icon"
                :error="form.errors.icon"
                hint="Optional icon name or asset reference."
            >
                <Input id="icon" v-model="form.icon" placeholder="Optional" />
            </FormField>
            <FormField
                id="proficiency"
                label="Proficiency"
                :error="form.errors.proficiency"
                hint="Optional value from 0 to 100."
            >
                <Input
                    id="proficiency"
                    v-model="form.proficiency"
                    type="number"
                    min="0"
                    max="100"
                    placeholder="80"
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
                        placeholder="Brief context about this capability."
                    />
                </FormField>
            </div>
            <div class="md:col-span-2">
                <FormCheckbox
                    id="is_active"
                    v-model="form.is_active"
                    label="Active"
                    description="Active skills can be shown on the public website."
                />
            </div>
        </div>
        <div
            class="mt-8 flex flex-wrap items-center gap-3 border-t border-border pt-6"
        >
            <Button type="submit" :disabled="form.processing">{{
                form.processing ? 'Saving…' : 'Save skill'
            }}</Button>
            <Link
                href="/admin/skills"
                :class="buttonVariants({ variant: 'outline' })"
                >Cancel</Link
            >
        </div>
    </form>
</template>
