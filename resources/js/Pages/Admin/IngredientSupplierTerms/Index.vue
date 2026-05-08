<script setup>
import { Head, router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import Button from "primevue/button";
import Column from "primevue/column";
import ConfirmDialog from "primevue/confirmdialog";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import { useConfirm } from "primevue/useconfirm";
import AdminTableToolbar from "@/Components/Admin/AdminTableToolbar.vue";
import BaseDataTable from "@/Components/Admin/Table/BaseDataTable.vue";
import InlineEditableNumber from "@/Components/Admin/Table/InlineEditableNumber.vue";
import InlineEditableToggle from "@/Components/Admin/Table/InlineEditableToggle.vue";
import CreateModal from "@/Components/Admin/IngredientSupplierTerms/CreateModal.vue";
import EditModal from "@/Components/Admin/IngredientSupplierTerms/EditModal.vue";
import PreferredBadge from "@/Components/Admin/IngredientSupplierTerms/PreferredBadge.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { trans } from "laravel-vue-i18n";
import { useAdminFilterState } from "@/composables/useAdminFilterState.js";
import { useLocaleFormat } from "@/composables/useLocaleFormat";
import { pageOptions as createPerPageOptions, activeOptions as createActiveOptions } from "@/Utils/functions.js";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    terms: { type: Object, required: true },
    filters: { type: Object, required: true },
    ingredients: { type: Array, required: true },
    suppliers: { type: Array, required: true },
});

const confirm = useConfirm();
const loading = ref(false);
const createModalVisible = ref(false);
const editModalVisible = ref(false);
const editingId = ref(null);
const selectedTerms = ref([]);

const { filterState, sortOrder, load, submitFilters, clearFilters, onSort, onPage } = useAdminFilterState({
    filters: props.filters,
    defaults: {
        search: "",
        active: "",
        sort_field: "ingredient",
        sort_direction: "asc",
        per_page: 10,
    },
    routeName: "admin.ingredient-supplier-terms.index",
    loading,
    toQuery: (state) => ({
        search: state.search || undefined,
        active: state.active,
        sort_field: state.sort_field,
        sort_direction: state.sort_direction,
        per_page: state.per_page,
    }),
});

const perPageOptions = createPerPageOptions(trans, [10, 20, 50]);
const activeOptions = createActiveOptions(trans);
/*
const activeOptions = [
    { label: trans("common.all"), value: "" },
    { label: trans("common.active"), value: "1" },
    { label: trans("common.inactive"), value: "0" },
];
*/

const form = useForm({
    ingredient_id: null,
    supplier_id: null,
    lead_time_days: null,
    minimum_order_quantity: null,
    pack_size: null,
    unit_cost_override: null,
    preferred: false,
    active: true,
    meta: "",
});

const currentPage = computed(() => props.terms.current_page ?? 1);
const first = computed(() => (currentPage.value - 1) * (props.terms.per_page ?? 10));

const resetForm = () => {
    form.clearErrors();
    form.ingredient_id = null;
    form.supplier_id = null;
    form.lead_time_days = null;
    form.minimum_order_quantity = null;
    form.pack_size = null;
    form.unit_cost_override = null;
    form.preferred = false;
    form.active = true;
    form.meta = "";
};

const metaToString = (meta) => {
    if (!meta) {
        return "";
    }

    return JSON.stringify(meta, null, 2);
};

const openCreate = () => {
    editModalVisible.value = false;
    editingId.value = null;
    resetForm();
    createModalVisible.value = true;
};

const openEdit = (term) => {
    createModalVisible.value = false;
    editingId.value = term.id;
    form.clearErrors();
    form.ingredient_id = term.ingredient_id;
    form.supplier_id = term.supplier_id;
    form.lead_time_days = term.lead_time_days;
    form.minimum_order_quantity = term.minimum_order_quantity === null ? null : Number(term.minimum_order_quantity);
    form.pack_size = term.pack_size === null ? null : Number(term.pack_size);
    form.unit_cost_override = term.unit_cost_override === null ? null : Number(term.unit_cost_override);
    form.preferred = Boolean(term.preferred);
    form.active = Boolean(term.active);
    form.meta = metaToString(term.meta);
    editModalVisible.value = true;
};

const submitCreate = () => {
    form.post(route("admin.ingredient-supplier-terms.store"), {
        preserveScroll: true,
        onSuccess: () => {
            createModalVisible.value = false;
            resetForm();
        },
    });
};

const submitEdit = () => {
    if (!editingId.value) {
        return;
    }

    form.put(route("admin.ingredient-supplier-terms.update", editingId.value), {
        preserveScroll: true,
        onSuccess: () => {
            editModalVisible.value = false;
            editingId.value = null;
            resetForm();
        },
    });
};

const confirmDelete = (term) => {
    confirm.require({
        header: trans("admin_supplier_terms.confirm_delete_header"),
        message: trans("admin_supplier_terms.confirm_delete_message", {
            ingredient: term.ingredient_name,
            supplier: term.supplier_name,
        }),
        rejectLabel: trans("common.cancel"),
        acceptLabel: trans("common.delete"),
        acceptClass: "p-button-danger",
        accept: () => {
            router.delete(route("admin.ingredient-supplier-terms.destroy", term.id), {
                preserveScroll: true,
            });
        },
    });
};

const { formatCurrency, formatQuantity } = useLocaleFormat();
</script>

<template>
    <Head :title="$t('admin_supplier_terms.meta_title')" />

    <div class="space-y-6">
        <SectionTitle
            :eyebrow="$t('admin_supplier_terms.eyebrow')"
            :title="$t('admin_supplier_terms.title')"
            :description="$t('admin_supplier_terms.description')"
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <AdminTableToolbar>
                <template #filters>
                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">
                            {{ $t("common.search") }}
                        </label>
                        <InputText
                            v-model="filterState.search"
                            class="w-full"
                            :placeholder="$t('admin_supplier_terms.filters.search_placeholder')"
                            @keyup.enter="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">
                            {{ $t("common.status") }}
                        </label>
                        <Select
                            v-model="filterState.active"
                            :options="activeOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">
                            {{ $t("common.rows_per_page") }}
                        </label>
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
                    <Button icon="pi pi-search" :label="$t('common.search')" @click="submitFilters" />
                    <Button icon="pi pi-plus" :label="$t('admin_supplier_terms.actions.create')" @click="openCreate" />
                </template>
            </AdminTableToolbar>

            <div class="mt-4 overflow-x-auto">
                <BaseDataTable
                    v-model:selection="selectedTerms"
                    :value="terms.data"
                    lazy
                    paginator
                    scrollable
                    :rows="terms.per_page"
                    :first="first"
                    :total-records="terms.total"
                    :loading="loading"
                    data-key="id"
                    sort-mode="single"
                    :sort-field="filterState.sort_field"
                    :sort-order="sortOrder"
                    :empty-title="$t('admin.common.empty.title')"
                    :empty-description="
                        $t(
                            filterState.search
                                ? 'admin.common.empty.no_results_description'
                                : 'admin.common.empty.description'
                        )
                    "
                    :empty-primary-label="$t('admin_supplier_terms.actions.create')"
                    :empty-secondary-label="$t('common.clear_filters')"
                    :selected-count="selectedTerms.length"
                    @sort="onSort"
                    @page="onPage"
                    @empty-primary="openCreate"
                    @empty-secondary="clearFilters"
                    @clear-selection="selectedTerms = []"
                >
                    <Column selection-mode="multiple" header-style="width:3rem" />
                    <Column field="ingredient" :header="$t('common.ingredient')" sortable>
                        <template #body="{ data }">
                            <div>
                                <p class="font-semibold text-bakery-dark">
                                    {{ data.ingredient_name }}
                                </p>
                                <p class="text-xs text-bakery-dark/60">
                                    {{ data.ingredient_unit || "-" }}
                                </p>
                            </div>
                        </template>
                    </Column>
                    <Column field="supplier" :header="$t('common.supplier')" sortable>
                        <template #body="{ data }">
                            <span class="font-medium text-bakery-dark">{{ data.supplier_name }}</span>
                        </template>
                    </Column>
                    <Column field="lead_time_days" :header="$t('admin_supplier_terms.columns.lead_time')" sortable>
                        <template #body="{ data }">
                            <InlineEditableNumber
                                :model-value="data.lead_time_days"
                                route-name="admin.ingredient-supplier-terms.inline.update"
                                :route-params="data.id"
                                field="lead_time_days"
                                :suffix="$t('common.day')"
                                :reload-only="['terms']"
                            />
                        </template>
                    </Column>
                    <Column
                        field="minimum_order_quantity"
                        :header="$t('admin_supplier_terms.columns.minimum')"
                        sortable
                    >
                        <template #body="{ data }">
                            {{ formatQuantity(data.minimum_order_quantity, data.ingredient_unit) }}
                        </template>
                    </Column>
                    <Column field="pack_size" :header="$t('admin_supplier_terms.columns.pack_size')" sortable>
                        <template #body="{ data }">
                            {{ formatQuantity(data.pack_size, data.ingredient_unit) }}
                        </template>
                    </Column>
                    <Column
                        field="unit_cost_override"
                        :header="$t('admin_supplier_terms.columns.unit_cost_override')"
                        sortable
                    >
                        <template #body="{ data }">
                            <InlineEditableNumber
                                :model-value="data.unit_cost_override"
                                route-name="admin.ingredient-supplier-terms.inline.update"
                                :route-params="data.id"
                                field="unit_cost_override"
                                :reload-only="['terms']"
                            />
                        </template>
                    </Column>
                    <Column field="preferred" :header="$t('admin_supplier_terms.columns.preferred')" sortable>
                        <template #body="{ data }">
                            <InlineEditableToggle
                                :model-value="Boolean(data.preferred)"
                                route-name="admin.ingredient-supplier-terms.inline.update"
                                :route-params="data.id"
                                field="preferred"
                                :reload-only="['terms']"
                            />
                        </template>
                    </Column>
                    <Column field="active" :header="$t('common.status')" sortable>
                        <template #body="{ data }">
                            <InlineEditableToggle
                                :model-value="Boolean(data.active)"
                                route-name="admin.ingredient-supplier-terms.inline.update"
                                :route-params="data.id"
                                field="active"
                                :reload-only="['terms']"
                            />
                        </template>
                    </Column>
                    <Column :header="$t('common.actions')" :exportable="false">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Button
                                    icon="pi pi-pencil"
                                    text
                                    rounded
                                    class="h-11! w-11!"
                                    :aria-label="$t('admin_supplier_terms.actions.edit')"
                                    @click="openEdit(data)"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    text
                                    rounded
                                    severity="danger"
                                    class="h-11! w-11!"
                                    :aria-label="$t('admin_supplier_terms.actions.delete')"
                                    @click="confirmDelete(data)"
                                />
                            </div>
                        </template>
                    </Column>
                </BaseDataTable>
            </div>
        </div>

        <CreateModal
            v-model:visible="createModalVisible"
            :form="form"
            :ingredients="ingredients"
            :suppliers="suppliers"
            @submit="submitCreate"
        />
        <EditModal
            v-model:visible="editModalVisible"
            :form="form"
            :ingredients="ingredients"
            :suppliers="suppliers"
            @submit="submitEdit"
        />
        <ConfirmDialog />
    </div>
</template>
