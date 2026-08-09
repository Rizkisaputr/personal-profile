<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { onBeforeUnmount, ref } from 'vue';
import FileUploadField from '@/components/admin/FileUploadField.vue';
import FormField from '@/components/admin/FormField.vue';
import FormTextarea from '@/components/admin/FormTextarea.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
type Settings = {
    website_title: string | null;
    website_description: string | null;
    seo_title: string | null;
    seo_description: string | null;
    footer_text: string | null;
    contact_email: string | null;
    contact_whatsapp: string | null;
    logo?: string | null;
    favicon?: string | null;
    logo_url: string | null;
    favicon_url: string | null;
};
const props = defineProps<{ settings: Settings }>();
const form = useForm({
    website_title: props.settings.website_title ?? '',
    website_description: props.settings.website_description ?? '',
    seo_title: props.settings.seo_title ?? '',
    seo_description: props.settings.seo_description ?? '',
    footer_text: props.settings.footer_text ?? '',
    contact_email: props.settings.contact_email ?? '',
    contact_whatsapp: props.settings.contact_whatsapp ?? '',
    logo: null as File | null,
    favicon: null as File | null,
});
const logoPreview = ref<string | null>(props.settings.logo_url);
const faviconPreview = ref<string | null>(props.settings.favicon_url);
let logoUrl: string | null = null;
let faviconUrl: string | null = null;
function selectLogo(file: File | null): void {
    form.logo = file;

    if (logoUrl) {
        URL.revokeObjectURL(logoUrl);
    }

    logoUrl = file ? URL.createObjectURL(file) : null;
    logoPreview.value = logoUrl ?? props.settings.logo_url;
}
function selectFavicon(file: File | null): void {
    form.favicon = file;

    if (faviconUrl) {
        URL.revokeObjectURL(faviconUrl);
    }

    faviconUrl = file ? URL.createObjectURL(file) : null;
    faviconPreview.value = faviconUrl ?? props.settings.favicon_url;
}
function submit(): void {
    form.transform((data) => ({ ...data, _method: 'put' })).post(
        '/admin/settings',
        { forceFormData: true, preserveScroll: true },
    );
}
onBeforeUnmount(() => {
    if (logoUrl) {
        URL.revokeObjectURL(logoUrl);
    }

    if (faviconUrl) {
        URL.revokeObjectURL(faviconUrl);
    }
});
</script>
<template>
    <Head title="Website Settings" /><PageHeader
        eyebrow="Settings"
        title="Website settings"
        description="Manage core metadata, branding assets, contact details, and footer copy."
    />
    <form class="mt-8 space-y-6" @submit.prevent="submit">
        <section class="surface-panel p-5 sm:p-8">
            <div class="mb-7">
                <h2 class="text-base font-semibold">General</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    The primary name and description of the website.
                </p>
            </div>
            <div class="grid gap-6">
                <FormField
                    id="website_title"
                    label="Website title"
                    :error="form.errors.website_title"
                    ><Input
                        id="website_title"
                        v-model="form.website_title" /></FormField
                ><FormField
                    id="website_description"
                    label="Website description"
                    :error="form.errors.website_description"
                    ><FormTextarea
                        id="website_description"
                        v-model="form.website_description"
                        :rows="4"
                /></FormField>
            </div>
        </section>
        <section class="surface-panel p-5 sm:p-8">
            <div class="mb-7">
                <h2 class="text-base font-semibold">SEO</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Default search metadata for public pages.
                </p>
            </div>
            <div class="grid gap-6">
                <FormField
                    id="seo_title"
                    label="SEO title"
                    :error="form.errors.seo_title"
                    ><Input
                        id="seo_title"
                        v-model="form.seo_title" /></FormField
                ><FormField
                    id="seo_description"
                    label="SEO description"
                    :error="form.errors.seo_description"
                    ><FormTextarea
                        id="seo_description"
                        v-model="form.seo_description"
                        :rows="4"
                /></FormField>
            </div>
        </section>
        <section class="surface-panel p-5 sm:p-8">
            <div class="mb-7">
                <h2 class="text-base font-semibold">Branding</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Simple assets used by the site shell and browser.
                </p>
            </div>
            <div class="grid gap-8 md:grid-cols-2">
                <div>
                    <div
                        class="mb-4 grid h-28 place-items-center rounded-xl border border-border bg-muted"
                    >
                        <img
                            v-if="logoPreview"
                            :src="logoPreview"
                            alt="Current logo preview"
                            class="max-h-20 max-w-[80%] object-contain"
                        /><span v-else class="text-sm text-muted-foreground"
                            >No logo</span
                        >
                    </div>
                    <FileUploadField
                        id="logo"
                        label="Logo"
                        accept="image/*"
                        hint="Image · max 2 MB"
                        :error="form.errors.logo"
                        :current-name="form.logo?.name"
                        @change="selectLogo"
                    />
                </div>
                <div>
                    <div
                        class="mb-4 grid h-28 place-items-center rounded-xl border border-border bg-muted"
                    >
                        <img
                            v-if="faviconPreview"
                            :src="faviconPreview"
                            alt="Current favicon preview"
                            class="size-12 object-contain"
                        /><span v-else class="text-sm text-muted-foreground"
                            >No favicon</span
                        >
                    </div>
                    <FileUploadField
                        id="favicon"
                        label="Favicon"
                        accept=".ico,image/png,image/jpeg,image/webp"
                        hint="ICO, PNG, JPG, or WebP · max 1 MB"
                        :error="form.errors.favicon"
                        :current-name="form.favicon?.name"
                        @change="selectFavicon"
                    />
                </div>
            </div>
        </section>
        <section class="surface-panel p-5 sm:p-8">
            <div class="mb-7">
                <h2 class="text-base font-semibold">Contact and footer</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Default contact destinations and footer text.
                </p>
            </div>
            <div class="grid gap-6 md:grid-cols-2">
                <FormField
                    id="contact_email"
                    label="Contact email"
                    :error="form.errors.contact_email"
                    ><Input
                        id="contact_email"
                        v-model="form.contact_email"
                        type="email" /></FormField
                ><FormField
                    id="contact_whatsapp"
                    label="Contact WhatsApp"
                    :error="form.errors.contact_whatsapp"
                    ><Input
                        id="contact_whatsapp"
                        v-model="form.contact_whatsapp"
                /></FormField>
                <div class="md:col-span-2">
                    <FormField
                        id="footer_text"
                        label="Footer text"
                        :error="form.errors.footer_text"
                        ><FormTextarea
                            id="footer_text"
                            v-model="form.footer_text"
                            :rows="3"
                    /></FormField>
                </div>
            </div>
        </section>
        <div
            class="sticky bottom-4 flex justify-end rounded-xl border border-border bg-card/90 p-3 backdrop-blur-md"
        >
            <Button type="submit" :disabled="form.processing">{{
                form.processing ? 'Saving…' : 'Save settings'
            }}</Button>
        </div>
    </form>
</template>
