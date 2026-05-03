<script setup>
import { Head, router, useForm } from "@inertiajs/vue3";
import { computed, reactive, ref } from "vue";
import Button from "primevue/button";
import Column from "primevue/column";
import ConfirmDialog from "primevue/confirmdialog";
import DataTable from "primevue/datatable";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import { useConfirm } from "primevue/useconfirm";
import AdminTableToolbar from "@/Components/Admin/AdminTableToolbar.vue";
import CreateModal from "@/Components/Admin/IngredientSupplierTerms/CreateModal.vue";
import EditModal from "@/Components/Admin/IngredientSupplierTerms/EditModal.vue";
import PreferredBadge from "@/Components/Admin/IngredientSupplierTerms/PreferredBadge.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { trans } from "laravel-vue-i18n";

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

const filterState = reactive({
    search: props.filters.search ?? "",
    active: props.filters.active ?? "",
    sort_field: props.filters.sort_field ?? "ingredient",
    sort_direction: props.filters.sort_direction ?? "asc",
    per_page: props.filters.per_page ?? 10,
});

const perPageOptions = [
    { label: trans("admin_supplier_terms.filters.per_page_option", { count: 10 }), value: 10 },
    { label: trans("admin_supplier_terms.filters.per_page_option", { count: 20 }), value: 20 },
    { label: trans("admin_supplier_terms.filters.per_page_option", { count: 50 }), value: 50 },
];

const activeOptions = [
    { label: trans("common.all"), value: "" },
    { label: trans("common.active"), value: "1" },
    { label: trans("common.inactive"), value: "0" },
];

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

const sortOrder = computed(() => (filterState.sort_direction === "asc" ? 1 : -1));
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

const load = (extra = {}) => {
    loading.value = true;

    router.get(
        "/admin/ingredient-supplier-terms",
        {
            search: filterState.search || undefined,
            active: filterState.active,
            sort_field: filterState.sort_field,
            sort_direction: filterState.sort_direction,
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
    filterState.search = "";
    filterState.active = "";
    filterState.sort_field = "ingredient";
    filterState.sort_direction = "asc";
    filterState.per_page = 10;
    submitFilters();
};

const onSort = (event) => {
    filterState.sort_field = event.sortField;
    filterState.sort_direction = event.sortOrder === 1 ? "asc" : "desc";
    load({ page: 1 });
};

const onPage = (event) => {
    filterState.per_page = event.rows;
    load({ page: event.page + 1, per_page: event.rows });
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
    form.post("/admin/ingredient-supplier-terms", {
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

    form.put(`/admin/ingredient-supplier-terms/${editingId.value}`, {
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
            router.delete(`/admin/ingredient-supplier-terms/${term.id}`, {
                preserveScroll: true,
            });
        },
    });
};

const formatQuantity = (value, unit = "") => {
    if (value === null || value === undefined || value === "") {
        return "-";
    }

    return `${Number(value).toLocaleString(trans("common.locale"), { maximumFractionDigits: 3 })}${unit ? ` ${unit}` : ""}`;
};

const formatCurrency = (value) => {
    if (value === null || value === undefined || value === "") {
        return "-";
    }

    return new Intl.NumberFormat(trans("common.locale"), {
        style: "currency",
        currency: trans("common.currency"),
        maximumFractionDigits: 0,
    }).format(Number(value));
};
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
                            {{ $t("admin_supplier_terms.filters.search") }}
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
                            {{ $t("admin_supplier_terms.filters.status") }}
                        </label>
                        <Select v-model="filterState.active" :options="activeOptions" option-label="label" option-value="value" class="w-full" @change="submitFilters" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">
                            {{ $t("admin_supplier_terms.filters.per_page") }}
                        </label>
                        <Select v-model="filterState.per_page" :options="perPageOptions" option-label="label" option-value="value" class="w-full" @change="submitFilters" />
                    </div>
                </template>

                <template #actions>
                    <Button icon="pi pi-search" :label="$t('admin_supplier_terms.actions.search')" @click="submitFilters" />
                    <Button icon="pi pi-plus" :label="$t('admin_supplier_terms.actions.create')" @click="openCreate" />
                </template>
            </AdminTableToolbar>

            <div class="mt-4 overflow-x-auto">
                <DataTable
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
                    @sort="onSort"
                    @page="onPage"
                >
                    <template #empty>
                        <div class="rounded-xl border border-dashed border-bakery-brown/25 bg-[#fcf7ef] p-6 text-center text-sm text-bakery-dark/70">
                            <p>{{ $t("admin_supplier_terms.empty") }}</p>
                            <div class="mt-3 flex flex-wrap items-center justify-center gap-2">
                                <Button :label="$t('common.clear_filters')" outlined size="small" @click="clearFilters" />
                                <Button :label="$t('admin_supplier_terms.actions.create')" size="small" @click="openCreate" />
                            </div>
                        </div>
                    </template>

                    <Column field="ingredient" :header="$t('admin_supplier_terms.columns.ingredient')" sortable>
                        <template #body="{ data }">
                            <div>
                                <p class="font-semibold text-bakery-dark">{{ data.ingredient_name }}</p>
                                <p class="text-xs text-bakery-dark/60">{{ data.ingredient_unit || '-' }}</p>
                            </div>
                        </template>
                    </Column>
                    <Column field="supplier" :header="$t('admin_supplier_terms.columns.supplier')" sortable>
                        <template #body="{ data }">
                            <span class="font-medium text-bakery-dark">{{ data.supplier_name }}</span>
                        </template>
                    </Column>
                    <Column field="lead_time_days" :header="$t('admin_supplier_terms.columns.lead_time')" sortable>
                        <template #body="{ data }">
                            {{ data.lead_time_days !== null ? $t("common.day_count", { count: data.lead_time_days }) : "-" }}
                        </template>
                    </Column>
                    <Column field="minimum_order_quantity" :header="$t('admin_supplier_terms.columns.minimum')" sortable>
                        <template #body="{ data }">
                            {{ formatQuantity(data.minimum_order_quantity, data.ingredient_unit) }}
                        </template>
                    </Column>
                    <Column field="pack_size" :header="$t('admin_supplier_terms.columns.pack_size')" sortable>
                        <template #body="{ data }">
                            {{ formatQuantity(data.pack_size, data.ingredient_unit) }}
                        </template>
                    </Column>
                    <Column field="unit_cost_override" :header="$t('admin_supplier_terms.columns.unit_cost_override')" sortable>
                        <template #body="{ data }">
                            {{ formatCurrency(data.unit_cost_override) }}
                        </template>
                    </Column>
                    <Column field="preferred" :header="$t('admin_supplier_terms.columns.preferred')" sortable>
                        <template #body="{ data }">
                            <PreferredBadge :preferred="Boolean(data.preferred)" :active="Boolean(data.active)" />
                        </template>
                    </Column>
                    <Column field="active" :header="$t('admin_supplier_terms.columns.status')" sortable>
                        <template #body="{ data }">
                            <span class="text-sm font-semibold" :class="data.active ? 'text-emerald-700' : 'text-stone-500'">
                                {{ data.active ? $t("common.active") : $t("common.inactive") }}
                            </span>
                        </template>
                    </Column>
                    <Column :header="$t('common.actions')" :exportable="false">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Button icon="pi pi-pencil" text rounded class="!h-11 !w-11" :aria-label="$t('admin_supplier_terms.actions.edit')" @click="openEdit(data)" />
                                <Button icon="pi pi-trash" text rounded severity="danger" class="!h-11 !w-11" :aria-label="$t('admin_supplier_terms.actions.delete')" @click="confirmDelete(data)" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <CreateModal v-model:visible="createModalVisible" :form="form" :ingredients="ingredients" :suppliers="suppliers" @submit="submitCreate" />
        <EditModal v-model:visible="editModalVisible" :form="form" :ingredients="ingredients" :suppliers="suppliers" @submit="submitEdit" />
        <ConfirmDialog />
    </div>
</template>
