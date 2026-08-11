<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowLeftRight,
    Award,
    BookOpenCheck,
    BriefcaseBusiness,
    CalendarClock,
    ChartPie,
    ChevronDown,
    Code2,
    ExternalLink,
    FileLock2,
    FolderKanban,
    GraduationCap,
    HandCoins,
    Landmark,
    LayoutDashboard,
    Languages,
    LogOut,
    Menu,
    Package,
    PiggyBank,
    RefreshCw,
    Settings,
    Share2,
    Sparkles,
    Tags,
    UserRound,
    Wallet,
    X,
} from '@lucide/vue';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import ProfileAvatar from '@/components/admin/ProfileAvatar.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Toaster } from '@/components/ui/sonner';
import { logout } from '@/routes';

const page = usePage();
const mobileOpen = ref(false);
const financeOpen = ref(page.url.startsWith('/admin/finance'));

function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') {
        mobileOpen.value = false;
    }
}

watch(mobileOpen, (isOpen) => {
    document.body.style.overflow = isOpen ? 'hidden' : '';
});

watch(
    () => page.url,
    (url) => {
        mobileOpen.value = false;

        if (url.startsWith('/admin/finance')) {
            financeOpen.value = true;
        }
    },
);

onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => {
    document.body.style.overflow = '';
    window.removeEventListener('keydown', onKeydown);
});

const workspaceNavigation = [
    {
        label: 'Profile',
        href: '/admin/profile',
        icon: UserRound,
    },
    { label: 'Skills', href: '/admin/skills', icon: Sparkles },
    {
        label: 'Experience',
        href: '/admin/experiences',
        icon: BriefcaseBusiness,
    },
    {
        label: 'Education',
        href: '/admin/educations',
        icon: GraduationCap,
    },
    {
        label: 'Training & Courses',
        href: '/admin/trainings',
        icon: BookOpenCheck,
    },
    {
        label: 'Projects',
        href: '/admin/projects',
        icon: FolderKanban,
    },
    {
        label: 'Technologies',
        href: '/admin/technologies',
        icon: Code2,
    },
    {
        label: 'Certificates',
        href: '/admin/certificates',
        icon: Award,
    },
    {
        label: 'Social Media',
        href: '/admin/social-links',
        icon: Share2,
    },
    {
        label: 'Website Settings',
        href: '/admin/settings',
        icon: Settings,
    },
];

const financeNavigation = [
    { label: 'Overview', href: '/admin/finance', icon: ChartPie },
    {
        label: 'Transactions',
        href: '/admin/finance/transactions',
        icon: ArrowLeftRight,
    },
    { label: 'Accounts', href: '/admin/finance/accounts', icon: Landmark },
    { label: 'Categories', href: '/admin/finance/categories', icon: Tags },
    {
        label: 'Debts & Receivables',
        href: '/admin/finance/debts',
        icon: HandCoins,
    },
    { label: 'Savings', href: '/admin/finance/savings', icon: PiggyBank },
    {
        label: 'Recurring Bills',
        href: '/admin/finance/bills',
        icon: CalendarClock,
    },
];

const personalNavigation = [
    { label: 'Important Documents', href: '/admin/documents', icon: FileLock2 },
    { label: 'Subscriptions', href: '/admin/subscriptions', icon: RefreshCw },
    { label: 'Assets', href: '/admin/assets', icon: Package },
];

function isActive(href: string | null): boolean {
    if (!href) {
        return false;
    }

    if (href === '/admin') {
        return page.url === '/admin';
    }

    if (href === '/admin/finance') {
        return page.url === '/admin/finance';
    }

    return page.url.startsWith(href);
}

function accountName(): string {
    return (
        page.props.profileIdentity?.name ||
        page.props.auth.user.name ||
        'Account'
    );
}

function handleLogout(): void {
    router.flushAll();
}

function changeLanguage(event: Event): void {
    const locale = (event.target as HTMLSelectElement).value;

    router.post('/admin/language', { locale }, { preserveScroll: true });
}
</script>

<template>
    <div class="admin-shell min-h-svh bg-background text-foreground">
        <div
            v-if="mobileOpen"
            class="fixed inset-0 z-40 bg-black/20 backdrop-blur-[2px] lg:hidden"
            @click="mobileOpen = false"
        />

        <aside
            id="admin-navigation"
            class="fixed inset-y-0 left-0 z-50 flex w-[17rem] flex-col border-r border-sidebar-border bg-sidebar px-4 py-5 transition-transform duration-300 ease-[var(--ease-editorial)] lg:translate-x-0"
            :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-11 items-center justify-between px-2">
                <Link
                    href="/admin"
                    class="text-[1.05rem] font-semibold tracking-[-0.015em]"
                >
                     My Workspace
                </Link>
                <Button
                    variant="ghost"
                    size="icon-sm"
                    class="lg:hidden"
                    aria-label="Close navigation"
                    @click="mobileOpen = false"
                >
                    <X class="size-4" />
                </Button>
            </div>

            <nav
                class="mt-8 flex-1 overflow-y-auto"
                aria-label="Admin navigation"
            >
                <p
                    class="px-3 text-[0.68rem] font-semibold tracking-[0.12em] text-muted-foreground/65 uppercase"
                >
                    Overview
                </p>
                <div class="mt-2">
                    <Link
                        href="/admin"
                        class="flex h-10 items-center gap-3 rounded-[10px] px-3 text-sm font-medium transition-colors"
                        :class="
                            isActive('/admin')
                                ? 'bg-foreground text-background'
                                : 'text-muted-foreground hover:bg-sidebar-accent hover:text-foreground'
                        "
                        @click="mobileOpen = false"
                        ><LayoutDashboard class="size-[18px]" />Dashboard</Link
                    >
                </div>

                <p
                    class="mt-7 px-3 text-[0.68rem] font-semibold tracking-[0.12em] text-muted-foreground/65 uppercase"
                >
                    Personal
                </p>
                <div class="mt-2 space-y-1">
                    <button
                        type="button"
                        class="flex h-10 w-full items-center gap-3 rounded-[10px] px-3 text-sm font-medium transition-colors"
                        :class="
                            page.url.startsWith('/admin/finance')
                                ? 'text-foreground'
                                : 'text-muted-foreground hover:bg-sidebar-accent hover:text-foreground'
                        "
                        :aria-expanded="financeOpen"
                        aria-controls="finance-navigation"
                        @click="financeOpen = !financeOpen"
                    >
                        <Wallet class="size-[18px]" /><span>Finance</span
                        ><ChevronDown
                            class="ml-auto size-4 transition-transform"
                            :class="financeOpen ? 'rotate-180' : ''"
                        />
                    </button>
                    <div
                        v-show="financeOpen"
                        id="finance-navigation"
                        class="ml-4 space-y-1 border-l border-sidebar-border pl-2"
                    >
                        <Link
                            v-for="item in financeNavigation"
                            :key="item.href"
                            :href="item.href"
                            class="flex h-9 items-center gap-2.5 rounded-lg px-3 text-[0.82rem] font-medium transition-colors"
                            :class="
                                isActive(item.href)
                                    ? 'bg-foreground text-background'
                                    : 'text-muted-foreground hover:bg-sidebar-accent hover:text-foreground'
                            "
                            @click="mobileOpen = false"
                            ><component :is="item.icon" class="size-4" />{{
                                item.label
                            }}</Link
                        >
                    </div>
                    <Link
                        v-for="item in personalNavigation"
                        :key="item.href"
                        :href="item.href"
                        class="flex h-10 items-center gap-3 rounded-[10px] px-3 text-sm font-medium transition-colors"
                        :class="
                            isActive(item.href)
                                ? 'bg-foreground text-background'
                                : 'text-muted-foreground hover:bg-sidebar-accent hover:text-foreground'
                        "
                        @click="mobileOpen = false"
                        ><component :is="item.icon" class="size-[18px]" />{{
                            item.label
                        }}</Link
                    >
                </div>

                <p
                    class="mt-7 px-3 text-[0.68rem] font-semibold tracking-[0.12em] text-muted-foreground/65 uppercase"
                >
                    Portfolio
                </p>
                <div class="mt-2 space-y-1">
                    <Link
                        v-for="item in workspaceNavigation"
                        :key="item.href"
                        :href="item.href"
                        class="flex h-10 items-center gap-3 rounded-[10px] px-3 text-sm font-medium transition-colors"
                        :class="
                            isActive(item.href)
                                ? 'bg-foreground text-background'
                                : 'text-muted-foreground hover:bg-sidebar-accent hover:text-foreground'
                        "
                        @click="mobileOpen = false"
                        ><component :is="item.icon" class="size-[18px]" />{{
                            item.label
                        }}</Link
                    >
                </div>
            </nav>
        </aside>

        <div class="min-h-svh bg-background lg:pl-[17rem]">
            <header
                class="sticky top-0 z-30 border-b border-border bg-card/95 backdrop-blur-md"
            >
                <div
                    class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-10"
                >
                    <div class="flex items-center">
                        <Button
                            variant="ghost"
                            size="icon"
                            class="lg:hidden"
                            aria-label="Open navigation"
                            aria-controls="admin-navigation"
                            :aria-expanded="mobileOpen"
                            @click="mobileOpen = true"
                        >
                            <Menu class="size-5" />
                        </Button>
                    </div>
                    <div class="flex items-center gap-2">
                        <label
                            class="relative flex items-center text-muted-foreground"
                            aria-label="Language"
                        >
                            <Languages
                                class="pointer-events-none absolute left-2.5 size-4"
                            />
                            <select
                                :value="page.props.locale ?? 'en'"
                                class="h-9 cursor-pointer appearance-none rounded-lg border border-border bg-background py-1 pr-7 pl-8 text-xs font-semibold text-foreground transition-colors outline-none hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring"
                                aria-label="Language"
                                @change="changeLanguage"
                            >
                                <option
                                    v-for="locale in page.props
                                        .availableLocales"
                                    :key="locale.code"
                                    :value="locale.code"
                                >
                                    {{ locale.code.toUpperCase() }}
                                </option>
                            </select>
                            <ChevronDown
                                class="pointer-events-none absolute right-2 size-3"
                            />
                        </label>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <button
                                    type="button"
                                    class="flex max-w-[13rem] items-center gap-2.5 rounded-lg p-1.5 text-sm font-medium transition-colors outline-none hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring"
                                    aria-label="Open account menu"
                                >
                                    <ProfileAvatar
                                        class="size-9 shrink-0 text-[0.65rem]"
                                        :name="accountName()"
                                        :image-url="
                                            page.props.profileIdentity
                                                ?.image_url
                                        "
                                        :alt="`Profile photo for ${accountName()}`"
                                    />
                                    <span
                                        data-no-translate
                                        class="hidden min-w-0 truncate sm:block"
                                        >{{ accountName() }}</span
                                    >
                                    <ChevronDown
                                        class="hidden size-4 shrink-0 text-muted-foreground sm:block"
                                    />
                                </button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                align="end"
                                class="w-48 rounded-xl p-1.5 shadow-lg"
                            >
                                <DropdownMenuItem as-child>
                                    <Link
                                        href="/admin/profile"
                                        class="flex w-full cursor-pointer items-center gap-2.5"
                                    >
                                        <UserRound class="size-4" />Profile
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem as-child>
                                    <a
                                        href="/"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="flex w-full cursor-pointer items-center gap-2.5"
                                    >
                                        <ExternalLink class="size-4" />View
                                        Website
                                    </a>
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem as-child>
                                    <Link
                                        :href="logout()"
                                        as="button"
                                        class="flex w-full cursor-pointer items-center gap-2.5 text-destructive"
                                        data-test="logout-button"
                                        @click="handleLogout"
                                    >
                                        <LogOut class="size-4" />Log Out
                                    </Link>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
            </header>

            <main
                class="min-h-[calc(100svh-4rem)] bg-background px-4 py-8 sm:px-6 sm:py-10 lg:px-10 lg:py-12"
            >
                <div class="mx-auto max-w-[76rem]">
                    <slot />
                </div>
            </main>
        </div>
        <Toaster position="top-right" />
    </div>
</template>
