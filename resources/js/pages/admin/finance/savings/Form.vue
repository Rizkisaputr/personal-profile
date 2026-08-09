<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import CurrencyInput from '@/components/admin/CurrencyInput.vue';
import FormField from '@/components/admin/FormField.vue';
import FormTextarea from '@/components/admin/FormTextarea.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
type G = {
    id: number;
    name: string;
    target_amount: string;
    start_date: string | null;
    target_date: string | null;
    status: string;
    notes: string | null;
};
const p = defineProps<{ goal?: G }>();
const statusLabels: Record<string, string> = {
    active: 'Aktif',
    completed: 'Tercapai',
    paused: 'Dijeda',
    cancelled: 'Dibatalkan',
};
const f = useForm({
    name: p.goal?.name ?? '',
    target_amount: p.goal?.target_amount ?? '',
    start_date: p.goal?.start_date?.slice(0, 10) ?? '',
    target_date: p.goal?.target_date?.slice(0, 10) ?? '',
    status: p.goal?.status ?? 'active',
    notes: p.goal?.notes ?? '',
});
function submit() {
    if (p.goal) {
        f.put(`/admin/finance/savings/${p.goal.id}`);
    } else {
        f.post('/admin/finance/savings');
    }
}
</script>
<template>
    <Head title="Savings goal" /><PageHeader
        eyebrow="Finance"
        :title="goal ? 'Edit savings goal' : 'Add savings goal'"
        description="Set an overall target without automatic monthly carry-over."
    />
    <form
        class="surface-panel mt-8 grid gap-5 p-6 md:grid-cols-2"
        @submit.prevent="submit"
    >
        <FormField
            id="name"
            label="Nama Target"
            :error="f.errors.name"
            required
        >
            <Input
                id="name"
                v-model="f.name"
                placeholder="Masukkan nama target tabungan"
            />
        </FormField>
        <FormField
            id="target_amount"
            label="Target Nominal"
            :error="f.errors.target_amount"
            required
        >
            <CurrencyInput
                id="target_amount"
                v-model="f.target_amount"
                placeholder="Contoh: 10.000.000"
            />
        </FormField>
        <FormField
            id="start_date"
            label="Tanggal Mulai"
            :error="f.errors.start_date"
        >
            <Input id="start_date" v-model="f.start_date" type="date" />
        </FormField>
        <FormField
            id="target_date"
            label="Tanggal Target"
            :error="f.errors.target_date"
        >
            <Input id="target_date" v-model="f.target_date" type="date" />
        </FormField>
        <FormField
            id="status"
            label="Status"
            :error="f.errors.status"
            required
        >
            <select
                id="status"
                v-model="f.status"
                class="h-10 rounded-md border bg-background px-3"
            >
                <option
                    v-for="s in ['active', 'completed', 'paused', 'cancelled']"
                    :key="s"
                    :value="s"
                >
                    {{ statusLabels[s] }}
                </option>
            </select>
        </FormField>
        <div class="md:col-span-2">
            <FormField id="notes" label="Catatan" :error="f.errors.notes">
                <FormTextarea
                    id="notes"
                    v-model="f.notes"
                    :rows="4"
                    placeholder="Masukkan catatan"
                />
            </FormField>
        </div>
        <div class="flex gap-3 md:col-span-2">
            <Button type="submit" :disabled="f.processing">{{
                f.processing ? 'Menyimpan…' : 'Simpan'
            }}</Button
            ><Link
                href="/admin/finance/savings"
                :class="buttonVariants({ variant: 'outline' })"
                >Cancel</Link
            >
        </div>
    </form>
</template>
