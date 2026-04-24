<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import PurchaseForm from '@/Components/Admin/Purchases/PurchaseForm.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    purchase: { type: Object, required: true },
    suppliers: { type: Array, required: true },
    ingredient_options: { type: Array, required: true },
});

const ingredientOptions = computed(() =>
    props.ingredient_options.map((ingredient) => ({
        label: `${ingredient.name} (${ingredient.unit})`,
        value: ingredient.id,
        unit: ingredient.unit,
    })),
);

const form = useForm({
    supplier_id: props.purchase.supplier_id,
    reference_number: props.purchase.reference_number || '',
    purchase_date: props.purchase.purchase_date,
    notes: props.purchase.notes || '',
    items: props.purchase.items.map((item) => ({
        ingredient_id: item.ingredient_id,
        quantity: item.quantity,
        unit: item.unit,
        unit_cost: item.unit_cost,
    })),
});

const submitUpdate = () => {
    form.put(`/admin/purchases/${props.purchase.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Beszerzés #${purchase.id}`" />

    <section class="space-y-6">
        <div class="ui-card p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <h1 class="font-heading text-2xl text-bakery-dark">Beszerzés #{{ purchase.id }}</h1>
                <Link href="/admin/purchases" class="text-sm underline">Vissza</Link>
            </div>
            <div class="mt-4 grid gap-3 md:grid-cols-3 text-sm">
                <p><strong>Beszállító:</strong> {{ purchase.supplier_name || '-' }}</p>
                <p><strong>Referencia:</strong> {{ purchase.reference_number || '-' }}</p>
                <p><strong>Dátum:</strong> {{ purchase.purchase_date }}</p>
                <p><strong>Státusz:</strong> {{ purchase.status }}</p>
                <p><strong>Könyvelt:</strong> {{ purchase.posted_at || '-' }}</p>
                <p><strong>Összesen:</strong> {{ new Intl.NumberFormat('hu-HU').format(purchase.total) }} Ft</p>
            </div>
            <p v-if="purchase.notes" class="mt-3 text-sm text-bakery-dark/75">{{ purchase.notes }}</p>
        </div>

        <div v-if="purchase.status === 'draft'" class="ui-card p-4 sm:p-5">
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">Tervezet szerkesztése</h2>
                    <p class="mt-1 text-sm text-bakery-dark/65">A generált tételek könyvelés előtt módosíthatók.</p>
                </div>
                <Button
                    label="Tervezet mentése"
                    icon="pi pi-save"
                    class="!min-h-11"
                    :loading="form.processing"
                    @click="submitUpdate"
                />
            </div>
            <PurchaseForm :form="form" :suppliers="suppliers" :ingredient-options="ingredientOptions" />
        </div>

        <div class="ui-card p-4 sm:p-5 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60">
                    <tr>
                        <th class="px-2 py-2">Alapanyag</th>
                        <th class="px-2 py-2 text-right">Mennyiség</th>
                        <th class="px-2 py-2 text-right">Egységár</th>
                        <th class="px-2 py-2 text-right">Összesen</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in purchase.items" :key="item.id" class="border-b border-bakery-brown/10">
                        <td class="px-2 py-2">{{ item.ingredient_name }}</td>
                        <td class="px-2 py-2 text-right">{{ item.quantity }} {{ item.unit }}</td>
                        <td class="px-2 py-2 text-right">{{ new Intl.NumberFormat('hu-HU').format(item.unit_cost) }} Ft</td>
                        <td class="px-2 py-2 text-right">{{ new Intl.NumberFormat('hu-HU').format(item.line_total) }} Ft</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
