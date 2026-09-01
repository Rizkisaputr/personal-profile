<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const identity = computed(() => page.props.profileIdentity);
const initials = computed(() =>
    (identity.value?.name || 'Personal Profile')
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase(),
);
const imageAlt = computed(() =>
    [identity.value?.name, identity.value?.professional_title]
        .filter(Boolean)
        .join(' — '),
);

defineProps<{ title?: string; description?: string }>();
</script>

<template>
    <main
        class="grid min-h-svh bg-white lg:grid-cols-[minmax(0,1.05fr)_minmax(28rem,0.95fr)]"
    >
        <section
            class="relative hidden min-h-svh overflow-hidden border-r border-[#ececea] bg-white px-10 lg:flex lg:flex-col xl:px-14"
        >
            <div class="flex min-h-0 flex-1 items-end justify-center">
                <img
                    v-if="identity?.login_image_url"
                    :src="identity.login_image_url"
                    :alt="imageAlt"
                    width="800"
                    height="1000"
                    fetchpriority="high"
                    decoding="async"
                    class="h-full max-h-svh w-full max-w-[36rem] object-contain object-bottom"
                />
                <div
                    v-else
                    class="grid h-full w-full max-w-[32rem] place-items-center text-[clamp(6rem,13vw,11rem)] font-semibold tracking-[-0.09em] text-[#171717]/10"
                    aria-hidden="true"
                >
                    {{ initials }}
                </div>
            </div>
        </section>

        <section
            class="flex min-h-svh items-center justify-center bg-white px-5 py-10 sm:px-8 lg:px-12"
        >
            <div class="w-full max-w-[25rem]">
                <header class="mb-8">
                    <p
                        class="mb-3 text-xs font-semibold tracking-[0.12em] text-[#737780] uppercase"
                    >
                        Personal Profile Admin
                    </p>
                    <h1
                        class="text-[2rem] leading-tight font-semibold tracking-[-0.035em] text-[#171717]"
                    >
                        Welcome back, {{ identity?.name || 'Rizki Saputra' }}
                    </h1>
                    <p
                        v-if="description"
                        class="mt-3 text-[0.95rem] leading-6 text-[#737780]"
                    >
                        {{ description }}
                    </p>
                </header>
                <slot />
            </div>
        </section>
    </main>
</template>
