<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { onBeforeUnmount, ref } from 'vue';
import FileUploadField from '@/components/admin/FileUploadField.vue';
import FormField from '@/components/admin/FormField.vue';
import FormTextarea from '@/components/admin/FormTextarea.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { Profile } from '@/types';

const props = defineProps<{ profile: Profile }>();
const dateValue = (value: string | null): string => value?.slice(0, 10) ?? '';

const form = useForm({
    full_name: props.profile.full_name,
    professional_title: props.profile.professional_title ?? '',
    tagline: props.profile.tagline ?? '',
    short_bio: props.profile.short_bio ?? '',
    about: props.profile.about ?? '',
    profile_image: null as File | null,
    hero_image: null as File | null,
    login_image: null as File | null,
    location: props.profile.location ?? '',
    email: props.profile.email ?? '',
    phone: props.profile.phone ?? '',
    whatsapp: props.profile.whatsapp ?? '',
    birth_place: props.profile.birth_place ?? '',
    birth_date: dateValue(props.profile.birth_date),
    marital_status: props.profile.marital_status ?? '',
    availability_status: props.profile.availability_status ?? '',
    cv_file: null as File | null,
});

const imagePreview = ref<string | null>(props.profile.profile_image_url);
const currentImageName = props.profile.profile_image?.split('/').pop() ?? null;
const heroImagePreview = ref<string | null>(props.profile.hero_image_url);
const currentHeroImageName = props.profile.hero_image?.split('/').pop() ?? null;
const loginImagePreview = ref<string | null>(props.profile.login_image_url);
const currentLoginImageName = props.profile.login_image?.split('/').pop() ?? null;
const selectedCvName = ref<string | null>(props.profile.cv_name);
let objectUrl: string | null = null;
let heroObjectUrl: string | null = null;
let loginObjectUrl: string | null = null;

function selectImage(file: File | null): void {
    form.profile_image = file;

    if (objectUrl) {
        URL.revokeObjectURL(objectUrl);
    }

    objectUrl = file ? URL.createObjectURL(file) : null;
    imagePreview.value = objectUrl ?? props.profile.profile_image_url;
}

function selectCv(file: File | null): void {
    form.cv_file = file;
    selectedCvName.value = file?.name ?? props.profile.cv_name;
}

function selectHeroImage(file: File | null): void {
    form.hero_image = file;

    if (heroObjectUrl) {
        URL.revokeObjectURL(heroObjectUrl);
    }

    heroObjectUrl = file ? URL.createObjectURL(file) : null;
    heroImagePreview.value = heroObjectUrl ?? props.profile.hero_image_url;
}

function selectLoginImage(file: File | null): void {
    form.login_image = file;

    if (loginObjectUrl) {
        URL.revokeObjectURL(loginObjectUrl);
    }

    loginObjectUrl = file ? URL.createObjectURL(file) : null;
    loginImagePreview.value = loginObjectUrl ?? props.profile.login_image_url;
}

function submit(): void {
    form.transform((data) => ({ ...data, _method: 'patch' })).post(
        '/admin/profile',
        {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.profile_image = null;
                form.hero_image = null;
                form.login_image = null;
                form.cv_file = null;
            },
        },
    );
}

onBeforeUnmount(() => {
    if (objectUrl) {
        URL.revokeObjectURL(objectUrl);
    }

    if (heroObjectUrl) {
        URL.revokeObjectURL(heroObjectUrl);
    }

    if (loginObjectUrl) {
        URL.revokeObjectURL(loginObjectUrl);
    }
});
</script>

<template>
    <Head title="Profil" />
    <PageHeader
        eyebrow="Profil"
        title="Profil Pribadi"
        description="Kelola informasi utama untuk portofolio dan profil profesional Anda."
    />

    <form class="mt-8 space-y-8" @submit.prevent="submit">
        <section class="surface-panel p-5 sm:p-8">
            <div class="mb-6 border-b border-border pb-5">
                <h2 class="text-base font-semibold tracking-[-0.015em]">Identitas</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Nama, profesi, dan tagline yang mewakili profil Anda.
                </p>
            </div>
            <div class="grid gap-6 md:grid-cols-2">
                <FormField
                    id="full_name"
                    label="Nama Lengkap"
                    :error="form.errors.full_name"
                    required
                >
                    <Input id="full_name" v-model="form.full_name" />
                </FormField>
                <FormField
                    id="professional_title"
                    label="Profesi"
                    :error="form.errors.professional_title"
                >
                    <Input
                        id="professional_title"
                        v-model="form.professional_title"
                    />
                </FormField>
                <div class="md:col-span-2">
                    <FormField
                        id="tagline"
                        label="Tagline"
                        :error="form.errors.tagline"
                    >
                        <Input id="tagline" v-model="form.tagline" />
                    </FormField>
                </div>
            </div>
        </section>

        <section class="surface-panel p-5 sm:p-8">
            <div class="mb-6 border-b border-border pb-5">
                <h2 class="text-base font-semibold tracking-[-0.015em]">
                    Foto Profil
                </h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Kelola foto sidebar, foto utama, dan foto login secara terpisah.
                </p>
            </div>
            <div class="grid min-w-0 gap-8 md:grid-cols-2 xl:grid-cols-3">
                <div class="min-w-0 rounded-xl border border-border p-5 sm:p-6">
                    <div
                        class="mx-auto size-28 overflow-hidden rounded-full border border-border bg-muted"
                    >
                        <img
                            v-if="imagePreview"
                            :src="imagePreview"
                            alt="Pratinjau foto profil sidebar"
                            class="h-full w-full object-cover object-center"
                        />
                        <div
                            v-else
                            class="grid h-full place-items-center text-sm text-muted-foreground"
                        >
                            Belum ada foto
                        </div>
                    </div>
                    <FileUploadField
                        id="profile_image"
                        class="mt-6"
                        label="Foto Profil Sidebar"
                        accept="image/jpeg,image/png,image/webp"
                        hint="Digunakan untuk foto profil kecil di sidebar publik dan avatar admin. Rekomendasi: rasio 1:1 minimal 600 × 600 px. JPG, PNG, atau WebP · maks. 6 MB."
                        :error="form.errors.profile_image"
                        :current-name="form.profile_image?.name || currentImageName"
                        @change="selectImage"
                    />
                </div>

                <div class="min-w-0 rounded-xl border border-border p-5 sm:p-6">
                    <div
                        class="mx-auto flex h-52 max-w-[13rem] items-center justify-center overflow-hidden rounded-xl border border-border bg-white"
                    >
                        <img
                            v-if="heroImagePreview"
                            :src="heroImagePreview"
                            alt="Pratinjau foto utama"
                            class="h-full w-full object-contain"
                        />
                        <div
                            v-else
                            class="grid h-full place-items-center px-4 text-center text-sm text-muted-foreground"
                        >
                            Belum ada foto utama
                        </div>
                    </div>
                    <FileUploadField
                        id="hero_image"
                        class="mt-6"
                        label="Foto Utama"
                        accept="image/jpeg,image/png,image/webp"
                        hint="Digunakan sebagai foto utama pada landing page. Rekomendasi: portrait atau half-body background putih/transparan minimal tinggi 1000 px. JPG, PNG, atau WebP · maks. 6 MB."
                        :error="form.errors.hero_image"
                        :current-name="form.hero_image?.name || currentHeroImageName"
                        @change="selectHeroImage"
                    />
                </div>

                <div class="min-w-0 rounded-xl border border-border p-5 sm:p-6">
                    <div
                        class="mx-auto flex h-52 max-w-[13rem] items-center justify-center overflow-hidden rounded-xl border border-border bg-white"
                    >
                        <img
                            v-if="loginImagePreview"
                            :src="loginImagePreview"
                            alt="Pratinjau foto login"
                            class="h-full w-full object-contain"
                        />
                        <div
                            v-else
                            class="grid h-full place-items-center px-4 text-center text-sm text-muted-foreground"
                        >
                            Belum ada foto login
                        </div>
                    </div>
                    <FileUploadField
                        id="login_image"
                        class="mt-6"
                        label="Foto Login"
                        accept="image/jpeg,image/png,image/webp"
                        hint="Digunakan sebagai foto besar pada halaman login admin. Rekomendasi: foto portrait atau half-body dengan background putih/transparan, minimal tinggi 1000 px · maks. 6 MB."
                        :error="form.errors.login_image"
                        :current-name="form.login_image?.name || currentLoginImageName"
                        @change="selectLoginImage"
                    />
                </div>
            </div>
        </section>

        <section class="surface-panel p-5 sm:p-8">
            <div class="mb-6 border-b border-border pb-5">
                <h2 class="text-base font-semibold tracking-[-0.015em]">Tentang</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Ringkasan singkat dan informasi lengkap mengenai profil Anda.
                </p>
            </div>
            <div class="grid gap-6">
                <FormField
                    id="short_bio"
                    label="Bio Singkat"
                    :error="form.errors.short_bio"
                    hint="Pengantar singkat untuk bagian profil yang ringkas."
                >
                    <FormTextarea
                        id="short_bio"
                        v-model="form.short_bio"
                        :rows="5"
                        class="min-h-32"
                    />
                </FormField>
                <FormField
                    id="about"
                    label="Tentang"
                    :error="form.errors.about"
                    hint="Informasi profil lengkap. Gunakan paragraf yang natural dan mudah dibaca."
                >
                    <FormTextarea
                        id="about"
                        v-model="form.about"
                        :rows="10"
                        class="min-h-56"
                    />
                </FormField>
            </div>
        </section>

        <section class="surface-panel p-5 sm:p-8">
            <div class="mb-6 border-b border-border pb-5">
                <h2 class="text-base font-semibold tracking-[-0.015em]">
                    Informasi Pribadi
                </h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Detail pribadi tambahan yang ditampilkan sesuai konfigurasi profil.
                </p>
            </div>
            <div class="grid gap-6 md:grid-cols-2">
                <FormField
                    id="birth_place"
                    label="Tempat Lahir"
                    :error="form.errors.birth_place"
                >
                    <Input id="birth_place" v-model="form.birth_place" />
                </FormField>
                <FormField
                    id="birth_date"
                    label="Tanggal Lahir"
                    :error="form.errors.birth_date"
                >
                    <Input id="birth_date" v-model="form.birth_date" type="date" />
                </FormField>
                <FormField
                    id="marital_status"
                    label="Status Pernikahan"
                    :error="form.errors.marital_status"
                    hint="Opsional, misalnya Belum Menikah atau Menikah."
                >
                    <Input
                        id="marital_status"
                        v-model="form.marital_status"
                        placeholder="Belum Menikah"
                    />
                </FormField>
            </div>
        </section>

        <section class="surface-panel p-5 sm:p-8">
            <div class="mb-6 border-b border-border pb-5">
                <h2 class="text-base font-semibold tracking-[-0.015em]">
                    Kontak & Ketersediaan
                </h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Informasi kontak profesional, lokasi, dan status ketersediaan.
                </p>
            </div>
            <div class="grid min-w-0 gap-6 md:grid-cols-2">
                <FormField id="email" label="Email" :error="form.errors.email">
                    <Input id="email" v-model="form.email" type="email" />
                </FormField>
                <FormField
                    id="location"
                    label="Lokasi"
                    :error="form.errors.location"
                >
                    <Input id="location" v-model="form.location" />
                </FormField>
                <FormField
                    id="phone"
                    label="Telepon"
                    :error="form.errors.phone"
                >
                    <Input id="phone" v-model="form.phone" type="tel" />
                </FormField>
                <FormField
                    id="whatsapp"
                    label="WhatsApp"
                    :error="form.errors.whatsapp"
                >
                    <Input id="whatsapp" v-model="form.whatsapp" type="tel" />
                </FormField>
                <FormField
                    id="availability_status"
                    label="Status Ketersediaan"
                    :error="form.errors.availability_status"
                >
                    <Input
                        id="availability_status"
                        v-model="form.availability_status"
                        placeholder="Tersedia untuk proyek tertentu"
                    />
                </FormField>
                <FileUploadField
                    id="cv_file"
                    label="Berkas CV"
                    accept="application/pdf,.pdf"
                    hint="Hanya PDF · maks. 5 MB"
                    :error="form.errors.cv_file"
                    :current-name="selectedCvName"
                    @change="selectCv"
                />
            </div>
        </section>

        <div
            class="sticky bottom-4 flex justify-end rounded-xl border border-border bg-card/90 p-3 shadow-sm backdrop-blur-md"
        >
            <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Menyimpan…' : 'Simpan Profil' }}
            </Button>
        </div>
    </form>
</template>
