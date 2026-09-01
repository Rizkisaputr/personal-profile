<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, X } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import type { PublicProfile, PublicSettings, SocialLink } from '@/types';
import SidebarSocialLinks from './SidebarSocialLinks.vue';

const page = usePage();
const open = ref(false);
const activeSection = ref('home');
let observer: IntersectionObserver | null = null;

const profile = computed(
    () => (page.props.profile ?? null) as PublicProfile | null,
);
const settings = computed(() => (page.props.settings ?? {}) as PublicSettings);
const socialLinks = computed(
    () => (page.props.socialLinks ?? []) as SocialLink[],
);
const sidebarSocialLinks = computed(
    () => (page.props.sidebarSocialLinks ?? socialLinks.value) as SocialLink[],
);
const isHome = computed(() => page.component === 'public/Home');
const name = computed(
    () =>
        profile.value?.full_name ||
        settings.value.website_title ||
        'Personal Profile',
);
const sidebarQuote = "“What you're not changing, you're choosing.”";
const initials = computed(() =>
    name.value
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase(),
);
const navigation = [
    { label: 'Home', id: 'home' },
    { label: 'About', id: 'about' },
    { label: 'Projects', id: 'projects' },
    { label: 'Skills', id: 'skills' },
    { label: 'Education', id: 'education' },
    { label: 'Training', id: 'training' },
    { label: 'Experience', id: 'experience' },
    { label: 'Contact', id: 'contact' },
];
const hrefFor = (id: string) =>
    isHome.value
        ? `#${id}`
        : id === 'projects'
          ? '/projects'
          : id === 'home'
            ? '/'
            : `/#${id}`;

function close(): void {
    open.value = false;
}
function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') {
        close();
    }
}
function setupObserver(): void {
    observer?.disconnect();

    if (!isHome.value) {
        activeSection.value = page.url.startsWith('/projects')
            ? 'projects'
            : 'home';

        return;
    }

    const sections = navigation
        .map(({ id }) => document.getElementById(id))
        .filter(Boolean) as HTMLElement[];
    observer = new IntersectionObserver(
        (entries) => {
            const visible = entries
                .filter((entry) => entry.isIntersecting)
                .sort(
                    (a, b) =>
                        a.boundingClientRect.top - b.boundingClientRect.top,
                )[0];

            if (visible) {
                activeSection.value = visible.target.id;
            }
        },
        { rootMargin: '-32% 0px -62% 0px', threshold: 0 },
    );
    sections.forEach((section) => observer?.observe(section));
}

function navigate(event: MouseEvent, id: string): void {
    if (!isHome.value) {
        return;
    }

    const section = document.getElementById(id);

    if (!section) {
        return;
    }

    event.preventDefault();
    activeSection.value = id;
    section.scrollIntoView({
        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches
            ? 'auto'
            : 'smooth',
        block: 'start',
    });
    window.history.replaceState(null, '', `#${id}`);
    close();
}

watch(open, (value) => {
    document.body.style.overflow = value ? 'hidden' : '';
});
watch(
    () => page.url,
    () => {
        close();
        requestAnimationFrame(setupObserver);
    },
);
onMounted(() => {
    window.addEventListener('keydown', onKeydown);
    setupObserver();
});
onBeforeUnmount(() => {
    observer?.disconnect();
    document.body.style.overflow = '';
    window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <header
        class="fixed inset-x-0 top-0 z-50 border-b border-[var(--public-sidebar-border)] bg-[var(--public-sidebar)]/95 backdrop-blur md:hidden"
    >
        <nav
            class="flex h-16 items-center justify-between px-4"
            aria-label="Primary navigation"
        >
            <Link
                href="/"
                class="flex min-w-0 items-center gap-3"
                @click="close"
            >
                <div
                    class="size-9 shrink-0 overflow-hidden rounded-full border border-border bg-white"
                >
                    <img
                        v-if="profile?.profile_image_url"
                        :src="profile.profile_image_url"
                        :alt="`${name} portrait`"
                        class="h-full w-full object-cover"
                    />
                    <span
                        v-else
                        class="grid h-full place-items-center text-xs font-semibold"
                        >{{ name.slice(0, 2).toUpperCase() }}</span
                    >
                </div>
                <span class="min-w-0"
                    ><span
                        class="block truncate text-sm font-semibold text-[#171717]"
                        >{{ name }}</span
                    ><span
                        v-if="sidebarQuote"
                        class="line-clamp-2 max-w-[13rem] text-[11px] leading-4 font-normal text-[var(--public-sidebar-role)] italic"
                        >{{ sidebarQuote }}</span
                    ></span
                >
            </Link>
            <button
                type="button"
                class="grid size-11 place-items-center rounded-lg hover:bg-black/5"
                :aria-expanded="open"
                aria-controls="public-mobile-menu"
                :aria-label="open ? 'Close menu' : 'Open menu'"
                @click="open = !open"
            >
                <X v-if="open" class="size-5" /><Menu v-else class="size-5" />
            </button>
        </nav>
        <Transition name="menu">
            <div
                v-if="open"
                id="public-mobile-menu"
                class="max-h-[calc(100svh-4rem)] overflow-y-auto border-t border-[var(--public-sidebar-divider)] bg-[var(--public-sidebar)] px-4 py-5"
            >
                <a
                    v-for="item in navigation"
                    :key="item.id"
                    :href="hrefFor(item.id)"
                    class="flex min-h-11 items-center border-b border-[var(--public-sidebar-divider)] text-sm transition-colors duration-200"
                    :class="
                        activeSection === item.id
                            ? 'text-[var(--public-sidebar-active)]'
                            : 'text-[var(--public-sidebar-nav)] hover:text-[#171717]'
                    "
                    @click="navigate($event, item.id)"
                    >{{ item.label }}</a
                >
                <SidebarSocialLinks :links="sidebarSocialLinks" class="mt-5" />
            </div>
        </Transition>
    </header>

    <aside
        class="fixed inset-y-0 left-0 z-40 hidden w-[240px] flex-col border-r border-[var(--public-sidebar-border)] bg-[var(--public-sidebar)] px-6 py-7 md:flex"
    >
        <Link href="/" class="block text-center">
            <div
                class="mx-auto size-24 overflow-hidden rounded-full border border-[var(--public-sidebar-photo-border)] bg-white"
            >
                <img
                    v-if="profile?.profile_image_url"
                    :src="profile.profile_image_url"
                    :alt="`${name} portrait`"
                    class="h-full w-full object-cover"
                />
                <span
                    v-else
                    class="grid h-full place-items-center text-xl font-semibold tracking-[-0.04em]"
                    >{{ initials || 'PP' }}</span
                >
            </div>
            <h2 class="mt-4 text-lg font-semibold tracking-[-0.035em]">
                {{ name }}
            </h2>
            <p
                class="mx-auto mt-1.5 line-clamp-3 max-w-[11rem] text-xs leading-[1.55] font-normal text-[var(--public-sidebar-role)] italic"
            >
                {{ sidebarQuote }}
            </p>
        </Link>
        <nav
            class="mt-7 border-t border-[var(--public-sidebar-divider)] pt-4"
            aria-label="Primary navigation"
        >
            <a
                v-for="item in navigation"
                :key="item.id"
                :href="hrefFor(item.id)"
                class="group flex h-8 items-center gap-3 text-[11px] font-medium tracking-[0.09em] uppercase transition-colors duration-200"
                :class="
                    activeSection === item.id
                        ? 'text-[var(--public-sidebar-active)]'
                        : 'text-[var(--public-sidebar-nav)] hover:text-[#171717]'
                "
                @click="navigate($event, item.id)"
            >
                <span
                    class="h-px transition-all duration-200"
                    :class="
                        activeSection === item.id
                            ? 'w-5 bg-[var(--public-sidebar-active)]'
                            : 'w-2 bg-[var(--public-sidebar-divider)] group-hover:w-3'
                    "
                />{{ item.label }}
            </a>
        </nav>
        <div
            class="mt-auto border-t border-[var(--public-sidebar-divider)] pt-4"
        >
            <SidebarSocialLinks :links="sidebarSocialLinks" />
        </div>
    </aside>
</template>
