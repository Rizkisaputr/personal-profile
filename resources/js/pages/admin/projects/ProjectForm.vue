<script setup lang="ts">
import { Link, router, useForm } from '@inertiajs/vue3';
import { ImagePlus } from '@lucide/vue';
import { onBeforeUnmount, ref } from 'vue';
import ConfirmDelete from '@/components/admin/ConfirmDelete.vue';
import FileUploadField from '@/components/admin/FileUploadField.vue';
import FormCheckbox from '@/components/admin/FormCheckbox.vue';
import FormField from '@/components/admin/FormField.vue';
import FormTextarea from '@/components/admin/FormTextarea.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { Project, Technology } from '@/types';

const props = defineProps<{ project?: Project; technologies: Technology[] }>();
const dateValue = (value?: string | null): string => value?.slice(0, 10) ?? '';
const existingImages = Object.fromEntries(
    (props.project?.images ?? []).map((image) => [
        image.id,
        { caption: image.caption ?? '', sort_order: image.sort_order },
    ]),
);

const form = useForm({
    title: props.project?.title ?? '',
    slug: props.project?.slug ?? '',
    short_description: props.project?.short_description ?? '',
    description: props.project?.description ?? '',
    thumbnail: null as File | null,
    category: props.project?.category ?? '',
    project_date: dateValue(props.project?.project_date),
    github_url: props.project?.github_url ?? '',
    demo_url: props.project?.demo_url ?? '',
    client_name: props.project?.client_name ?? '',
    work_status: props.project?.work_status ?? 'done',
    is_featured: props.project?.is_featured ?? false,
    is_published: props.project?.is_published ?? true,
    sort_order: props.project?.sort_order ?? 0,
    seo_title: props.project?.seo_title ?? '',
    seo_description: props.project?.seo_description ?? '',
    technology_ids: props.project?.technology_ids ?? [],
    gallery_images: [] as File[],
    gallery_captions: [] as string[],
    gallery_sort_orders: [] as number[],
    existing_images: existingImages,
});

const thumbnailPreview = ref<string | null>(
    props.project?.thumbnail_url ?? null,
);
const galleryPreviews = ref<string[]>([]);
let thumbnailObjectUrl: string | null = null;

function selectThumbnail(file: File | null): void {
    form.thumbnail = file;

    if (thumbnailObjectUrl) {
        URL.revokeObjectURL(thumbnailObjectUrl);
    }

    thumbnailObjectUrl = file ? URL.createObjectURL(file) : null;
    thumbnailPreview.value =
        thumbnailObjectUrl ?? props.project?.thumbnail_url ?? null;
}

function selectGallery(event: Event): void {
    galleryPreviews.value.forEach(URL.revokeObjectURL);
    const files = Array.from((event.target as HTMLInputElement).files ?? []);
    form.gallery_images = files;
    form.gallery_captions = files.map(() => '');
    form.gallery_sort_orders = files.map((_, index) => index);
    galleryPreviews.value = files.map(URL.createObjectURL);
}

function toggleTechnology(id: number): void {
    form.technology_ids = form.technology_ids.includes(id)
        ? form.technology_ids.filter((technologyId) => technologyId !== id)
        : [...form.technology_ids, id];
}

function deleteImage(id: number): void {
    if (!props.project) {
        return;
    }

    router.delete(`/admin/projects/${props.project.id}/images/${id}`, {
        preserveScroll: true,
    });
}

function submit(): void {
    const options = { forceFormData: true, preserveScroll: true };

    if (props.project) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(
            `/admin/projects/${props.project.id}`,
            options,
        );

        return;
    }

    form.post('/admin/projects', options);
}

onBeforeUnmount(() => {
    if (thumbnailObjectUrl) {
        URL.revokeObjectURL(thumbnailObjectUrl);
    }

    galleryPreviews.value.forEach(URL.revokeObjectURL);
});
</script>

<template>
    <form class="mt-8 space-y-6" @submit.prevent="submit">
        <section class="surface-panel p-5 sm:p-8">
            <div class="mb-7">
                <h2 class="text-base font-semibold">Basic Information</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    The identity and main summary displayed in the project list.
                </p>
            </div>
            <div class="grid gap-6 md:grid-cols-2">
                <FormField
                    id="title"
                    label="Project title"
                    :error="form.errors.title"
                    required
                    ><Input id="title" v-model="form.title"
                /></FormField>
                <FormField
                    id="slug"
                    label="Slug"
                    :error="form.errors.slug"
                    hint="Leave blank on create to generate it from the title."
                    ><Input
                        id="slug"
                        v-model="form.slug"
                        placeholder="project-name"
                /></FormField>
                <FormField
                    id="category"
                    label="Category"
                    :error="form.errors.category"
                    ><Input id="category" v-model="form.category"
                /></FormField>
                <FormField
                    id="client_name"
                    label="Client name"
                    :error="form.errors.client_name"
                    ><Input id="client_name" v-model="form.client_name"
                /></FormField>
                <FormField
                    id="project_date"
                    label="Project date"
                    :error="form.errors.project_date"
                    ><Input
                        id="project_date"
                        v-model="form.project_date"
                        type="date"
                /></FormField>
                <FormField
                    id="sort_order"
                    label="Sort order"
                    :error="form.errors.sort_order"
                    ><Input
                        id="sort_order"
                        v-model="form.sort_order"
                        type="number"
                /></FormField>
                <FormField
                    id="work_status"
                    label="Project status"
                    :error="form.errors.work_status"
                >
                    <select
                        id="work_status"
                        v-model="form.work_status"
                        class="h-11 w-full rounded-[10px] border border-input bg-card px-3 text-sm"
                    >
                        <option value="waiting">Waiting</option>
                        <option value="running">Running</option>
                        <option value="on_hold">On Hold</option>
                        <option value="done">Done</option>
                    </select>
                </FormField>
                <div class="md:col-span-2">
                    <FormField
                        id="short_description"
                        label="Short description"
                        :error="form.errors.short_description"
                        ><FormTextarea
                            id="short_description"
                            v-model="form.short_description"
                            :rows="4"
                    /></FormField>
                </div>
            </div>
        </section>

        <section class="surface-panel p-5 sm:p-8">
            <div class="mb-7">
                <h2 class="text-base font-semibold">Studi Kasus</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Use clear headings such as Project Summary, Problem, My
                    Role, Solution, Key Features, and Results or Impact.
                </p>
            </div>
            <FormField
                id="description"
                label="Project description"
                :error="form.errors.description"
                ><FormTextarea
                    id="description"
                    v-model="form.description"
                    :rows="18"
                    placeholder="Project Overview&#10;&#10;Describe the project context…"
            /></FormField>
        </section>

        <section class="surface-panel p-5 sm:p-8">
            <div class="mb-7">
                <h2 class="text-base font-semibold">Links and Technologies</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Connect the project to its implementation and external
                    destinations.
                </p>
            </div>
            <div class="grid gap-6 md:grid-cols-2">
                <FormField
                    id="github_url"
                    label="GitHub URL"
                    :error="form.errors.github_url"
                    ><Input
                        id="github_url"
                        v-model="form.github_url"
                        type="url"
                /></FormField>
                <FormField
                    id="demo_url"
                    label="Demo URL"
                    :error="form.errors.demo_url"
                    ><Input id="demo_url" v-model="form.demo_url" type="url"
                /></FormField>
                <div class="md:col-span-2">
                    <span class="mb-3 block text-sm font-medium"
                        >Technologies</span
                    >
                    <div
                        v-if="technologies.length"
                        class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <label
                            v-for="technology in technologies"
                            :key="technology.id"
                            class="flex cursor-pointer items-center gap-3 rounded-xl border border-border bg-card px-4 py-3 text-sm"
                        >
                            <input
                                type="checkbox"
                                class="size-4 accent-[#315cff]"
                                :checked="
                                    form.technology_ids.includes(technology.id)
                                "
                                @change="toggleTechnology(technology.id)"
                            />
                            <span>{{ technology.name }}</span>
                        </label>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">
                        Add technologies first to associate them with this
                        project.
                    </p>
                    <p
                        v-if="form.errors.technology_ids"
                        class="mt-2 text-sm text-destructive"
                    >
                        {{ form.errors.technology_ids }}
                    </p>
                </div>
            </div>
        </section>

        <section class="surface-panel p-5 sm:p-8">
            <div class="mb-7">
                <h2 class="text-base font-semibold">Media</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    A strong hero image and concise gallery support the case
                    study.
                </p>
            </div>
            <div class="grid gap-8 lg:grid-cols-2">
                <div>
                    <div
                        class="mb-4 aspect-[16/10] overflow-hidden rounded-xl border border-border bg-muted"
                    >
                        <img
                            v-if="thumbnailPreview"
                            :src="thumbnailPreview"
                            alt="Hero image preview"
                            class="h-full w-full object-cover"
                        />
                        <div
                            v-else
                            class="grid h-full place-items-center text-sm text-muted-foreground"
                        >
                            No hero image yet
                        </div>
                    </div>
                    <FileUploadField
                        id="thumbnail"
                        label="Thumbnail"
                        accept="image/*"
                        hint="Image · max 4 MB"
                        :error="form.errors.thumbnail"
                        :current-name="form.thumbnail?.name"
                        @change="selectThumbnail"
                    />
                </div>
                <div>
                    <label for="gallery_images" class="text-sm font-medium"
                        >Add Gallery Images</label
                    >
                    <label
                        for="gallery_images"
                        class="mt-2 flex cursor-pointer items-center gap-3 rounded-xl border border-dashed border-input bg-background-alt p-4"
                        ><ImagePlus class="size-5 text-muted-foreground" /><span
                            ><span class="block text-sm font-medium"
                                >Select multiple images</span
                            ><span class="text-xs text-muted-foreground"
                                >Maximum 6 MB per image</span
                            ></span
                        ><input
                            id="gallery_images"
                            type="file"
                            accept="image/*"
                            multiple
                            class="sr-only"
                            @change="selectGallery"
                    /></label>
                    <p
                        v-if="form.errors.gallery_images"
                        class="mt-2 text-sm text-destructive"
                    >
                        {{ form.errors.gallery_images }}
                    </p>
                </div>
            </div>

            <div
                v-if="galleryPreviews.length"
                class="mt-8 grid gap-4 sm:grid-cols-2"
            >
                <div
                    v-for="(preview, index) in galleryPreviews"
                    :key="preview"
                    class="rounded-xl border border-border p-3"
                >
                    <img
                        :src="preview"
                        :alt="`Pratinjau galeri baru ${index + 1}`"
                        class="aspect-video w-full rounded-lg object-cover"
                    />
                    <div class="mt-3 grid gap-3 sm:grid-cols-[1fr_6rem]">
                        <Input
                            v-model="form.gallery_captions[index]"
                            :aria-label="`Caption for image ${index + 1}`"
                            placeholder="Optional caption"
                        /><Input
                            v-model="form.gallery_sort_orders[index]"
                            type="number"
                            :aria-label="`Order for image ${index + 1}`"
                        />
                    </div>
                </div>
            </div>

            <div
                v-if="project?.images?.length"
                class="mt-8 border-t border-border pt-7"
            >
                <h3 class="text-sm font-medium">Galeri Saat Ini</h3>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div
                        v-for="image in project.images"
                        :key="image.id"
                        class="rounded-xl border border-border p-3"
                    >
                        <img
                            :src="image.url"
                            :alt="image.caption || 'Project gallery image'"
                            class="aspect-video w-full rounded-lg object-cover"
                        />
                        <div class="mt-3 grid gap-3 sm:grid-cols-[1fr_6rem]">
                            <Input
                                v-model="form.existing_images[image.id].caption"
                                :aria-label="`Caption for ${image.id}`"
                            /><Input
                                v-model="
                                    form.existing_images[image.id].sort_order
                                "
                                type="number"
                                :aria-label="`Order for ${image.id}`"
                            />
                        </div>
                        <div class="mt-2 flex justify-end">
                            <ConfirmDelete
                                title="Delete gallery image?"
                                @confirm="deleteImage(image.id)"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="surface-panel p-5 sm:p-8">
            <div class="mb-7">
                <h2 class="text-base font-semibold">Publishing and SEO</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Configure priority, visibility, and search presentation.
                </p>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <FormCheckbox
                    id="is_published"
                    v-model="form.is_published"
                    label="Published"
                    description="Published projects are eligible for the public portfolio."
                /><FormCheckbox
                    id="is_featured"
                    v-model="form.is_featured"
                    label="Featured"
                    description="Highlight this project among selected work."
                />
            </div>
            <div class="mt-6 grid gap-6 md:grid-cols-2">
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

        <div
            class="sticky bottom-4 flex flex-wrap justify-end gap-3 rounded-xl border border-border bg-card/90 p-3 backdrop-blur-md"
        >
            <Link
                href="/admin/projects"
                :class="buttonVariants({ variant: 'outline' })"
                >Cancel</Link
            ><Button type="submit" :disabled="form.processing">{{
                form.processing ? 'Saving…' : 'Save project'
            }}</Button>
        </div>
    </form>
</template>
