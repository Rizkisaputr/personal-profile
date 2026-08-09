<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowDownRight,
    ArrowUpRight,
    CircleDollarSign,
    Clock3,
    FolderKanban,
    PiggyBank,
    WalletCards,
} from '@lucide/vue';
import { computed } from 'vue';
import ProfileAvatar from '@/components/admin/ProfileAvatar.vue';
import { formatIDR } from '@/lib/money';

type FinanceSummary = {
    total_balance: string;
    income: string;
    expense: string;
    savings: string;
    remaining_debt: string;
};
type Flow = { label: string; income: string; expense: string };
type Breakdown = { category: string; amount: string };
type Goal = {
    id: number;
    name: string;
    saved: string;
    target: string;
    remaining: string;
    progress: number;
    month: {
        target: string;
        actual: string;
        remaining: string;
        progress: number;
    };
    previous_month: { shortfall: string };
};
type Alert = {
    type: string;
    title: string;
    amount: string | null;
    due_date: string;
    days: number;
    severity: 'info' | 'warning' | 'urgent';
    href: string;
};
type Portfolio = {
    total: number;
    waiting: number;
    running: number;
    on_hold: number;
    done: number;
};
type Transaction = {
    id: number;
    type: string;
    transaction_date: string;
    description: string | null;
    amount: string;
    account?: { name: string } | null;
    from_account?: { name: string } | null;
    to_account?: { name: string } | null;
};
type Payment = {
    id: string;
    type: string;
    name: string;
    amount: string;
    due_date: string;
    href: string;
};
const props = defineProps<{
    financeSummary: FinanceSummary;
    cashFlow: Flow[];
    expenseBreakdown: Breakdown[];
    savingsGoals: Goal[];
    alerts: Alert[];
    portfolioSummary: Portfolio;
    recentTransactions: Transaction[];
    upcomingPayments: Payment[];
}>();
const page = usePage();
const identity = page.props.profileIdentity;
const moneyCards = computed(() => [
    {
        label: 'Total Saldo',
        value: props.financeSummary.total_balance,
        icon: WalletCards,
    },
    {
        label: 'Pemasukan Bulan Ini',
        value: props.financeSummary.income,
        icon: ArrowUpRight,
    },
    {
        label: 'Pengeluaran Bulan Ini',
        value: props.financeSummary.expense,
        icon: ArrowDownRight,
    },
    { label: 'Tabungan', value: props.financeSummary.savings, icon: PiggyBank },
    {
        label: 'Sisa Hutang',
        value: props.financeSummary.remaining_debt,
        icon: CircleDollarSign,
    },
]);
const chartMax = computed(() =>
    Math.max(
        1,
        ...props.cashFlow.flatMap((item) => [
            Number(item.income),
            Number(item.expense),
        ]),
    ),
);
const expenseTotal = computed(() =>
    props.expenseBreakdown.reduce((sum, item) => sum + Number(item.amount), 0),
);
const colors = [
    '#315cff',
    '#f59e0b',
    '#ef4444',
    '#10b981',
    '#8b5cf6',
    '#64748b',
];
const donutStyle = computed(() => {
    if (!expenseTotal.value) {
        return { background: '#eef0f3' };
    }

    let cursor = 0;
    const stops = props.expenseBreakdown.map((item, index) => {
        const start = cursor;
        cursor += (Number(item.amount) / expenseTotal.value) * 100;

        return `${colors[index % colors.length]} ${start}% ${cursor}%`;
    });

    return { background: `conic-gradient(${stops.join(',')})` };
});
function greeting() {
    const hour = new Date().getHours();

    return hour < 11
        ? 'Selamat pagi'
        : hour < 15
          ? 'Selamat siang'
          : hour < 18
            ? 'Selamat sore'
            : 'Selamat malam';
}
function firstName() {
    return (identity?.name || page.props.auth.user.name).split(/\s+/)[0];
}
function date(value: string) {
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(value));
}
function alertTiming(item: Alert) {
    if (item.days < 0) {
        return `terlambat ${Math.abs(item.days)} hari`;
    }

    if (item.days === 0) {
        return item.type === 'document'
            ? 'kedaluwarsa hari ini'
            : 'jatuh tempo hari ini';
    }

    return `dalam ${item.days} hari`;
}
function accountName(item: Transaction) {
    if (item.type === 'transfer') {
        return `${item.from_account?.name || 'Rekening'} → ${item.to_account?.name || 'Rekening'}`;
    }

    return item.account?.name || '—';
}

function severityLabel(severity: Alert['severity']): string {
    return {
        info: 'Informasi',
        warning: 'Perhatian',
        urgent: 'Penting',
    }[severity];
}

function paymentTypeLabel(type: string): string {
    return (
        {
            debt: 'Hutang',
            bill: 'Tagihan',
            subscription: 'Langganan',
        }[type] || type
    );
}
</script>

<template>
    <Head title="Pusat Kendali Pribadi" />
    <header class="flex items-center gap-4">
        <ProfileAvatar
            class="size-14"
            :name="identity?.name || page.props.auth.user.name"
            :image-url="identity?.image_url"
        />
        <div>
            <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">
                {{ greeting() }}, {{ firstName() }}
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Berikut ringkasan kondisi Anda hari ini.
            </p>
        </div>
    </header>

    <section class="mt-8">
        <div class="mb-4">
            <p class="editorial-kicker">Keuangan</p>
        </div>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            <article
                v-for="card in moneyCards"
                :key="card.label"
                class="surface-panel p-5"
            >
                <div class="flex justify-between gap-3">
                    <p class="text-sm text-muted-foreground">
                        {{ card.label }}
                    </p>
                    <component
                        :is="card.icon"
                        class="size-4 text-muted-foreground"
                    />
                </div>
                <p class="mt-5 text-xl font-semibold tabular-nums">
                    {{ formatIDR(card.value) }}
                </p>
            </article>
        </div>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-[1.6fr_1fr]">
        <article class="surface-panel p-5 sm:p-6">
            <div>
                <h2 class="font-semibold">Arus Kas — 6 Bulan Terakhir</h2>
                <p class="mt-1 text-xs text-muted-foreground">
                    Hanya pemasukan dan pengeluaran; transfer serta penyesuaian
                    tidak dihitung.
                </p>
            </div>
            <div
                v-if="
                    cashFlow.some(
                        (item) => Number(item.income) || Number(item.expense),
                    )
                "
                class="mt-7 flex h-64 items-end gap-3 border-b border-border px-2"
            >
                <div
                    v-for="item in cashFlow"
                    :key="item.label"
                    class="flex h-full flex-1 flex-col justify-end"
                >
                    <div class="flex flex-1 items-end justify-center gap-1.5">
                        <div
                            class="w-2/5 rounded-t bg-emerald-500/80"
                            :style="{
                                height: `${Math.max(2, (Number(item.income) / chartMax) * 100)}%`,
                            }"
                            :title="`Pemasukan ${formatIDR(item.income)}`"
                        />
                        <div
                            class="w-2/5 rounded-t bg-rose-400/80"
                            :style="{
                                height: `${Math.max(2, (Number(item.expense) / chartMax) * 100)}%`,
                            }"
                            :title="`Pengeluaran ${formatIDR(item.expense)}`"
                        />
                    </div>
                    <p class="py-3 text-center text-xs text-muted-foreground">
                        {{ item.label }}
                    </p>
                </div>
            </div>
            <p
                v-else
                class="grid h-64 place-items-center text-sm text-muted-foreground"
            >
                Belum ada data arus kas dalam enam bulan terakhir.
            </p>
            <div class="mt-3 flex gap-4 text-xs text-muted-foreground">
                <span class="flex items-center gap-2"
                    ><i
                        class="size-2 rounded-full bg-emerald-500"
                    />Pemasukan</span
                ><span class="flex items-center gap-2"
                    ><i
                        class="size-2 rounded-full bg-rose-400"
                    />Pengeluaran</span
                >
            </div>
        </article>
        <article class="surface-panel p-5 sm:p-6">
            <h2 class="font-semibold">Pengeluaran Berdasarkan Kategori</h2>
            <p class="mt-1 text-xs text-muted-foreground">Bulan berjalan</p>
            <div
                v-if="expenseBreakdown.length"
                class="mt-6 flex flex-col items-center gap-6 sm:flex-row sm:items-start xl:flex-col xl:items-center"
            >
                <div
                    class="relative size-44 shrink-0 rounded-full"
                    :style="donutStyle"
                >
                    <div
                        class="absolute inset-9 flex flex-col items-center justify-center rounded-full bg-card px-1 text-center"
                    >
                        <span class="text-[11px] text-muted-foreground"
                            >Total</span
                        ><strong
                            class="mt-0.5 max-w-full text-[11px] leading-4 font-semibold whitespace-nowrap tabular-nums"
                            :title="formatIDR(expenseTotal)"
                            >{{
                            formatIDR(expenseTotal)
                        }}</strong
                        >
                    </div>
                </div>
                <div class="w-full min-w-0 divide-y divide-border">
                    <div
                        v-for="(item, index) in expenseBreakdown"
                        :key="item.category"
                        class="grid min-w-0 grid-cols-[minmax(0,1fr)_auto] items-center gap-4 py-2.5 text-xs first:pt-0 last:pb-0"
                    >
                        <span
                            class="flex min-w-0 items-center gap-2 text-muted-foreground"
                            ><i
                                class="size-2 shrink-0 rounded-full"
                                :style="{
                                    backgroundColor:
                                        colors[index % colors.length],
                                }"
                            /><span class="truncate" :title="item.category">{{
                                item.category
                            }}</span></span
                        ><span
                            class="shrink-0 text-right font-medium whitespace-nowrap tabular-nums"
                            >{{
                            formatIDR(item.amount)
                        }}</span
                        >
                    </div>
                </div>
            </div>
            <p
                v-else
                class="grid h-64 place-items-center text-sm text-muted-foreground"
            >
                Belum ada pengeluaran bulan ini.
            </p>
        </article>
    </section>

    <section class="mt-6">
        <h2 class="mb-4 text-lg font-semibold">Progres Tabungan</h2>
        <div v-if="savingsGoals.length" class="grid gap-4 lg:grid-cols-2">
            <article
                v-for="goal in savingsGoals"
                :key="goal.id"
                class="surface-panel p-5"
            >
                <div class="flex justify-between gap-4">
                    <div>
                        <Link
                            :href="`/admin/finance/savings/${goal.id}`"
                            class="font-semibold hover:underline"
                            >{{ goal.name }}</Link
                        >
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ formatIDR(goal.saved) }} /
                            {{ formatIDR(goal.target) }}
                        </p>
                    </div>
                    <strong>{{ goal.progress }}%</strong>
                </div>
                <div class="mt-4 h-2 overflow-hidden rounded-full bg-muted">
                    <div
                        class="h-full rounded-full bg-[#315cff]"
                        :style="{ width: `${goal.progress}%` }"
                    />
                </div>
                <div class="mt-5 flex justify-between text-xs">
                    <span class="text-muted-foreground">Bulan berjalan</span
                    ><span
                        >{{ formatIDR(goal.month.actual) }} /
                        {{ formatIDR(goal.month.target) }} ·
                        {{ goal.month.progress }}%</span
                    >
                </div>
                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-muted">
                    <div
                        class="h-full bg-sky-400"
                        :style="{ width: `${goal.month.progress}%` }"
                    />
                </div>
                <p
                    v-if="Number(goal.previous_month.shortfall) > 0"
                    class="mt-3 text-xs text-amber-700"
                >
                    Kekurangan target bulan sebelumnya:
                    {{ formatIDR(goal.previous_month.shortfall) }}. Tidak dibawa
                    ke bulan ini.
                </p>
            </article>
        </div>
        <p
            v-else
            class="surface-panel p-8 text-center text-sm text-muted-foreground"
        >
            Belum ada target tabungan aktif.
        </p>
    </section>

    <section class="mt-6">
        <div class="mb-4 flex items-center gap-2">
            <AlertTriangle class="size-5" />
            <h2 class="text-lg font-semibold">Informasi Penting</h2>
        </div>
        <div
            v-if="alerts.length"
            class="surface-panel divide-y divide-border overflow-hidden"
        >
            <Link
                v-for="item in alerts"
                :key="`${item.type}-${item.title}-${item.due_date}`"
                :href="item.href"
                class="flex gap-4 p-4 transition-colors hover:bg-muted/30"
                ><span
                    class="mt-1 size-2.5 shrink-0 rounded-full"
                    :class="
                        item.severity === 'urgent'
                            ? 'bg-red-500'
                            : item.severity === 'warning'
                              ? 'bg-amber-500'
                              : 'bg-sky-500'
                    "
                />
                <div class="min-w-0">
                    <p class="text-sm font-medium">
                        {{ item.title
                        }}<span v-if="item.amount">
                            · {{ formatIDR(item.amount) }}</span
                        >
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        {{
                            item.type === 'document'
                                ? 'Kedaluwarsa'
                                : 'Jatuh tempo'
                        }}
                        {{ date(item.due_date) }} · {{ alertTiming(item) }}
                    </p>
                </div>
                <span
                    class="ml-auto text-[0.65rem] font-semibold uppercase"
                    :class="
                        item.severity === 'urgent'
                            ? 'text-red-600'
                            : item.severity === 'warning'
                              ? 'text-amber-700'
                              : 'text-sky-700'
                    "
                    >{{ severityLabel(item.severity) }}</span
                ></Link
            >
        </div>
        <p
            v-else
            class="surface-panel p-8 text-center text-sm text-muted-foreground"
        >
            Tidak ada hal yang memerlukan perhatian dalam 30 hari ke depan.
        </p>
    </section>

    <section class="mt-6">
        <div class="mb-4 flex items-center gap-2">
            <FolderKanban class="size-5" />
            <h2 class="text-lg font-semibold">Ringkasan Portofolio</h2>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
            <article
                v-for="item in [
                    { label: 'Total Proyek', value: portfolioSummary.total },
                    { label: 'Menunggu', value: portfolioSummary.waiting },
                    { label: 'Berjalan', value: portfolioSummary.running },
                    { label: 'Ditunda', value: portfolioSummary.on_hold },
                    { label: 'Selesai', value: portfolioSummary.done },
                ]"
                :key="item.label"
                class="surface-panel p-4"
            >
                <p class="text-xs text-muted-foreground">{{ item.label }}</p>
                <p class="mt-3 text-2xl font-semibold">{{ item.value }}</p>
            </article>
        </div>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-2">
        <article class="surface-panel overflow-hidden">
            <div class="border-b p-5">
                <h2 class="font-semibold">Transaksi Terbaru</h2>
            </div>
            <div v-if="recentTransactions.length" class="divide-y">
                <div
                    v-for="item in recentTransactions"
                    :key="item.id"
                    class="flex items-center gap-4 p-4"
                >
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium">
                            {{ item.description || item.type }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ date(item.transaction_date) }} ·
                            {{ accountName(item) }}
                        </p>
                    </div>
                    <p
                        class="text-sm font-semibold tabular-nums"
                        :class="
                            item.type === 'income'
                                ? 'text-emerald-600'
                                : item.type === 'expense'
                                  ? 'text-rose-600'
                                  : 'text-muted-foreground'
                        "
                    >
                        {{
                            item.type === 'income'
                                ? '+ '
                                : item.type === 'expense'
                                  ? '- '
                                  : ''
                        }}{{ formatIDR(item.amount) }}
                    </p>
                </div>
            </div>
            <p v-else class="p-8 text-center text-sm text-muted-foreground">
                Belum ada transaksi.
            </p>
        </article>
        <article class="surface-panel overflow-hidden">
            <div class="flex items-center gap-2 border-b p-5">
                <Clock3 class="size-4" />
                <h2 class="font-semibold">Pembayaran Mendatang</h2>
            </div>
            <div v-if="upcomingPayments.length" class="divide-y">
                <Link
                    v-for="item in upcomingPayments"
                    :key="item.id"
                    :href="item.href"
                    class="flex items-center gap-4 p-4 hover:bg-muted/30"
                    ><div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium">
                            {{ item.name }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ paymentTypeLabel(item.type) }} ·
                            {{ date(item.due_date) }}
                        </p>
                    </div>
                    <p class="text-sm font-semibold tabular-nums">
                        {{ formatIDR(item.amount) }}
                    </p></Link
                >
            </div>
            <p v-else class="p-8 text-center text-sm text-muted-foreground">
                Belum ada pembayaran mendatang.
            </p>
        </article>
    </section>
</template>
