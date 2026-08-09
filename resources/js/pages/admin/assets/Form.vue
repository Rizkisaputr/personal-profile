<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import CurrencyInput from '@/components/admin/CurrencyInput.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import InputError from '@/components/InputError.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
type A = {
    id: number;
    name: string;
    category: string;
    purchase_price: string | null;
    purchase_date: string | null;
    warranty_expiry: string | null;
    serial_number: string | null;
    status: string;
    notes: string | null;
};
const props = defineProps<{ asset?: A; categories: string[] }>();
const statusLabels: Record<string, string> = {
    active: 'Aktif',
    sold: 'Terjual',
    lost: 'Hilang',
    disposed: 'Dihapuskan',
};
const categoryLabels: Record<string, string> = {
    Computer: 'Komputer',
    Phone: 'Ponsel',
    Vehicle: 'Kendaraan',
    Electronics: 'Elektronik',
    Property: 'Properti',
    Digital: 'Digital',
    Other: 'Lainnya',
};
const form = useForm({
    name: props.asset?.name ?? '',
    category: props.asset?.category ?? 'Other',
    purchase_price: props.asset?.purchase_price ?? '',
    purchase_date: props.asset?.purchase_date?.slice(0, 10) ?? '',
    warranty_expiry: props.asset?.warranty_expiry?.slice(0, 10) ?? '',
    serial_number: props.asset?.serial_number ?? '',
    status: props.asset?.status ?? 'active',
    notes: props.asset?.notes ?? '',
});
function submit() {
    if (props.asset) {
form.put(`/admin/assets/${props.asset.id}`);
} else {
form.post('/admin/assets');
}
}
</script>
<template>
    <Head title="Asset" /><PageHeader
        eyebrow="Personal"
        :title="asset ? 'Edit asset' : 'Add asset'"
        description="Simple personal asset inventory without depreciation accounting."
    />
    <form
        class="surface-panel mt-8 grid gap-5 p-6 md:grid-cols-2"
        @submit.prevent="submit"
    >
        <label class="grid gap-2 text-sm"
            >Nama Aset<Input v-model="form.name" />
            <InputError :message="form.errors.name" /></label
        ><label class="grid gap-2 text-sm"
            >Kategori<select
                v-model="form.category"
                class="h-10 rounded-md border bg-background px-3"
            >
                <option
                    v-for="category in categories"
                    :key="category"
                    :value="category"
                >
                    {{ categoryLabels[category] ?? category }}
                </option>
            </select><InputError :message="form.errors.category" /></label
        ><label class="grid gap-2 text-sm"
            >Harga Beli<CurrencyInput
                v-model="form.purchase_price"
                placeholder="Contoh: 1.500.000" /><InputError :message="form.errors.purchase_price" /></label
        ><label class="grid gap-2 text-sm"
            >Tanggal Pembelian<Input
                v-model="form.purchase_date"
                type="date" /><InputError :message="form.errors.purchase_date" /></label
        ><label class="grid gap-2 text-sm"
            >Garansi Berakhir<Input
                v-model="form.warranty_expiry"
                type="date" /><InputError :message="form.errors.warranty_expiry" /></label
        ><label class="grid gap-2 text-sm"
            >Nomor Seri<Input v-model="form.serial_number" /><InputError :message="form.errors.serial_number" /></label
        ><label class="grid gap-2 text-sm"
            >Status<select
                v-model="form.status"
                class="h-10 rounded-md border bg-background px-3"
            >
                <option
                    v-for="status in ['active', 'sold', 'lost', 'disposed']"
                    :key="status"
                    :value="status"
                >
                    {{ statusLabels[status] }}
                </option>
            </select><InputError :message="form.errors.status" /></label
        ><label class="grid gap-2 text-sm md:col-span-2"
            >Catatan<textarea
                v-model="form.notes"
                class="min-h-24 rounded-md border bg-background p-3"
            />
            <InputError :message="form.errors.notes" />
        </label>
        <div class="flex gap-3 md:col-span-2">
            <Button type="submit" :disabled="form.processing">{{
                form.processing ? 'Menyimpan…' : 'Simpan'
            }}</Button
            ><Link
                href="/admin/assets"
                :class="buttonVariants({ variant: 'outline' })"
                >Cancel</Link
            >
        </div>
    </form>
</template>
