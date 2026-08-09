<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import FormCheckbox from '@/components/admin/FormCheckbox.vue';
import FormField from '@/components/admin/FormField.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { SocialLink } from '@/types';
const props = defineProps<{ socialLink?: SocialLink }>();
const form = useForm({
    platform: props.socialLink?.platform ?? '',
    label: props.socialLink?.label ?? '',
    url: props.socialLink?.url ?? '',
    icon: props.socialLink?.icon ?? '',
    sort_order: props.socialLink?.sort_order ?? 0,
    is_active: props.socialLink?.is_active ?? true,
});
function submit(): void {
    if (props.socialLink) {
        form.put(`/admin/social-links/${props.socialLink.id}`);

        return;
    }

    form.post('/admin/social-links');
}
</script>
<template>
    <form
        class="surface-panel mt-8 max-w-4xl p-5 sm:p-8"
        @submit.prevent="submit"
    >
        <div class="grid gap-6 md:grid-cols-2">
            <FormField
                id="platform"
                label="Platform"
                :error="form.errors.platform"
                required
                ><Input
                    id="platform"
                    v-model="form.platform"
                    placeholder="GitHub" /></FormField
            ><FormField id="label" label="Label" :error="form.errors.label"
                ><Input
                    id="label"
                    v-model="form.label"
                    placeholder="Follow on GitHub"
            /></FormField>
            <div class="md:col-span-2">
                <FormField
                    id="url"
                    label="URL"
                    :error="form.errors.url"
                    required
                    ><Input
                        id="url"
                        v-model="form.url"
                        type="url"
                        placeholder="https://github.com/username"
                /></FormField>
            </div>
            <FormField
                id="icon"
                label="Icon identifier"
                :error="form.errors.icon"
                hint="A simple string such as github, linkedin, or whatsapp."
                ><Input id="icon" v-model="form.icon" /></FormField
            ><FormField
                id="sort_order"
                label="Sort order"
                :error="form.errors.sort_order"
                ><Input id="sort_order" v-model="form.sort_order" type="number"
            /></FormField>
            <div class="md:col-span-2">
                <FormCheckbox
                    id="is_active"
                    v-model="form.is_active"
                    label="Active"
                    description="Active links can be displayed on the public website."
                />
            </div>
        </div>
        <div class="mt-8 flex gap-3 border-t border-border pt-6">
            <Button type="submit" :disabled="form.processing">{{
                form.processing ? 'Saving…' : 'Save social link'
            }}</Button
            ><Link
                href="/admin/social-links"
                :class="buttonVariants({ variant: 'outline' })"
                >Cancel</Link
            >
        </div>
    </form>
</template>
