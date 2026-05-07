<script setup>
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import Button from "primevue/button";
import PurchaseForm from "@/Components/Admin/Purchases/PurchaseForm.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useLocaleFormat } from "@/composables/useLocaleFormat";

defineOptions({ layout: AdminLayout });

const { formatCurrency, formatQuantity } = useLocaleFormat();

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
    }))
);

const form = useForm({
    supplier_id: props.purchase.supplier_id,
    reference_number: props.purchase.reference_number || "",
    purchase_date: props.purchase.purchase_date,
    notes: props.purchase.notes || "",
    items: props.purchase.items.map((item) => ({
        ingredient_id: item.ingredient_id,
        quantity: item.quantity,
        unit: item.unit,
        unit_cost: item.unit_cost,
    })),
});

const submitUpdate = () => {
    form.put(route("admin.purchases.update", props.purchase.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="$t('admin_procurement.title', { id: purchase.id })" />

    <section class="space-y-6">
        <div class="ui-card p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <h1 class="font-heading text-2xl text-bakery-dark">
                    {{ $t("admin_procurement.title", { id: purchase.id }) }}
                </h1>
                <Link :href="route('admin.purchases.index')" class="text-sm underline">{{
                    $t("common.previous")
                }}</Link>
            </div>
            <div class="mt-4 grid gap-3 md:grid-cols-3 text-sm">
                <p>
                    <strong>{{ $t("common.supplier") }}:</strong>
                    {{ purchase.supplier_name || "-" }}
                </p>
                <p>
                    <strong>{{ $t("common.reference") }}:</strong>
                    {{ purchase.reference_number || "-" }}
                </p>
                <p>
                    <strong>{{ $t("common.date") }}:</strong> {{ purchase.purchase_date }}
                </p>
                <p>
                    <strong>{{ $t("common.status") }}:</strong> {{ purchase.status }}
                </p>
                <p>
                    <strong>{{ $t("admin_purchases.booked") }}:</strong>
                    {{ purchase.posted_at || "-" }}
                </p>
                <p>
                    <strong>{{ $t("common.total") }}:</strong>
                    {{ formatCurrency(purchase.total) }}
                </p>
            </div>
            <p v-if="purchase.notes" class="mt-3 text-sm text-bakery-dark/75">
                {{ purchase.notes }}
            </p>
        </div>

        <div v-if="purchase.status === 'draft'" class="ui-card p-4 sm:p-5">
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">
                        {{ $t("common.edit_draft") }}
                    </h2>
                    <p class="mt-1 text-sm text-bakery-dark/65">{{ $t("common.items_modified_before_posting") }}.</p>
                </div>
                <Button
                    :label="$t('common.save_draft')"
                    icon="pi pi-save"
                    class="min-h-11!"
                    :loading="form.processing"
                    @click="submitUpdate"
                />
            </div>
            <PurchaseForm :form="form" :suppliers="suppliers" :ingredient-options="ingredientOptions" />
        </div>

        <div class="ui-card p-4 sm:p-5 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead
                    class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-widest text-bakery-dark/60"
                >
                    <tr>
                        <th class="px-2 py-2">{{ $t("common.ingredient") }}</th>
                        <th class="px-2 py-2 text-right">{{ $t("common.quantity") }}</th>
                        <th class="px-2 py-2 text-right">{{ $t("common.unit_cost") }}</th>
                        <th class="px-2 py-2 text-right">{{ $t("common.total") }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in purchase.items" :key="item.id" class="border-b border-bakery-brown/10">
                        <td class="px-2 py-2">{{ item.ingredient_name }}</td>
                        <td class="px-2 py-2 text-right">
                            {{ formatQuantity(item.quantity, item.unit) }}
                        </td>
                        <td class="px-2 py-2 text-right">
                            {{ formatCurrency(item.unit_cost) }}
                        </td>
                        <td class="px-2 py-2 text-right">
                            {{ formatCurrency(item.line_total) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
