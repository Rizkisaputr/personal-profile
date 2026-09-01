<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { home } from '@/routes';

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

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <main
        class="grid min-h-svh bg-background lg:grid-cols-[minmax(0,0.92fr)_minmax(30rem,1.08fr)]"
    >
        <section
            class="relative hidden overflow-hidden border-r border-border bg-background-warm p-10 lg:flex lg:flex-col xl:p-14"
        >
            <Link
                :href="home()"
                class="relative z-20 inline-flex w-fit items-center gap-3 text-sm font-semibold tracking-[-0.01em]"
            >
                <span
                    class="grid size-9 place-items-center rounded-[10px] bg-foreground text-xs font-semibold text-background"
                    >{{ initials }}</span
                >
                {{ identity?.name || 'Personal Profile' }}
            </Link>

            <div
                class="relative mx-auto mt-8 w-full max-w-[30rem] flex-1 overflow-hidden rounded-2xl border border-border bg-[#e9e9e5]"
            >
                <img
                    v-if="identity?.image_url"
                    :src="identity.image_url"
                    :alt="imageAlt"
                    width="800"
                    height="1000"
                    fetchpriority="high"
                    decoding="async"
                    class="absolute inset-0 h-full w-full object-cover object-center"
                />
                <div
                    v-else
                    class="hero-editorial-grid absolute inset-0 flex flex-col justify-between p-8 xl:p-10"
                >
                    <div class="flex items-center justify-between">
                        <span class="editorial-kicker">Profile</span>
                        <span class="size-2 rounded-full bg-primary" />
                    </div>
                    <div>
                        <div class="mb-7 h-px w-24 bg-primary" />
                        <p
                            class="text-[clamp(5rem,11vw,9rem)] leading-none font-medium tracking-[-0.09em]"
                        >
                            {{ initials }}
                        </p>
                    </div>
                </div>
                <div
                    class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/72 via-black/30 to-transparent px-8 pt-24 pb-8 text-white xl:px-10 xl:pb-10"
                >
                    <p class="text-xl font-semibold tracking-[-0.025em]">
                        {{ identity?.name || 'Personal Profile' }}
                    </p>
                    <p
                        v-if="identity?.professional_title"
                        class="mt-1 text-sm text-white/78"
                    >
                        {{ identity.professional_title }}
                    </p>
                    <p
                        v-if="identity?.tagline"
                        class="mt-4 max-w-sm text-sm leading-6 text-white/72"
                    >
                        {{ identity.tagline }}
                    </p>
                </div>
            </div>
        </section>

        <section
            class="flex min-h-svh items-center justify-center px-5 py-8 sm:px-8 lg:px-12"
        >
            <div class="w-full max-w-[25rem]">
                <Link
                    :href="home()"
                    class="mb-8 inline-flex items-center gap-3 text-sm font-semibold lg:hidden"
                >
                    <span
                        class="grid size-9 place-items-center rounded-[10px] bg-foreground text-xs text-background"
                        >{{ initials }}</span
                    >
                    {{ identity?.name || 'Personal Profile' }}
                </Link>

                <div
                    class="mb-8 flex items-center gap-4 rounded-xl border border-border bg-background-warm p-3 lg:hidden"
                >
                    <div
                        class="grid size-14 shrink-0 place-items-center overflow-hidden rounded-lg border border-border bg-card text-sm font-semibold"
                    >
                        <img
                            v-if="identity?.image_url"
                            :src="identity.image_url"
                            alt=""
                            class="h-full w-full object-cover object-center"
                            decoding="async"
                        />
                        <span v-else aria-hidden="true">{{ initials }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold">
                            {{ identity?.name || 'Personal Profile' }}
                        </p>
                        <p
                            v-if="identity?.professional_title"
                            class="mt-0.5 truncate text-xs text-muted-foreground"
                        >
                            {{ identity.professional_title }}
                        </p>
                    </div>
                </div>

                <header class="mb-8">
                    <p class="editorial-kicker mb-3">Admin access</p>
                    <h1
                        v-if="title"
                        class="text-[2rem] leading-tight font-semibold tracking-[-0.035em]"
                    >
                        {{ title }}
                    </h1>
                    <p
                        v-if="description"
                        class="mt-3 text-[0.95rem] leading-6 text-muted-foreground"
                    >
                        {{ description }}
                    </p>
                </header>

                <slot />
            </div>
        </section>
    </main>
</template>
