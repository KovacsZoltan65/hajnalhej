<script setup>
import { Head, router, useForm } from "@inertiajs/vue3";
import { computed, reactive, ref } from "vue";
import Button from "primevue/button";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import DatePicker from "primevue/datepicker";
import InputText from "primevue/inputtext";
import Select from "primevue/select";

import AdminTableToolbar from "@/Components/Admin/AdminTableToolbar.vue";
import AdjustmentModal from "@/Components/Admin/Inventory/AdjustmentModal.vue";
import WasteEntryModal from "@/Components/Admin/Inventory/WasteEntryModal.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { trans } from "laravel-vue-i18n";

import { createDayOptions, perPageOptions } from "@/Utils/functions";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    dashboard: { type: Object, required: true },
    ledger: { type: Object, required: true },
    filters: { type: Object, required: true },
    movement_types: { type: Array, required: true },
    ingredient_options: { type: Array, required: true },
    product_options: { type: Array, required: true },
    waste_reasons: { type: Array, required: true },
});

const loading = ref(false);
const wasteModalVisible = ref(false);
const adjustmentModalVisible = ref(false);

const filterState = reactive({
    days: props.filters.days ?? 7,
    date_from: props.filters.date_from ?? "",
    date_to: props.filters.date_to ?? "",
    search: props.filters.search ?? "",
    movement_type: props.filters.movement_type ?? "",
    ingredient_id: props.filters.ingredient_id || null,
    per_page: props.filters.per_page ?? 15,
});

const dayOptions = createDayOptions(trans, [7, 14, 30, 90]);

import { trans } from "laravel-vue-i18n";
const perPageOptions = perPageOptions(trans, [15, 30, 50]);
/*
const perPageOptions = [
    { label: trans("common.page_count", { count: 15 }), value: 15 },
    { label: trans("common.page_count", { count: 30 }), value: 30 },
    { label: trans("common.page_count", { count: 50 }), value: 50 },
];
*/

const movementTypeOptions = computed(() => [
    { label: trans("common.all"), value: "" },
    ...props.movement_types.map((type) => ({
        label: movementTypeLabel(type),
        value: type,
    })),
]);

const ingredientOptions = computed(() =>
    props.ingredient_options.map((ingredient) => ({
        label: `${ingredient.name} (${ingredient.unit})`,
        value: ingredient.id,
        unit: ingredient.unit,
    }))
);
const productOptions = computed(() =>
    props.product_options.map((product) => ({
        label: `${product.name} (/${product.slug})`,
        value: product.id,
    }))
);

const ingredientFilterOptions = computed(() => [
    { label: trans("common.all"), value: null },
    ...ingredientOptions.value,
]);
const wasteReasonOptions = computed(() =>
    props.waste_reasons.map((reason) => ({ label: reason, value: reason }))
);

const wasteForm = useForm({
    waste_type: "ingredient",
    ingredient_id: null,
    product_id: null,
    quantity: 1,
    reason: trans("admin_inventory.waste_reason_expired"),
    occurred_at: new Date().toISOString().slice(0, 10),
});

const adjustmentForm = useForm({
    ingredient_id: null,
    difference: 0,
    unit_cost: null,
    occurred_at: new Date().toISOString().slice(0, 10),
    notes: "",
});

const currentPage = computed(() => props.ledger.current_page ?? 1);
const first = computed(
    () => (currentPage.value - 1) * (props.ledger.per_page ?? filterState.per_page)
);

const load = (extra = {}) => {
    loading.value = true;

    router.get(
        route("admin.inventory.index"),
        {
            days: filterState.days,
            date_from: filterState.date_from || undefined,
            date_to: filterState.date_to || undefined,
            search: filterState.search || undefined,
            movement_type: filterState.movement_type || undefined,
            ingredient_id: filterState.ingredient_id || undefined,
            per_page: filterState.per_page,
            ...extra,
        },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
            onFinish: () => {
                loading.value = false;
            },
        }
    );
};

const submitFilters = () => load({ page: 1 });

const clearFilters = () => {
    filterState.days = 7;
    filterState.date_from = "";
    filterState.date_to = "";
    filterState.search = "";
    filterState.movement_type = "";
    filterState.ingredient_id = null;
    filterState.per_page = 15;
    submitFilters();
};

const onPage = (event) => {
    filterState.per_page = event.rows;
    load({ page: event.page + 1, per_page: event.rows });
};

const openWasteModal = () => {
    wasteForm.reset();
    wasteForm.clearErrors();
    wasteForm.waste_type = "ingredient";
    wasteForm.ingredient_id = null;
    wasteForm.product_id = null;
    wasteForm.quantity = 1;
    wasteForm.reason = trans("admin_inventory.waste_reason_expired");
    wasteForm.occurred_at = new Date().toISOString().slice(0, 10);
    wasteModalVisible.value = true;
};

const openAdjustmentModal = () => {
    adjustmentForm.reset();
    adjustmentForm.clearErrors();
    adjustmentForm.ingredient_id = null;
    adjustmentForm.difference = 0;
    adjustmentForm.unit_cost = null;
    adjustmentForm.occurred_at = new Date().toISOString().slice(0, 10);
    adjustmentForm.notes = "";
    adjustmentModalVisible.value = true;
};

const submitWaste = () => {
    wasteForm.post(route("admin.inventory.waste.store"), {
        preserveScroll: true,
        onSuccess: () => {
            wasteModalVisible.value = false;
            wasteForm.reset();
        },
    });
};

const submitAdjustment = () => {
    adjustmentForm.post(route("admin.inventory.adjustments.store"), {
        preserveScroll: true,
        onSuccess: () => {
            adjustmentModalVisible.value = false;
            adjustmentForm.reset();
        },
    });
};

const asCurrency = (value) =>
    new Intl.NumberFormat(trans("common.locale"), {
        style: "currency",
        currency: trans("common.currency"),
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));

const movementTypeLabel = (type) => {
    const map = {
        purchase_in: trans("admin_inventory.movement_types.purchase_in"),
        production_out: trans("admin_inventory.movement_types.production_out"),
        waste_out: trans("admin_inventory.movement_types.waste_out"),
        adjustment_in: trans("admin_inventory.movement_types.adjustment_in"),
        adjustment_out: trans("admin_inventory.movement_types.adjustment_out"),
        count_correction: trans("admin_inventory.movement_types.count_correction"),
        return_in: trans("admin_inventory.movement_types.return_in"),
        return_out: trans("admin_inventory.movement_types.return_out"),
    };

    return map[type] ?? type;
};

const movementTypeClass = (type) => {
    const map = {
        purchase_in: "bg-emerald-100 text-emerald-800",
        production_out: "bg-amber-100 text-amber-800",
        waste_out: "bg-rose-100 text-rose-800",
        adjustment_in: "bg-blue-100 text-blue-800",
        adjustment_out: "bg-orange-100 text-orange-800",
        count_correction: "bg-violet-100 text-violet-800",
        return_in: "bg-cyan-100 text-cyan-800",
        return_out: "bg-pink-100 text-pink-800",
    };

    return map[type] ?? "bg-stone-100 text-stone-700";
};

const directionClass = (direction) =>
    direction === "out" ? "text-rose-700" : "text-emerald-700";

const parseDateFromYmd = (value) => {
    if (!value) {
        return null;
    }

    const [year, month, day] = String(value).split("-").map(Number);
    if (!year || !month || !day) {
        return null;
    }

    return new Date(year, month - 1, day);
};

const toYmd = (value) => {
    if (!(value instanceof Date) || Number.isNaN(value.getTime())) {
        return "";
    }

    const year = value.getFullYear();
    const month = String(value.getMonth() + 1).padStart(2, "0");
    const day = String(value.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
};

const dateFromPicker = computed({
    get: () => parseDateFromYmd(filterState.date_from),
    set: (value) => {
        filterState.date_from = toYmd(value);
    },
});

const dateToPicker = computed({
    get: () => parseDateFromYmd(filterState.date_to),
    set: (value) => {
        filterState.date_to = toYmd(value);
    },
});
</script>

<template>
    <Head :title="$t('admin_inventory.meta_title')" />

    <div class="space-y-6">
        <SectionTitle
            :eyebrow="$t('admin_inventory.eyebrow')"
            :title="$t('admin_inventory.title')"
            :description="$t('admin_inventory.description')"
        />

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <article class="ui-card p-4">
                <p class="text-xs uppercase text-bakery-dark/60">
                    {{ $t("admin_inventory.summary.total_stock_value") }}
                </p>
                <p class="mt-2 font-heading text-3xl">
                    {{ asCurrency(dashboard.summary.total_stock_value) }}
                </p>
            </article>
            <article class="ui-card p-4">
                <p class="text-xs uppercase text-bakery-dark/60">
                    {{ $t("admin_inventory.summary.low_stock_count") }}
                </p>
                <p class="mt-2 font-heading text-3xl">
                    {{ dashboard.summary.low_stock_count }}
                </p>
            </article>
            <article class="ui-card p-4">
                <p class="text-xs uppercase text-bakery-dark/60">
                    {{ $t("admin_inventory.summary.out_of_stock_count") }}
                </p>
                <p class="mt-2 font-heading text-3xl">
                    {{ dashboard.summary.out_of_stock_count }}
                </p>
            </article>
            <article class="ui-card p-4">
                <p class="text-xs uppercase text-bakery-dark/60">
                    {{ $t("admin_inventory.summary.weekly_waste_cost") }}
                </p>
                <p class="mt-2 font-heading text-3xl">
                    {{ asCurrency(dashboard.summary.weekly_waste_cost) }}
                </p>
            </article>
            <article class="ui-card p-4">
                <p class="text-xs uppercase text-bakery-dark/60">
                    {{ $t("admin_inventory.summary.weekly_purchase_value") }}
                </p>
                <p class="mt-2 font-heading text-3xl">
                    {{ asCurrency(dashboard.summary.weekly_purchase_value) }}
                </p>
            </article>
        </section>

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <AdminTableToolbar
                :stacked="true"
                filters-grid-class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4"
                actions-class="flex flex-wrap items-end gap-2 [&_.p-button]:shrink-0 [&_.p-button]:whitespace-nowrap"
            >
                <template #filters>
                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_inventory.filters.search") }}</label
                        >
                        <InputText
                            v-model="filterState.search"
                            class="w-full"
                            :placeholder="
                                $t('admin_inventory.filters.search_placeholder')
                            "
                            @keyup.enter="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("common.type") }}</label
                        >
                        <Select
                            v-model="filterState.movement_type"
                            :options="movementTypeOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_inventory.filters.ingredient") }}</label
                        >
                        <Select
                            v-model="filterState.ingredient_id"
                            :options="ingredientFilterOptions"
                            option-label="label"
                            option-value="value"
                            filter
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_inventory.filters.days") }}</label
                        >
                        <Select
                            v-model="filterState.days"
                            :options="dayOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>
                </template>

                <template #actions>
                    <div class="space-y-1 min-w-44">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_inventory.filters.date_from") }}</label
                        >
                        <DatePicker
                            v-model="dateFromPicker"
                            show-icon
                            date-format="yy.mm.dd"
                            class="w-full"
                            @update:model-value="submitFilters"
                        />
                    </div>
                    <div class="space-y-1 min-w-44">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_inventory.filters.date_to") }}</label
                        >
                        <DatePicker
                            v-model="dateToPicker"
                            show-icon
                            date-format="yy.mm.dd"
                            class="w-full"
                            @update:model-value="submitFilters"
                        />
                    </div>
                    <div class="space-y-1 min-w-36">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_inventory.filters.per_page") }}</label
                        >
                        <Select
                            v-model="filterState.per_page"
                            :options="perPageOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>
                    <Button
                        icon="pi pi-filter-slash"
                        :label="$t('common.clear_filters')"
                        severity="secondary"
                        outlined
                        @click="clearFilters"
                    />
                    <Button
                        icon="pi pi-search"
                        :label="$t('admin_inventory.actions.search')"
                        @click="submitFilters"
                    />
                    <Button
                        icon="pi pi-exclamation-triangle"
                        :label="$t('admin_inventory.actions.waste')"
                        severity="warn"
                        @click="openWasteModal"
                    />
                    <Button
                        icon="pi pi-sliders-h"
                        :label="$t('admin_inventory.actions.adjustment')"
                        @click="openAdjustmentModal"
                    />
                </template>
            </AdminTableToolbar>

            <div class="mt-4 overflow-x-auto">
                <DataTable
                    :value="ledger.data"
                    lazy
                    paginator
                    scrollable
                    :rows="ledger.per_page"
                    :first="first"
                    :total-records="ledger.total"
                    :loading="loading"
                    data-key="id"
                    @page="onPage"
                >
                    <template #empty>
                        <div
                            class="rounded-xl border border-dashed border-bakery-brown/25 bg-[#fcf7ef] p-6 text-center text-sm text-bakery-dark/70"
                        >
                            <p>{{ $t("admin_inventory.empty") }}</p>
                            <div
                                class="mt-3 flex flex-wrap items-center justify-center gap-2"
                            >
                                <Button
                                    :label="$t('common.clear_filters')"
                                    outlined
                                    size="small"
                                    @click="clearFilters"
                                />
                                <Button
                                    :label="$t('admin_inventory.actions.waste')"
                                    size="small"
                                    severity="warn"
                                    @click="openWasteModal"
                                />
                            </div>
                        </div>
                    </template>

                    <Column
                        field="occurred_at"
                        :header="$t('admin_inventory.columns.date')"
                    >
                        <template #body="{ data }">
                            <span class="text-sm text-bakery-dark">{{
                                data.occurred_at || "-"
                            }}</span>
                        </template>
                    </Column>
                    <Column
                        field="ingredient_name"
                        :header="$t('admin_inventory.columns.ingredient')"
                    >
                        <template #body="{ data }">
                            <div>
                                <p class="font-medium text-bakery-dark">
                                    {{ data.ingredient_name || "-" }}
                                </p>
                                <p class="text-xs text-bakery-dark/60">
                                    {{ data.ingredient_unit || "-" }}
                                </p>
                            </div>
                        </template>
                    </Column>
                    <Column
                        field="movement_type"
                        :header="$t('admin_inventory.columns.type')"
                    >
                        <template #body="{ data }">
                            <span
                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="movementTypeClass(data.movement_type)"
                            >
                                {{ movementTypeLabel(data.movement_type) }}
                            </span>
                        </template>
                    </Column>
                    <Column field="quantity" :header="$t('common.quantity')">
                        <template #body="{ data }">
                            <span
                                class="font-semibold"
                                :class="directionClass(data.direction)"
                            >
                                {{ data.direction === "out" ? "-" : "+"
                                }}{{ data.quantity }}
                            </span>
                        </template>
                    </Column>
                    <Column
                        field="unit_cost"
                        :header="$t('admin_inventory.columns.unit_cost')"
                    >
                        <template #body="{ data }">
                            <span>{{
                                data.unit_cost !== null ? asCurrency(data.unit_cost) : "-"
                            }}</span>
                        </template>
                    </Column>
                    <Column
                        field="total_cost"
                        :header="$t('admin_inventory.columns.value')"
                    >
                        <template #body="{ data }">
                            <span class="font-medium">{{
                                data.total_cost !== null
                                    ? asCurrency(data.total_cost)
                                    : "-"
                            }}</span>
                        </template>
                    </Column>
                    <Column
                        field="reference_type"
                        :header="$t('admin_inventory.columns.reference')"
                    >
                        <template #body="{ data }">
                            <span class="text-sm text-bakery-dark/80"
                                >{{ data.reference_type || "-" }} #{{
                                    data.reference_id || "-"
                                }}</span
                            >
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <WasteEntryModal
            v-model:visible="wasteModalVisible"
            :form="wasteForm"
            :ingredient-options="ingredientOptions"
            :product-options="productOptions"
            :waste-reasons="wasteReasonOptions"
            @submit="submitWaste"
        />

        <AdjustmentModal
            v-model:visible="adjustmentModalVisible"
            :form="adjustmentForm"
            :ingredient-options="ingredientOptions"
            @submit="submitAdjustment"
        />
    </div>
</template>
