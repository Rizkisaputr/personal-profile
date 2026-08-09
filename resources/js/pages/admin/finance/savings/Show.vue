<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import CurrencyInput from '@/components/admin/CurrencyInput.vue';
import FormField from '@/components/admin/FormField.vue';
import FormTextarea from '@/components/admin/FormTextarea.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatIDR } from '@/lib/money';
type Goal = {
    id: number;
    name: string;
    target_amount: string;
    saved_amount: string;
    remaining_amount: string;
    progress: number;
    current_month: {
        target: string;
        actual: string;
        remaining: string;
        progress: number;
    };
    previous_month: { target: string; actual: string; shortfall: string };
    monthly_targets: {
        id: number;
        year: number;
        month: number;
        target_amount: string;
    }[];
    entries: {
        id: number;
        type: string;
        amount: string;
        entry_date: string;
        account: { name: string };
    }[];
};
defineProps<{
    goal: Goal;
    accounts: { id: number; name: string }[];
}>();
const target = useForm({
    year: new Date().getFullYear(),
    month: new Date().getMonth() + 1,
    target_amount: '',
});
const entry = useForm({
    type: 'contribution',
    amount: '',
    entry_date: new Date().toISOString().slice(0, 10),
    account_id: '',
    notes: '',
});
</script>
<template>
    <Head :title="goal.name" /><PageHeader
        eyebrow="Finance · Savings"
        :title="goal.name"
        description="History-derived savings progress with independent monthly targets."
        ><template #actions
            ><Link
                :href="`/admin/finance/savings/${goal.id}/edit`"
                :class="buttonVariants({ variant: 'outline' })"
                >Edit</Link
            ></template
        ></PageHeader
    >
    <div class="mt-8 grid gap-4 sm:grid-cols-3">
        <div
            v-for="x in [
                ['Saved', goal.saved_amount],
                ['Target', goal.target_amount],
                ['Remaining', goal.remaining_amount],
            ]"
            :key="x[0]"
            class="surface-panel p-4"
        >
            <p class="text-xs text-muted-foreground">{{ x[0] }}</p>
            <p class="mt-2 font-semibold">{{ formatIDR(x[1]) }}</p>
        </div>
    </div>
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <form
            class="surface-panel p-5"
            @submit.prevent="
                target.post(`/admin/finance/savings/${goal.id}/targets`, {
                    onSuccess: () => target.reset('target_amount'),
                })
            "
        >
            <h2 class="font-semibold">Monthly target</h2>
            <div class="mt-4 grid gap-3">
                <Input v-model="target.year" type="number" /><Input
                    v-model="target.month"
                    type="number"
                    min="1"
                    max="12"
                /><CurrencyInput
                    v-model="target.target_amount"
                    placeholder="Contoh: 1.000.000"
                /><Button>Save target</Button>
            </div>
        </form>
        <form
            class="surface-panel p-5"
            @submit.prevent="
                entry.post(`/admin/finance/savings/${goal.id}/entries`, {
                    onSuccess: () => entry.reset('amount', 'notes'),
                })
            "
        >
            <h2 class="font-semibold">Tambah / Ambil Tabungan</h2>
            <p class="mt-1 text-xs leading-5 text-muted-foreground">
                Catat dana yang ditambahkan ke target atau diambil dari tabungan.
            </p>
            <div class="mt-4 grid gap-4">
                <FormField
                    id="entry_type"
                    label="Jenis Transaksi"
                    :error="entry.errors.type"
                    required
                >
                    <select
                        id="entry_type"
                        v-model="entry.type"
                        class="h-10 rounded-md border bg-background px-3"
                    >
                        <option value="contribution">Tambah Tabungan</option>
                        <option value="withdrawal">Ambil Tabungan</option>
                    </select>
                </FormField>
                <FormField
                    id="entry_amount"
                    label="Nominal"
                    :error="entry.errors.amount"
                    required
                >
                    <CurrencyInput
                        id="entry_amount"
                        v-model="entry.amount"
                        placeholder="Contoh: 500.000"
                    />
                </FormField>
                <FormField
                    id="entry_date"
                    label="Tanggal"
                    :error="entry.errors.entry_date"
                    required
                >
                    <Input
                        id="entry_date"
                        v-model="entry.entry_date"
                        type="date"
                    />
                </FormField>
                <FormField
                    id="entry_account"
                    label="Rekening"
                    :error="entry.errors.account_id"
                    required
                >
                    <select
                        id="entry_account"
                        v-model="entry.account_id"
                        class="h-10 rounded-md border bg-background px-3"
                    >
                        <option value="">Pilih rekening</option>
                        <option
                            v-for="a in accounts"
                            :key="a.id"
                            :value="a.id"
                        >
                            {{ a.name }}
                        </option>
                    </select>
                </FormField>
                <FormField
                    id="entry_notes"
                    label="Catatan"
                    :error="entry.errors.notes"
                >
                    <FormTextarea
                        id="entry_notes"
                        v-model="entry.notes"
                        :rows="3"
                        placeholder="Masukkan catatan"
                    />
                </FormField>
                <Button type="submit" :disabled="entry.processing">
                    {{
                        entry.processing
                            ? 'Menyimpan…'
                            : entry.type === 'contribution'
                              ? 'Tambah Tabungan'
                              : 'Ambil Tabungan'
                    }}
                </Button>
            </div>
        </form>
    </div>
    <div class="surface-panel mt-6 p-5">
        <h2 class="font-semibold">History</h2>
        <div
            v-for="e in goal.entries"
            :key="e.id"
            class="flex justify-between border-b py-3 text-sm"
        >
            <span class="capitalize">{{ e.type }} · {{ e.account.name }}</span
            ><span
                >{{ formatIDR(e.amount) }} ·
                {{ new Date(e.entry_date).toLocaleDateString('id-ID') }}</span
            >
        </div>
        <p class="mt-5 text-sm text-muted-foreground">
            Kekurangan target bulan sebelumnya:
            {{ formatIDR(goal.previous_month.shortfall) }}. Nilai ini tidak
            dipindahkan secara otomatis.
        </p>
    </div>
</template>
