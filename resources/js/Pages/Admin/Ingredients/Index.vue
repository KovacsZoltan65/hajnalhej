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
import CreateModal from "@/Components/Admin/Ingredients/CreateModal.vue";
import EditModal from "@/Components/Admin/Ingredients/EditModal.vue";
import IngredientStatusBadge from "../../../Components/Admin/Ingredients/IngredientStatusBadge.vue";
import IngredientStockBadge from "../../../Components/Admin/Ingredients/IngredientStockBadge.vue";
import SectionTitle from "../../../Components/SectionTitle.vue";
import AdminLayout from "../../../Layouts/AdminLayout.vue";
import { trans } from "laravel-vue-i18n";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    ingredients: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    units: {
        type: Array,
        required: true,
    },
});

const confirm = useConfirm();
const loading = ref(false);
const createModalVisible = ref(false);
const editModalVisible = ref(false);
const editingId = ref(null);

const filterState = reactive({
    search: props.filters.search ?? "",
    is_active: props.filters.is_active ?? "",
    unit: props.filters.unit ?? "",
    sort_field: props.filters.sort_field ?? "name",
    sort_direction: props.filters.sort_direction ?? "asc",
    per_page: props.filters.per_page ?? 10,
});

const perPageOptions = [
    { label: trans("admin_ingredients.filters.per_page_option", { count: 10 }), value: 10 },
    { label: trans("admin_ingredients.filters.per_page_option", { count: 20 }), value: 20 },
    { label: trans("admin_ingredients.filters.per_page_option", { count: 50 }), value: 50 },
];

const activeOptions = [
    { label: trans("common.all"), value: "" },
    { label: trans("common.active"), value: "1" },
    { label: trans("common.inactive"), value: "0" },
];

const unitOptions = computed(() => [
    { label: trans("common.all"), value: "" },
    ...props.units.map((unit) => ({ label: unit, value: unit })),
]);

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

const form = useForm({
    name: "",
    slug: "",
    sku: "",
    unit: "db",
    estimated_unit_cost: 0,
    current_stock: 0,
    minimum_stock: 0,
    is_active: true,
    notes: "",
});

const sortOrder = computed(() => (filterState.sort_direction === "asc" ? 1 : -1));
const currentPage = computed(() => props.ingredients.current_page ?? 1);
const first = computed(
    () => (currentPage.value - 1) * (props.ingredients.per_page ?? 10)
);

const load = (extra = {}) => {
    loading.value = true;

    router.get(
        route("admin.ingredients.index"),
        {
            search: filterState.search || undefined,
            is_active: filterState.is_active,
            unit: filterState.unit || undefined,
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
    filterState.is_active = "";
    filterState.unit = "";
    filterState.sort_field = "name";
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
    form.reset();
    form.clearErrors();
    form.name = "";
    form.slug = "";
    form.sku = "";
    form.unit = props.units[0] ?? "db";
    form.estimated_unit_cost = 0;
    form.current_stock = 0;
    form.minimum_stock = 0;
    form.is_active = true;
    form.notes = "";
    createModalVisible.value = true;
};

const openEdit = (ingredient) => {
    createModalVisible.value = false;
    editingId.value = ingredient.id;
    form.clearErrors();
    form.name = ingredient.name;
    form.slug = ingredient.slug;
    form.sku = ingredient.sku ?? "";
    form.unit = ingredient.unit;
    form.estimated_unit_cost = ingredient.estimated_unit_cost ?? 0;
    form.current_stock = ingredient.current_stock;
    form.minimum_stock = ingredient.minimum_stock;
    form.is_active = ingredient.is_active;
    form.notes = ingredient.notes ?? "";
    editModalVisible.value = true;
};

const closeCreateModal = () => {
    createModalVisible.value = false;
};

const closeEditModal = () => {
    editModalVisible.value = false;
};

const submitCreate = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            closeCreateModal();
            form.reset();
        },
    };

    form.post(route("admin.ingredients.store"), options);
};

const submitEdit = () => {
    if (!editingId.value) {
        return;
    }

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
            form.reset();
            editingId.value = null;
        },
    };

    form.put(route("admin.ingredients.update", editingId.value), options);
};

const confirmDelete = (ingredient) => {
    confirm.require({
        header: trans("admin_ingredients.confirm_delete_header"),
        message: trans("admin_ingredients.confirm_delete_message", {
            name: ingredient.name,
        }),
        rejectLabel: trans("common.cancel"),
        acceptLabel: trans("common.delete"),
        acceptClass: "p-button-danger",
        accept: () => {
            router.delete(route("admin.ingredients.destroy", ingredient.id), {
                preserveScroll: true,
            });
        },
    });
};
</script>

<template>
    <Head :title="$t('admin_ingredients.meta_title')" />

    <div class="space-y-6">
        <SectionTitle
            :eyebrow="$t('admin_ingredients.eyebrow')"
            :title="$t('admin_ingredients.title')"
            :description="$t('admin_ingredients.description')"
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <AdminTableToolbar>
                <template #filters>
                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_ingredients.filters.search") }}</label
                        >
                        <InputText
                            v-model="filterState.search"
                            class="w-full"
                            :placeholder="$t('admin_ingredients.filters.search_placeholder')"
                            @keyup.enter="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_ingredients.filters.status") }}</label
                        >
                        <Select
                            v-model="filterState.is_active"
                            :options="activeOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_ingredients.filters.unit") }}</label
                        >
                        <Select
                            v-model="filterState.unit"
                            :options="unitOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_ingredients.filters.per_page") }}</label
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
                </template>

                <template #actions>
                    <Button
                        icon="pi pi-search"
                        :label="$t('admin_ingredients.actions.search')"
                        @click="submitFilters"
                    />
                    <Button
                        icon="pi pi-plus"
                        :label="$t('admin_ingredients.actions.create')"
                        @click="openCreate"
                    />
                </template>
            </AdminTableToolbar>

            <div class="mt-4 overflow-x-auto">
                <DataTable
                    :value="ingredients.data"
                    lazy
                    paginator
                    scrollable
                    :rows="ingredients.per_page"
                    :first="first"
                    :total-records="ingredients.total"
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
                            <p>{{ $t("admin_ingredients.empty") }}</p>
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
                                    :label="$t('admin_ingredients.actions.create')"
                                    size="small"
                                    @click="openCreate"
                                />
                            </div>
                        </div>
                    </template>

                    <Column field="name" :header="$t('admin_ingredients.columns.name')" sortable>
                        <template #body="{ data }">
                            <div>
                                <p class="font-semibold text-bakery-dark">
                                    {{ data.name }}
                                </p>
                                <p class="text-xs text-bakery-dark/60">
                                    /{{ data.slug }}
                                    <span v-if="data.sku">| {{ data.sku }}</span>
                                </p>
                            </div>
                        </template>
                    </Column>
                    <Column field="unit" :header="$t('admin_ingredients.columns.unit')" sortable />
                    <Column
                        field="estimated_unit_cost"
                        :header="$t('admin_ingredients.columns.estimated_unit_cost')"
                        sortable
                    >
                        <template #body="{ data }">
                            <span class="font-medium text-bakery-dark">{{
                                formatCurrency(data.estimated_unit_cost)
                            }}</span>
                        </template>
                    </Column>
                    <Column field="current_stock" :header="$t('admin_ingredients.columns.stock')" sortable>
                        <template #body="{ data }">
                            <IngredientStockBadge
                                :current-stock="data.current_stock"
                                :minimum-stock="data.minimum_stock"
                                :unit="data.unit"
                            />
                        </template>
                    </Column>
                    <Column field="is_active" :header="$t('admin_ingredients.columns.status')" sortable>
                        <template #body="{ data }">
                            <IngredientStatusBadge :active="data.is_active" />
                        </template>
                    </Column>
                    <Column :header="$t('common.actions')" :exportable="false">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Button
                                    icon="pi pi-pencil"
                                    text
                                    rounded
                                    class="!h-11 !w-11"
                                    :aria-label="$t('admin_ingredients.actions.edit')"
                                    @click="openEdit(data)"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    text
                                    rounded
                                    severity="danger"
                                    class="!h-11 !w-11"
                                    :aria-label="$t('admin_ingredients.actions.delete')"
                                    @click="confirmDelete(data)"
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
            :units="units"
            @submit="submitCreate"
        />
        <EditModal
            v-model:visible="editModalVisible"
            :form="form"
            :units="units"
            @submit="submitEdit"
        />
        <ConfirmDialog />
    </div>
</template>
