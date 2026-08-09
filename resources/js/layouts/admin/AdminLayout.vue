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
    LogOut,
    Menu,
    Package,
    PiggyBank,
    RefreshCw,
    Settings,
    Share2,
    Sparkles,
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
        label: 'Profil',
        href: '/admin/profile',
        icon: UserRound,
    },
    { label: 'Keahlian', href: '/admin/skills', icon: Sparkles },
    {
        label: 'Pengalaman',
        href: '/admin/experiences',
        icon: BriefcaseBusiness,
    },
    {
        label: 'Pendidikan',
        href: '/admin/educations',
        icon: GraduationCap,
    },
    {
        label: 'Pelatihan & Kursus',
        href: '/admin/trainings',
        icon: BookOpenCheck,
    },
    {
        label: 'Proyek',
        href: '/admin/projects',
        icon: FolderKanban,
    },
    {
        label: 'Teknologi',
        href: '/admin/technologies',
        icon: Code2,
    },
    {
        label: 'Sertifikat',
        href: '/admin/certificates',
        icon: Award,
    },
    {
        label: 'Media Sosial',
        href: '/admin/social-links',
        icon: Share2,
    },
    {
        label: 'Pengaturan Website',
        href: '/admin/settings',
        icon: Settings,
    },
];

const financeNavigation = [
    { label: 'Ringkasan', href: '/admin/finance', icon: ChartPie },
    {
        label: 'Transaksi',
        href: '/admin/finance/transactions',
        icon: ArrowLeftRight,
    },
    { label: 'Rekening', href: '/admin/finance/accounts', icon: Landmark },
    {
        label: 'Hutang & Piutang',
        href: '/admin/finance/debts',
        icon: HandCoins,
    },
    { label: 'Tabungan', href: '/admin/finance/savings', icon: PiggyBank },
    {
        label: 'Tagihan Rutin',
        href: '/admin/finance/bills',
        icon: CalendarClock,
    },
];

const personalNavigation = [
    { label: 'Berkas Penting', href: '/admin/documents', icon: FileLock2 },
    { label: 'Langganan', href: '/admin/subscriptions', icon: RefreshCw },
    { label: 'Aset', href: '/admin/assets', icon: Package },
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
        page.props.profileIdentity?.name || page.props.auth.user.name || 'Akun'
    );
}

function handleLogout(): void {
    router.flushAll();
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
                    RS My Workspace
                </Link>
                <Button
                    variant="ghost"
                    size="icon-sm"
                    class="lg:hidden"
                    aria-label="Tutup navigasi"
                    @click="mobileOpen = false"
                >
                    <X class="size-4" />
                </Button>
            </div>

            <nav
                class="mt-8 flex-1 overflow-y-auto"
                aria-label="Navigasi admin"
            >
                <p
                    class="px-3 text-[0.68rem] font-semibold tracking-[0.12em] text-muted-foreground/65 uppercase"
                >
                    Ringkasan
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
                        ><LayoutDashboard class="size-[18px]" />Dasboard</Link
                    >
                </div>

                <p
                    class="mt-7 px-3 text-[0.68rem] font-semibold tracking-[0.12em] text-muted-foreground/65 uppercase"
                >
                    Pribadi
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
                        <Wallet class="size-[18px]" /><span>Keuangan</span
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
                    Portofolio
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
                            aria-label="Buka navigasi"
                            aria-controls="admin-navigation"
                            :aria-expanded="mobileOpen"
                            @click="mobileOpen = true"
                        >
                            <Menu class="size-5" />
                        </Button>
                    </div>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <button
                                type="button"
                                class="flex max-w-[13rem] items-center gap-2.5 rounded-lg p-1.5 text-sm font-medium transition-colors outline-none hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring"
                                aria-label="Buka menu akun"
                            >
                                <ProfileAvatar
                                    class="size-9 shrink-0 text-[0.65rem]"
                                    :name="accountName()"
                                    :image-url="
                                        page.props.profileIdentity?.image_url
                                    "
                                    :alt="`Foto profil ${accountName()}`"
                                />
                                <span
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
                                    <UserRound class="size-4" />Profil
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem as-child>
                                <a
                                    href="/"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex w-full cursor-pointer items-center gap-2.5"
                                >
                                    <ExternalLink class="size-4" />Lihat Website
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
                                    <LogOut class="size-4" />Keluar
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
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
