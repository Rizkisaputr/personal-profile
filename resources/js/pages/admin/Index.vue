<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowDownRight,
    ArrowRight,
    ArrowUpRight,
    CircleDollarSign,
    FolderKanban,
    Landmark,
    Plus,
    PiggyBank,
    ReceiptText,
    Scale,
    WalletCards,
} from '@lucide/vue';
import { computed } from 'vue';
import ProfileAvatar from '@/components/admin/ProfileAvatar.vue';
import { formatIDR } from '@/lib/money';

type Summary = {
    total_balance: string;
    income: string;
    expense: string;
    net_cash_flow: string;
    remaining_debt: string;
    savings: string;
};
type Trend = { period: string; label: string; income: string; expense: string };
type Category = { category: string; amount: string };
type Goal = {
    id: number;
    name: string;
    saved: string;
    target: string;
    progress: number;
};
type Debt = { id: number; name: string; remaining: string; progress: number };
type ProjectSummary = {
    total: number;
    waiting: number;
    running: number;
    on_hold: number;
    done: number;
};
type Project = {
    id: number;
    title: string;
    status: keyof Omit<ProjectSummary, 'total'>;
    technologies: string[];
    updated_at: string | null;
};
type Transaction = {
    id: number;
    type: string;
    transaction_date: string;
    description: string | null;
    amount: string;
    category?: { name: string } | null;
    account?: { name: string } | null;
    from_account?: { name: string } | null;
    to_account?: { name: string } | null;
};

const props = defineProps<{
    financialSummary: Summary;
    financeTrend: Trend[];
    expenseByCategory: Category[];
    savingsGoals: Goal[];
    debtSummary: { total: string; items: Debt[] };
    projectSummary: ProjectSummary;
    recentProjects: Project[];
    recentTransactions: Transaction[];
}>();
const page = usePage();
const identity = computed(
    () =>
        page.props.profileIdentity as
            { name?: string; image_url?: string } | undefined,
);
const locale = computed(() => (page.props.locale === 'id' ? 'id-ID' : 'en-US'));
const firstName = computed(
    () =>
        (identity.value?.name || page.props.auth.user.name)
            .trim()
            .split(/\s+/)[0],
);
const periodLabel = computed(() =>
    new Intl.DateTimeFormat(locale.value, {
        month: 'long',
        year: 'numeric',
    }).format(new Date()),
);
const greeting = computed(() => {
    const hour = new Date().getHours();

    if (page.props.locale === 'id') {
        return hour < 11
            ? 'Selamat pagi'
            : hour < 18
              ? 'Selamat siang'
              : 'Selamat malam';
    }

    return hour < 12
        ? 'Good morning'
        : hour < 18
          ? 'Good afternoon'
          : 'Good evening';
});
const intro = computed(() =>
    page.props.locale === 'id'
        ? 'Berikut ringkasan kondisi personal Anda bulan ini.'
        : 'Here is your personal overview for this month.',
);

const kpis = computed(() => [
    {
        label: 'Total Balance',
        value: props.financialSummary.total_balance,
        icon: WalletCards,
        tone: 'neutral',
    },
    {
        label: 'Income This Month',
        value: props.financialSummary.income,
        icon: ArrowUpRight,
        tone: 'positive',
    },
    {
        label: 'Expense This Month',
        value: props.financialSummary.expense,
        icon: ArrowDownRight,
        tone: 'negative',
    },
    {
        label: 'Net Cash Flow',
        value: props.financialSummary.net_cash_flow,
        icon: Scale,
        tone:
            Number(props.financialSummary.net_cash_flow) >= 0
                ? 'positive'
                : 'negative',
    },
    {
        label: 'Remaining Debt',
        value: props.financialSummary.remaining_debt,
        icon: CircleDollarSign,
        tone: 'neutral',
    },
    {
        label: 'Total Savings',
        value: props.financialSummary.savings,
        icon: PiggyBank,
        tone: 'primary',
    },
]);
const chartHasData = computed(() =>
    props.financeTrend.some(
        (item) => Number(item.income) || Number(item.expense),
    ),
);
const chartMax = computed(() =>
    Math.max(
        1,
        ...props.financeTrend.flatMap((item) => [
            Number(item.income),
            Number(item.expense),
        ]),
    ),
);
const expenseTotal = computed(() =>
    props.expenseByCategory.reduce(
        (total, item) => total + Number(item.amount),
        0,
    ),
);
const categoryColors = [
    '#315cff',
    '#5878e8',
    '#7990dc',
    '#99a6c8',
    '#b6bdca',
    '#d2d6dd',
];
const donutStyle = computed(() => {
    if (!expenseTotal.value) {
        return { background: '#eef0f3' };
    }

    let cursor = 0;
    const stops = props.expenseByCategory.map((item, index) => {
        const start = cursor;
        cursor += (Number(item.amount) / expenseTotal.value) * 100;

        return `${categoryColors[index]} ${start}% ${cursor}%`;
    });

    return { background: `conic-gradient(${stops.join(',')})` };
});
function formattedDate(value: string): string {
    return new Intl.DateTimeFormat(locale.value, {
        day: 'numeric',
        month: 'short',
    }).format(new Date(value));
}
function relativeDate(value: string | null): string {
    if (!value) {
        return '—';
    }

    const days = Math.round(
        (Date.now() - new Date(value).getTime()) / 86400000,
    );

    if (days <= 0) {
        return page.props.locale === 'id' ? 'Hari ini' : 'Today';
    }

    if (days === 1) {
        return page.props.locale === 'id' ? 'Kemarin' : 'Yesterday';
    }

    return page.props.locale === 'id'
        ? `${days} hari lalu`
        : `${days} days ago`;
}
function statusLabel(status: Project['status']): string {
    const labels =
        page.props.locale === 'id'
            ? {
                  waiting: 'Menunggu',
                  running: 'Berjalan',
                  on_hold: 'Ditunda',
                  done: 'Selesai',
              }
            : {
                  waiting: 'Waiting',
                  running: 'Running',
                  on_hold: 'On Hold',
                  done: 'Completed',
              };

    return labels[status];
}
function transactionTitle(item: Transaction): string {
    return (
        item.description ||
        item.category?.name ||
        (item.type === 'transfer' ? 'Account transfer' : item.type)
    );
}
function transactionMeta(item: Transaction): string {
    if (item.type === 'transfer') {
        return `${item.from_account?.name || 'Account'} → ${item.to_account?.name || 'Account'}`;
    }

    return item.category?.name || item.account?.name || 'Uncategorized';
}
</script>

<template>
    <Head title="Dashboard" />
    <div class="space-y-7 sm:space-y-9">
        <header
            class="flex flex-col gap-5 border-b border-border/70 pb-7 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="flex min-w-0 items-center gap-4">
                <ProfileAvatar
                    class="size-12 shrink-0 ring-4 ring-primary/5 sm:size-14"
                    :name="identity?.name || page.props.auth.user.name"
                    :image-url="identity?.image_url"
                />
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                        <h1
                            class="text-2xl font-semibold tracking-[-0.035em] sm:text-3xl"
                        >
                            {{ greeting }},
                            <span data-no-translate>{{ firstName }}</span>
                        </h1>
                        <span
                            class="rounded-full border border-border bg-card px-2.5 py-1 text-[0.68rem] font-semibold tracking-wide text-muted-foreground uppercase"
                            >{{ periodLabel }}</span
                        >
                    </div>
                    <p class="mt-1.5 text-sm text-muted-foreground">
                        {{ intro }}
                    </p>
                </div>
            </div>
            <div class="flex shrink-0 flex-wrap gap-2">
                <Link
                    href="/admin/finance/transactions/create"
                    class="inline-flex h-9 items-center gap-2 rounded-lg bg-foreground px-3.5 text-xs font-semibold text-background transition-colors hover:bg-foreground/85 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    ><Plus class="size-3.5" />Add Transaction</Link
                >
                <Link
                    href="/admin/projects/create"
                    class="inline-flex h-9 items-center gap-2 rounded-lg border border-border bg-card px-3.5 text-xs font-semibold transition-colors hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    ><Plus class="size-3.5" />Add Project</Link
                >
            </div>
        </header>

        <section aria-labelledby="financial-overview">
            <div class="mb-4 flex items-end justify-between">
                <div>
                    <p class="editorial-kicker">Personal Finance</p>
                    <h2
                        id="financial-overview"
                        class="mt-1 text-lg font-semibold tracking-tight"
                    >
                        Financial Overview
                    </h2>
                </div>
                <Link
                    href="/admin/finance"
                    class="hidden items-center gap-1 text-xs font-semibold text-muted-foreground transition-colors hover:text-foreground sm:flex"
                    >View finance <ArrowRight class="size-3.5"
                /></Link>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                <article
                    v-for="card in kpis"
                    :key="card.label"
                    class="group relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-[0_1px_2px_rgba(15,23,42,0.025)] transition duration-200 hover:-translate-y-0.5 hover:border-foreground/15"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p
                                class="text-[0.68rem] font-semibold tracking-[0.1em] text-muted-foreground uppercase"
                            >
                                {{ card.label }}
                            </p>
                            <p
                                class="mt-4 text-[1.65rem] leading-none font-semibold tracking-[-0.04em] tabular-nums sm:text-3xl"
                                :class="
                                    card.tone === 'positive'
                                        ? 'text-emerald-700'
                                        : card.tone === 'negative'
                                          ? 'text-rose-700'
                                          : ''
                                "
                            >
                                {{ formatIDR(card.value) }}
                            </p>
                        </div>
                        <span
                            class="grid size-9 shrink-0 place-items-center rounded-xl"
                            :class="
                                card.tone === 'positive'
                                    ? 'bg-emerald-50 text-emerald-700'
                                    : card.tone === 'negative'
                                      ? 'bg-rose-50 text-rose-700'
                                      : card.tone === 'primary'
                                        ? 'bg-primary/10 text-primary'
                                        : 'bg-muted text-muted-foreground'
                            "
                            ><component :is="card.icon" class="size-[18px]"
                        /></span>
                    </div>
                </article>
            </div>
        </section>

        <section
            class="grid min-w-0 gap-5 xl:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)]"
        >
            <article
                class="min-w-0 rounded-2xl border border-border bg-card p-5 sm:p-6"
            >
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="font-semibold">Income vs Expense</h2>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Last 6 months · transfers excluded
                        </p>
                    </div>
                    <div class="flex gap-4 text-[0.7rem] text-muted-foreground">
                        <span class="flex items-center gap-1.5"
                            ><i
                                class="size-2 rounded-full bg-emerald-600"
                            />Income</span
                        ><span class="flex items-center gap-1.5"
                            ><i
                                class="size-2 rounded-full bg-rose-500"
                            />Expense</span
                        >
                    </div>
                </div>
                <div v-if="chartHasData" class="mt-6 min-w-0">
                    <div
                        class="relative grid h-60 grid-cols-6 gap-2 border-b border-border px-1 sm:gap-4 sm:px-3"
                        role="img"
                        aria-label="Income and expense comparison for the last six months"
                    >
                        <div
                            class="pointer-events-none absolute inset-x-0 top-0 bottom-0 flex flex-col justify-between"
                            aria-hidden="true"
                        >
                            <i
                                v-for="line in 4"
                                :key="line"
                                class="block border-t border-dashed border-border/55"
                            />
                        </div>
                        <div
                            v-for="item in financeTrend"
                            :key="item.period"
                            class="relative z-10 flex min-w-0 items-end justify-center gap-1 sm:gap-2"
                        >
                            <div
                                class="group relative flex h-full flex-1 items-end justify-end sm:max-w-8"
                            >
                                <div
                                    class="w-full rounded-t-md bg-emerald-600/80 transition-all duration-200 group-hover:bg-emerald-600"
                                    :style="{
                                        height: Number(item.income)
                                            ? `${Math.max(5, (Number(item.income) / chartMax) * 100)}%`
                                            : '0%',
                                    }"
                                    :title="`${item.label} · Income ${formatIDR(item.income)}`"
                                />
                            </div>
                            <div
                                class="group relative flex h-full flex-1 items-end justify-start sm:max-w-8"
                            >
                                <div
                                    class="w-full rounded-t-md bg-rose-400/80 transition-all duration-200 group-hover:bg-rose-500"
                                    :style="{
                                        height: Number(item.expense)
                                            ? `${Math.max(5, (Number(item.expense) / chartMax) * 100)}%`
                                            : '0%',
                                    }"
                                    :title="`${item.label} · Expense ${formatIDR(item.expense)}`"
                                />
                            </div>
                            <span class="sr-only"
                                >{{ item.label }}: Income
                                {{ formatIDR(item.income) }}, Expense
                                {{ formatIDR(item.expense) }}</span
                            >
                        </div>
                    </div>
                    <div
                        class="grid grid-cols-6 px-1 pt-3 text-center text-[0.65rem] font-medium text-muted-foreground sm:px-3"
                    >
                        <span v-for="item in financeTrend" :key="item.period">{{
                            item.label
                        }}</span>
                    </div>
                </div>
                <div
                    v-else
                    class="mt-6 grid h-60 place-items-center rounded-xl border border-dashed border-border bg-muted/20 px-6 text-center"
                >
                    <div>
                        <ReceiptText
                            class="mx-auto size-6 text-muted-foreground/60"
                        />
                        <p class="mt-3 text-sm font-medium">
                            No transactions this month.
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Your six-month trend will appear here.
                        </p>
                    </div>
                </div>
            </article>
            <article
                class="min-w-0 rounded-2xl border border-border bg-card p-5 sm:p-6"
            >
                <h2 class="font-semibold">Spending by Category</h2>
                <p class="mt-1 text-xs text-muted-foreground">Current month</p>
                <div v-if="expenseByCategory.length" class="mt-6">
                    <div
                        class="relative mx-auto size-40 rounded-full"
                        :style="donutStyle"
                    >
                        <div
                            class="absolute inset-8 grid place-items-center rounded-full bg-card text-center"
                        >
                            <div>
                                <p class="text-[0.65rem] text-muted-foreground">
                                    Total spent
                                </p>
                                <p
                                    class="mt-1 text-sm font-semibold tabular-nums"
                                >
                                    {{ formatIDR(expenseTotal) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 space-y-2.5">
                        <div
                            v-for="(item, index) in expenseByCategory"
                            :key="item.category"
                            class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-3 text-xs"
                        >
                            <span
                                class="flex min-w-0 items-center gap-2 text-muted-foreground"
                                ><i
                                    class="size-2 shrink-0 rounded-full"
                                    :style="{
                                        backgroundColor: categoryColors[index],
                                    }"
                                /><span class="truncate">{{
                                    item.category
                                }}</span></span
                            ><span class="font-medium tabular-nums">{{
                                formatIDR(item.amount)
                            }}</span>
                        </div>
                    </div>
                </div>
                <div
                    v-else
                    class="mt-6 grid h-60 place-items-center rounded-xl border border-dashed border-border bg-muted/20 px-5 text-center"
                >
                    <div>
                        <ReceiptText
                            class="mx-auto size-6 text-muted-foreground/60"
                        />
                        <p class="mt-3 text-sm font-medium">
                            No expenses this month.
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Category insights will appear after a transaction.
                        </p>
                    </div>
                </div>
            </article>
        </section>

        <section class="grid gap-5 lg:grid-cols-2">
            <article
                class="rounded-2xl border border-border bg-card p-5 sm:p-6"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="editorial-kicker">Progress</p>
                        <h2 class="mt-1 font-semibold">Savings Goals</h2>
                    </div>
                    <PiggyBank class="size-5 text-primary" />
                </div>
                <div
                    v-if="savingsGoals.length"
                    class="mt-5 divide-y divide-border"
                >
                    <Link
                        v-for="goal in savingsGoals"
                        :key="goal.id"
                        :href="`/admin/finance/savings/${goal.id}`"
                        class="block py-4 first:pt-0 last:pb-0 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        ><div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">
                                    {{ goal.name }}
                                </p>
                                <p
                                    class="mt-1 text-xs text-muted-foreground tabular-nums"
                                >
                                    {{ formatIDR(goal.saved) }} /
                                    {{ formatIDR(goal.target) }}
                                </p>
                            </div>
                            <span class="text-sm font-semibold tabular-nums"
                                >{{ goal.progress }}%</span
                            >
                        </div>
                        <div
                            class="mt-3 h-1.5 overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full bg-primary transition-[width] duration-200"
                                :style="{ width: `${goal.progress}%` }"
                            /></div
                    ></Link>
                </div>
                <div
                    v-else
                    class="mt-5 rounded-xl border border-dashed border-border bg-muted/20 p-7 text-center"
                >
                    <p class="text-sm font-medium">No savings goals yet.</p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Create a goal to track your progress.
                    </p>
                </div>
            </article>
            <article
                class="rounded-2xl border border-border bg-card p-5 sm:p-6"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="editorial-kicker">Liabilities</p>
                        <h2 class="mt-1 font-semibold">Remaining Debt</h2>
                    </div>
                    <CircleDollarSign class="size-5 text-muted-foreground" />
                </div>
                <p
                    class="mt-4 text-2xl font-semibold tracking-tight tabular-nums"
                >
                    {{ formatIDR(debtSummary.total) }}
                </p>
                <div
                    v-if="debtSummary.items.length"
                    class="mt-5 divide-y divide-border"
                >
                    <Link
                        v-for="debt in debtSummary.items"
                        :key="debt.id"
                        :href="`/admin/finance/debts/${debt.id}`"
                        class="flex items-center gap-3 py-3 first:pt-0 last:pb-0 hover:text-primary"
                        ><span
                            class="grid size-8 shrink-0 place-items-center rounded-lg bg-muted"
                            ><Landmark
                                class="size-4 text-muted-foreground" /></span
                        ><span
                            class="min-w-0 flex-1 truncate text-sm font-medium"
                            >{{ debt.name }}</span
                        ><span class="text-right text-xs text-muted-foreground"
                            ><strong
                                class="block font-semibold text-foreground tabular-nums"
                                >{{ formatIDR(debt.remaining) }}</strong
                            >remaining</span
                        ></Link
                    >
                </div>
                <div
                    v-else
                    class="mt-5 rounded-xl border border-dashed border-border bg-muted/20 p-7 text-center"
                >
                    <p class="text-sm font-medium">No active debt.</p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Your active debt summary will appear here.
                    </p>
                </div>
            </article>
        </section>

        <section aria-labelledby="portfolio-overview">
            <div class="mb-4">
                <p class="editorial-kicker">Workspace</p>
                <h2 id="portfolio-overview" class="mt-1 text-lg font-semibold">
                    Portfolio Overview
                </h2>
            </div>
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <article
                    v-for="item in [
                        {
                            label: 'Total Projects',
                            value: projectSummary.total,
                        },
                        { label: 'Running', value: projectSummary.running },
                        {
                            label: 'Waiting / On Hold',
                            value:
                                projectSummary.waiting + projectSummary.on_hold,
                        },
                        { label: 'Completed', value: projectSummary.done },
                    ]"
                    :key="item.label"
                    class="rounded-xl border border-border bg-card p-4"
                >
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-xs text-muted-foreground">
                            {{ item.label }}
                        </p>
                        <FolderKanban class="size-4 text-muted-foreground/60" />
                    </div>
                    <p class="mt-3 text-2xl font-semibold tabular-nums">
                        {{ item.value }}
                    </p>
                </article>
            </div>
        </section>

        <section class="grid gap-5 xl:grid-cols-2">
            <article
                class="overflow-hidden rounded-2xl border border-border bg-card"
            >
                <div
                    class="flex items-center justify-between border-b border-border px-5 py-4"
                >
                    <h2 class="font-semibold">Recent Projects</h2>
                    <Link
                        href="/admin/projects"
                        class="flex items-center gap-1 text-xs font-semibold text-muted-foreground hover:text-foreground"
                        >View all projects <ArrowRight class="size-3.5"
                    /></Link>
                </div>
                <div
                    v-if="recentProjects.length"
                    class="divide-y divide-border"
                >
                    <Link
                        v-for="project in recentProjects"
                        :key="project.id"
                        :href="`/admin/projects/${project.id}/edit`"
                        class="flex items-center gap-3 px-5 py-4 transition-colors hover:bg-muted/35"
                        ><span
                            class="grid size-9 shrink-0 place-items-center rounded-lg bg-primary/8 text-primary"
                            ><FolderKanban class="size-4" /></span
                        ><span class="min-w-0 flex-1"
                            ><span class="block truncate text-sm font-medium">{{
                                project.title
                            }}</span
                            ><span
                                class="mt-0.5 block truncate text-xs text-muted-foreground"
                                >{{
                                    project.technologies.join(' · ') ||
                                    'No technology'
                                }}</span
                            ></span
                        ><span class="shrink-0 text-right"
                            ><span
                                class="block text-[0.65rem] font-semibold tracking-wide uppercase"
                                :class="
                                    project.status === 'running'
                                        ? 'text-blue-700'
                                        : project.status === 'done'
                                          ? 'text-emerald-700'
                                          : 'text-muted-foreground'
                                "
                                >{{ statusLabel(project.status) }}</span
                            ><span
                                class="mt-0.5 block text-[0.65rem] text-muted-foreground"
                                >{{ relativeDate(project.updated_at) }}</span
                            ></span
                        ></Link
                    >
                </div>
                <div v-else class="p-8 text-center">
                    <p class="text-sm font-medium">No projects yet.</p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        New portfolio work will appear here.
                    </p>
                </div>
            </article>
            <article
                class="overflow-hidden rounded-2xl border border-border bg-card"
            >
                <div
                    class="flex items-center justify-between border-b border-border px-5 py-4"
                >
                    <h2 class="font-semibold">Recent Transactions</h2>
                    <Link
                        href="/admin/finance/transactions"
                        class="flex items-center gap-1 text-xs font-semibold text-muted-foreground hover:text-foreground"
                        >View all transactions <ArrowRight class="size-3.5"
                    /></Link>
                </div>
                <div
                    v-if="recentTransactions.length"
                    class="divide-y divide-border"
                >
                    <div
                        v-for="item in recentTransactions"
                        :key="item.id"
                        class="flex items-center gap-3 px-5 py-4"
                    >
                        <span
                            class="grid size-9 shrink-0 place-items-center rounded-lg"
                            :class="
                                item.type === 'income'
                                    ? 'bg-emerald-50 text-emerald-700'
                                    : item.type === 'expense'
                                      ? 'bg-rose-50 text-rose-700'
                                      : 'bg-muted text-muted-foreground'
                            "
                            ><ArrowUpRight
                                v-if="item.type === 'income'"
                                class="size-4" /><ArrowDownRight
                                v-else-if="item.type === 'expense'"
                                class="size-4" /><ReceiptText
                                v-else
                                class="size-4" /></span
                        ><span class="min-w-0 flex-1"
                            ><span class="block truncate text-sm font-medium">{{
                                transactionTitle(item)
                            }}</span
                            ><span
                                class="mt-0.5 block truncate text-xs text-muted-foreground"
                                >{{ transactionMeta(item) }} ·
                                {{ formattedDate(item.transaction_date) }}</span
                            ></span
                        ><span
                            class="shrink-0 text-sm font-semibold tabular-nums"
                            :class="
                                item.type === 'income'
                                    ? 'text-emerald-700'
                                    : item.type === 'expense'
                                      ? 'text-rose-700'
                                      : 'text-foreground'
                            "
                            >{{
                                item.type === 'income'
                                    ? '+'
                                    : item.type === 'expense'
                                      ? '−'
                                      : ''
                            }}{{ formatIDR(item.amount) }}</span
                        >
                    </div>
                </div>
                <div v-else class="p-8 text-center">
                    <p class="text-sm font-medium">No transactions yet.</p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Your latest finance activity will appear here.
                    </p>
                </div>
            </article>
        </section>
    </div>
</template>
