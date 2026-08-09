<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { onBeforeUnmount, ref } from 'vue';
import FileUploadField from '@/components/admin/FileUploadField.vue';
import FormCheckbox from '@/components/admin/FormCheckbox.vue';
import FormField from '@/components/admin/FormField.vue';
import FormTextarea from '@/components/admin/FormTextarea.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { Certificate } from '@/types';
const props = defineProps<{ certificate?: Certificate }>();
const dateValue = (value?: string | null): string => value?.slice(0, 10) ?? '';
const form = useForm({
    title: props.certificate?.title ?? '',
    issuer: props.certificate?.issuer ?? '',
    issue_date: dateValue(props.certificate?.issue_date),
    credential_id: props.certificate?.credential_id ?? '',
    credential_url: props.certificate?.credential_url ?? '',
    image: null as File | null,
    description: props.certificate?.description ?? '',
    sort_order: props.certificate?.sort_order ?? 0,
    is_active: props.certificate?.is_active ?? true,
});
const preview = ref<string | null>(props.certificate?.image_url ?? null);
let objectUrl: string | null = null;
function selectImage(file: File | null): void {
    form.image = file;

    if (objectUrl) {
        URL.revokeObjectURL(objectUrl);
    }

    objectUrl = file ? URL.createObjectURL(file) : null;
    preview.value = objectUrl ?? props.certificate?.image_url ?? null;
}
function submit(): void {
    const options = { forceFormData: true };

    if (props.certificate) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(
            `/admin/certificates/${props.certificate.id}`,
            options,
        );

        return;
    }

    form.post('/admin/certificates', options);
}
onBeforeUnmount(() => {
    if (objectUrl) {
        URL.revokeObjectURL(objectUrl);
    }
});
</script>
<template>
    <form class="surface-panel mt-8 p-5 sm:p-8" @submit.prevent="submit">
        <div class="grid gap-8 lg:grid-cols-[13rem_1fr]">
            <div>
                <div
                    class="aspect-[4/3] overflow-hidden rounded-xl border border-border bg-muted"
                >
                    <img
                        v-if="preview"
                        :src="preview"
                        alt="Certificate preview"
                        class="h-full w-full object-cover"
                    />
                    <div
                        v-else
                        class="grid h-full place-items-center text-sm text-muted-foreground"
                    >
                        No image
                    </div>
                </div>
                <FileUploadField
                    id="image"
                    class="mt-4"
                    label="Certificate image"
                    accept="image/*"
                    hint="Image · max 4 MB"
                    :error="form.errors.image"
                    :current-name="form.image?.name"
                    @change="selectImage"
                />
            </div>
            <div class="grid gap-6 md:grid-cols-2">
                <FormField
                    id="title"
                    label="Title"
                    :error="form.errors.title"
                    required
                    ><Input id="title" v-model="form.title" /></FormField
                ><FormField
                    id="issuer"
                    label="Issuer"
                    :error="form.errors.issuer"
                    ><Input id="issuer" v-model="form.issuer" /></FormField
                ><FormField
                    id="issue_date"
                    label="Issue date"
                    :error="form.errors.issue_date"
                    ><Input
                        id="issue_date"
                        v-model="form.issue_date"
                        type="date" /></FormField
                ><FormField
                    id="credential_id"
                    label="Credential ID"
                    :error="form.errors.credential_id"
                    ><Input id="credential_id" v-model="form.credential_id"
                /></FormField>
                <div class="md:col-span-2">
                    <FormField
                        id="credential_url"
                        label="Credential URL"
                        :error="form.errors.credential_url"
                        ><Input
                            id="credential_url"
                            v-model="form.credential_url"
                            type="url"
                    /></FormField>
                </div>
                <FormField
                    id="sort_order"
                    label="Sort order"
                    :error="form.errors.sort_order"
                    ><Input
                        id="sort_order"
                        v-model="form.sort_order"
                        type="number"
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
                    /></FormField>
                </div>
                <div class="md:col-span-2">
                    <FormCheckbox
                        id="is_active"
                        v-model="form.is_active"
                        label="Active"
                        description="Active certificates can appear on the public website."
                    />
                </div>
            </div>
        </div>
        <div class="mt-8 flex gap-3 border-t border-border pt-6">
            <Button type="submit" :disabled="form.processing">{{
                form.processing ? 'Saving…' : 'Save certificate'
            }}</Button
            ><Link
                href="/admin/certificates"
                :class="buttonVariants({ variant: 'outline' })"
                >Cancel</Link
            >
        </div>
    </form>
</template>
