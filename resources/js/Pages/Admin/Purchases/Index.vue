<script setup>
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import Button from "primevue/button";
import Column from "primevue/column";
import ConfirmDialog from "primevue/confirmdialog";
import DataTable from "primevue/datatable";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import { useConfirm } from "primevue/useconfirm";

import AdminTableToolbar from "@/Components/Admin/AdminTableToolbar.vue";
import CreateModal from "@/Components/Admin/Purchases/CreateModal.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { pageOptions as createPerPageOptions } from "@/Utils/functions.js";
import { trans } from "laravel-vue-i18n";
import { useAdminFilterState } from "@/composables/useAdminFilterState.js";
import { useLocaleFormat } from "@/composables/useLocaleFormat";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    purchases: {
        type: Object,
        required: true,
    },
    suppliers: {
        type: Array,
        required: true,
    },
    ingredient_options: {
        type: Array,
        required: true,
    },
    statuses: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const confirm = useConfirm();
const loading = ref(false);
const createModalVisible = ref(false);

const { filterState, sortOrder, load, submitFilters, clearFilters, onSort, onPage } = useAdminFilterState({
    filters: props.filters,
    defaults: {
        search: "",
        status: "",
        supplier_id: null,
        sort_field: "purchase_date",
        sort_direction: "desc",
        per_page: 10,
    },
    routeName: "admin.purchases.index",
    loading,
    toQuery: (state) => ({
        search: state.search || undefined,
        status: state.status || undefined,
        supplier_id: state.supplier_id || undefined,
        sort_field: state.sort_field,
        sort_direction: state.sort_direction,
        per_page: state.per_page,
    }),
});

const perPageOptions = createPerPageOptions(trans, [10, 20, 50]);

const statusOptions = computed(() => [
    { label: "Mind", value: "" },
    ...props.statuses.map((status) => ({
        label: statusLabel(status),
        value: status,
    })),
]);

const supplierOptions = computed(() => [{ id: null, name: "Mind" }, ...props.suppliers]);
const ingredientOptions = computed(() =>
    props.ingredient_options.map((ingredient) => ({
        label: `${ingredient.name} (${ingredient.unit})`,
        value: ingredient.id,
        unit: ingredient.unit,
    }))
);

const newItem = () => ({ ingredient_id: null, quantity: 1, unit: "db", unit_cost: 0 });

const form = useForm({
    supplier_id: null,
    reference_number: "",
    purchase_date: new Date().toISOString().slice(0, 10),
    notes: "",
    items: [newItem()],
});

const currentPage = computed(() => props.purchases.current_page ?? 1);
const first = computed(() => (currentPage.value - 1) * (props.purchases.per_page ?? 10));

const openCreate = () => {
    form.reset();
    form.clearErrors();
    form.supplier_id = null;
    form.reference_number = "";
    form.purchase_date = new Date().toISOString().slice(0, 10);
    form.notes = "";
    form.items = [newItem()];
    createModalVisible.value = true;
};

const closeCreateModal = () => {
    createModalVisible.value = false;
};

const submitCreate = () => {
    form.post(route("admin.purchases.store"), {
        preserveScroll: true,
        onSuccess: () => {
            closeCreateModal();
            form.reset();
        },
    });
};

const postNow = (purchase) => {
    const ref = purchase.reference_number || `#${purchase.id}`;

    confirm.require({
        header: trans("admin_purchases.header"),
        message: trans("admin_purchases.message", { id: ref }),
        rejectLabel: trans("common.cancel"),
        acceptLabel: trans("admin_purchases.accounting"),
        accept: () => {
            router.post(route("admin.purchases.post", purchase.id), {}, { preserveScroll: true });
        },
    });
};

const cancelPurchase = (purchase) => {
    const ret = purchase.reference_number || `#${purchase.id}`;

    confirm.require({
        header: trans("admin_purchases.cancel_purchase"),
        message: trans("admin_purchases.cancel_purchase_message", { id: ref }),
        rejectLabel: trans("common.cancel"),
        acceptLabel: trans("admin_purchases.storno"),
        acceptClass: "p-button-danger",
        accept: () => {
            router.post(route("admin.purchases.cancel", purchase.id), {}, { preserveScroll: true });
        },
    });
};

const { formatCurrency } = useLocaleFormat();

const statusLabel = (status) => {
    const map = {
        draft: trans("common.draft"),
        posted: trans("admin_purchases.booked"),
        cancelled: trans("admin_purchases.canceled"),
    };

    return map[status] ?? status;
};

const statusClass = (status) => {
    const map = {
        draft: "bg-amber-100 text-amber-800",
        posted: "bg-emerald-100 text-emerald-800",
        cancelled: "bg-rose-100 text-rose-800",
    };

    return map[status] ?? "bg-stone-100 text-stone-700";
};
</script>

<template>
    <Head :title="$t('admin_procurements.title')" />

    <div class="space-y-6">
        <SectionTitle
            :eyebrow="$t('admin_procurements.eyebrow')"
            :title="$t('admin_procurements.title')"
            :description="$t('admin_procurements.description')"
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <AdminTableToolbar>
                <template #filters>
                    <!-- KERESÉS -->
                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">{{
                            $t("common.search")
                        }}</label>
                        <InputText
                            v-model="filterState.search"
                            class="w-full"
                            :placeholder="$t('admin_procurements.reference_or_comment')"
                            @keyup.enter="submitFilters"
                        />
                    </div>

                    <!-- ÁLLAPOT -->
                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">{{
                            $t("common.status")
                        }}</label>
                        <Select
                            v-model="filterState.status"
                            :options="statusOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>

                    <!-- SZÁLLÍTÓ -->
                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">{{
                            $t("common.supplier")
                        }}</label>
                        <Select
                            v-model="filterState.supplier_id"
                            :options="supplierOptions"
                            option-label="name"
                            option-value="id"
                            class="w-full"
                            filter
                            @change="submitFilters"
                        />
                    </div>

                    <!-- SOR / OLDAL -->
                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">{{
                            $t("table.rows_per_page")
                        }}</label>
                        <Select
                            v-model="filterState.per_page"
                            :options="perPageOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>
                </template>

                <template #actions>
                    <Button
                        icon="pi pi-filter-slash"
                        :label="$t('common.clear_filters')"
                        severity="secondary"
                        outlined
                        @click="clearFilters"
                    />
                    <Button icon="pi pi-search" :label="$t('common.search')" @click="submitFilters" />
                    <Button icon="pi pi-plus" :label="$t('admin_procurements.create')" @click="openCreate" />
                </template>
            </AdminTableToolbar>

            <div class="mt-4 overflow-x-auto">
                <DataTable
                    :value="purchases.data"
                    lazy
                    paginator
                    scrollable
                    :rows="purchases.per_page"
                    :first="first"
                    :total-records="purchases.total"
                    :loading="loading"
                    data-key="id"
                    sort-mode="single"
                    :sort-field="filterState.sort_field"
                    :sort-order="sortOrder"
                    @sort="onSort"
                    @page="onPage"
                >
                    <template #empty>
                        <div
                            class="rounded-xl border border-dashed border-bakery-brown/25 bg-[#fcf7ef] p-6 text-center text-sm text-bakery-dark/70"
                        >
                            <p>{{ $t("admin_procurements.empty") }}.</p>
                            <div class="mt-3 flex flex-wrap items-center justify-center gap-2">
                                <Button
                                    :label="$t('common.clear_filters')"
                                    outlined
                                    size="small"
                                    @click="clearFilters"
                                />
                                <Button :label="$t('admin_procurements.create')" size="small" @click="openCreate" />
                            </div>
                        </div>
                    </template>

                    <Column field="purchase_date" :header="$t('common.date')" sortable>
                        <template #body="{ data }">
                            <span class="text-sm text-bakery-dark">{{ data.purchase_date || "-" }}</span>
                        </template>
                    </Column>
                    <Column field="reference_number" :header="$t('common.reference')" sortable>
                        <template #body="{ data }">
                            <span class="font-medium text-bakery-dark">{{
                                data.reference_number || `#${data.id}`
                            }}</span>
                        </template>
                    </Column>
                    <Column field="supplier_name" :header="$t('common.supplier')">
                        <template #body="{ data }">
                            <span>{{ data.supplier_name || $t("common.not_specified") }}</span>
                        </template>
                    </Column>
                    <Column field="items_count" :header="$t('common.items')" />
                    <Column field="total" :header="$t('common.total')" sortable>
                        <template #body="{ data }">
                            <span class="font-medium">{{ formatCurrency(data.total) }}</span>
                        </template>
                    </Column>
                    <Column field="status" :header="$t('common.status')" sortable>
                        <template #body="{ data }">
                            <span
                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="statusClass(data.status)"
                            >
                                {{ statusLabel(data.status) }}
                            </span>
                        </template>
                    </Column>
                    <Column :header="$t('common.actions')" :exportable="false">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Link
                                    :href="route('admin.purchases.show', data.id)"
                                    class="inline-flex min-h-11 items-center rounded-md border border-bakery-brown/20 px-3 py-2 text-xs font-medium text-bakery-brown hover:bg-bakery-brown/10"
                                >
                                    Részletek
                                </Link>
                                <Button
                                    v-if="data.status === 'draft'"
                                    icon="pi pi-check"
                                    text
                                    rounded
                                    class="h-11! w-11!"
                                    :aria-label="$t('admin_purchases.header')"
                                    @click="postNow(data)"
                                />
                                <Button
                                    v-if="data.status === 'draft'"
                                    icon="pi pi-times"
                                    text
                                    rounded
                                    severity="danger"
                                    class="h-11! w-11!"
                                    :aria-label="$t('admin_purchases.cancel_purchase')"
                                    @click="cancelPurchase(data)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <CreateModal
            v-model:visible="createModalVisible"
            :form="form"
            :suppliers="suppliers"
            :ingredient-options="ingredientOptions"
            @submit="submitCreate"
        />
        <ConfirmDialog />
    </div>
</template>
