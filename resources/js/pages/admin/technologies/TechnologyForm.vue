<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import FormField from '@/components/admin/FormField.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { Technology } from '@/types';
const props = defineProps<{ technology?: Technology }>();
const form = useForm({
    name: props.technology?.name ?? '',
    slug: props.technology?.slug ?? '',
    icon: props.technology?.icon ?? '',
});
function submit(): void {
    if (props.technology) {
        form.put(`/admin/technologies/${props.technology.id}`);

        return;
    }

    form.post('/admin/technologies');
}
</script>
<template>
    <form
        class="surface-panel mt-8 max-w-3xl p-5 sm:p-8"
        @submit.prevent="submit"
    >
        <div class="grid gap-6 sm:grid-cols-2">
            <FormField id="name" label="Name" :error="form.errors.name" required
                ><Input
                    id="name"
                    v-model="form.name"
                    placeholder="Laravel" /></FormField
            ><FormField
                id="slug"
                label="Slug"
                :error="form.errors.slug"
                hint="Leave blank on create to generate automatically."
                ><Input id="slug" v-model="form.slug" placeholder="laravel"
            /></FormField>
            <div class="sm:col-span-2">
                <FormField
                    id="icon"
                    label="Icon identifier"
                    :error="form.errors.icon"
                    hint="For example: laravel, vue, mysql, docker, or cloudflare."
                    ><Input id="icon" v-model="form.icon" placeholder="laravel"
                /></FormField>
            </div>
        </div>
        <div class="mt-8 flex gap-3 border-t border-border pt-6">
            <Button type="submit" :disabled="form.processing">{{
                form.processing ? 'Saving…' : 'Save technology'
            }}</Button
            ><Link
                href="/admin/technologies"
                :class="buttonVariants({ variant: 'outline' })"
                >Cancel</Link
            >
        </div>
    </form>
</template>
