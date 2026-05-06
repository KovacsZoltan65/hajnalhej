<script setup>
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import Button from "primevue/button";
import Column from "primevue/column";
import ConfirmDialog from "primevue/confirmdialog";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import { useConfirm } from "primevue/useconfirm";

import AdminTableToolbar from "@/Components/Admin/AdminTableToolbar.vue";
import BaseDataTable from "@/Components/Admin/Table/BaseDataTable.vue";
import EntityStatusBadge from "@/Components/Admin/Table/EntityStatusBadge.vue";
import InlineEditableNumber from "@/Components/Admin/Table/InlineEditableNumber.vue";
import InlineEditableSelect from "@/Components/Admin/Table/InlineEditableSelect.vue";
import InlineEditableToggle from "@/Components/Admin/Table/InlineEditableToggle.vue";
import CreateModal from "@/Components/Admin/Products/CreateModal.vue";
import EditModal from "@/Components/Admin/Products/EditModal.vue";
import CategoryStatusBadge from "@/Components/Admin/Categories/CategoryStatusBadge.vue";
import ProductPrice from "@/Components/Admin/Products/ProductPrice.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { pageOptions as createPerPageOptions } from "@/Utils/functions.js";
import { trans } from "laravel-vue-i18n";
import { useAdminFilterState } from "@/composables/useAdminFilterState.js";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    products: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    stockStatuses: {
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
const editModalVisible = ref(false);
const editingId = ref(null);
const selectedProducts = ref([]);

const { filterState, sortOrder, load, submitFilters, clearFilters, onSort, onPage } = useAdminFilterState({
    filters: props.filters,
    defaults: {
        search: "",
        category_id: null,
        is_active: "",
        sort_field: "sort_order",
        sort_direction: "asc",
        per_page: 10,
    },
    routeName: "admin.products.index",
    loading,
    toQuery: (state) => ({
        search: state.search || undefined,
        category_id: state.category_id || undefined,
        is_active: state.is_active,
        sort_field: state.sort_field,
        sort_direction: state.sort_direction,
        per_page: state.per_page,
    }),
});

const perPageOptions = createPerPageOptions(trans, [10, 20, 50]);
/*
const perPageOptions = [
    { label: "10 / oldal", value: 10 },
    { label: "20 / oldal", value: 20 },
    { label: "50 / oldal", value: 50 },
];
*/
const activeOptions = [
    { label: "Mind", value: "" },
    { label: "Aktív", value: "1" },
    { label: "Inaktív", value: "0" },
];

const form = useForm({
    category_id: null,
    name: "",
    slug: "",
    short_description: "",
    description: "",
    price: 0,
    is_active: true,
    is_featured: false,
    stock_status: "in_stock",
    image_path: "",
    sort_order: 0,
});

const currentPage = computed(() => props.products.current_page ?? 1);
const first = computed(() => (currentPage.value - 1) * (props.products.per_page ?? 10));

const openCreate = () => {
    editModalVisible.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    form.category_id = props.categories[0]?.id ?? null;
    form.name = "";
    form.slug = "";
    form.short_description = "";
    form.description = "";
    form.price = 0;
    form.is_active = true;
    form.is_featured = false;
    form.stock_status = "in_stock";
    form.image_path = "";
    form.sort_order = 0;
    createModalVisible.value = true;
};

const openEdit = (product) => {
    createModalVisible.value = false;
    editingId.value = product.id;
    form.clearErrors();
    form.category_id = product.category_id;
    form.name = product.name;
    form.slug = product.slug;
    form.short_description = product.short_description ?? "";
    form.description = product.description ?? "";
    form.price = product.price;
    form.is_active = product.is_active;
    form.is_featured = product.is_featured;
    form.stock_status = product.stock_status;
    form.image_path = product.image_path ?? "";
    form.sort_order = product.sort_order;
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

    form.post(route("admin.products.store"), options);
};

const submitEdit = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
            form.reset();
            editingId.value = null;
        },
    };

    if (!editingId.value) {
        return;
    }

    form.put(route("admin.products.update", editingId.value), options);
};

const confirmDelete = (product) => {
    confirm.require({
        header: "Termék törlése",
        message: `Biztosan törlöd ezt a terméket: ${product.name}?`,
        rejectLabel: "Mégse",
        acceptLabel: "Törlés",
        acceptClass: "p-button-danger",
        accept: () => {
            router.delete(route("admin.products.destroy", product.id), {
                preserveScroll: true,
            });
        },
    });
};
</script>

<template>
    <Head title="Termékek" />

    <div class="space-y-6">
        <SectionTitle
            eyebrow="Admin / Termékek"
            title="Termékek"
            description="A Kategóriák referencia modul mintájára épített teljes termékkezelés."
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <AdminTableToolbar>
                <template #filters>
                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >Keresés</label
                        >
                        <InputText
                            v-model="filterState.search"
                            class="w-full"
                            placeholder="Név vagy slug"
                            @keyup.enter="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >Kategória</label
                        >
                        <Select
                            v-model="filterState.category_id"
                            :options="[{ id: null, name: 'Mind' }, ...categories]"
                            option-label="name"
                            option-value="id"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >Státusz</label
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
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >Találat / oldal</label
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
                    <Link
                        :href="route('admin.products.create-flow')"
                        class="inline-flex items-center whitespace-nowrap rounded-lg bg-bakery-brown px-3 py-2 text-sm font-medium text-white hover:bg-bakery-dark"
                    >
                        {{ $t("admin.products.flow.actions.open") }}
                    </Link>
                    <Link
                        :href="route('admin.recipes.index')"
                        class="inline-flex items-center whitespace-nowrap rounded-lg border border-bakery-brown/20 px-3 py-2 text-sm font-medium text-bakery-brown hover:bg-bakery-brown/10"
                    >
                        Receptek oldal
                    </Link>
                    <Button icon="pi pi-search" label="Keresés" @click="submitFilters" />
                    <Button icon="pi pi-plus" label="Új termék" @click="openCreate" />
                </template>
            </AdminTableToolbar>

            <div class="mt-4 overflow-x-auto">
                <BaseDataTable
                    v-model:selection="selectedProducts"
                    :value="products.data"
                    lazy
                    paginator
                    scrollable
                    :rows="products.per_page"
                    :first="first"
                    :total-records="products.total"
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
                    :empty-primary-label="$t('admin_product.actions.create')"
                    :empty-secondary-label="$t('common.clear_filters')"
                    :selected-count="selectedProducts.length"
                    @sort="onSort"
                    @page="onPage"
                    @empty-primary="openCreate"
                    @empty-secondary="clearFilters"
                    @clear-selection="selectedProducts = []"
                >
                    <Column selection-mode="multiple" header-style="width:3rem" />
                    <Column field="name" header="Név" sortable>
                        <template #body="{ data }">
                            <div>
                                <p class="font-semibold text-bakery-dark">
                                    {{ data.name }}
                                </p>
                                <p class="text-xs text-bakery-dark/60">
                                    /{{ data.slug }}
                                    <span v-if="data.category_name">/ {{ data.category_name }}</span>
                                </p>
                            </div>
                        </template>
                    </Column>
                    <Column field="category_id" :header="$t('admin.products.flow.fields.category')" sortable>
                        <template #body="{ data }">
                            <InlineEditableSelect
                                :model-value="data.category_id"
                                route-name="admin.products.inline.update"
                                :route-params="data.id"
                                field="category_id"
                                :options="categories"
                                option-label="name"
                                option-value="id"
                                :reload-only="['products']"
                            />
                        </template>
                    </Column>
                    <Column field="price" header="Ár" sortable>
                        <template #body="{ data }">
                            <InlineEditableNumber
                                :model-value="data.price"
                                route-name="admin.products.inline.update"
                                :route-params="data.id"
                                field="price"
                                :reload-only="['products']"
                            />
                        </template>
                    </Column>
                    <Column field="is_active" header="Státusz" sortable>
                        <template #body="{ data }">
                            <div class="flex items-center gap-3">
                                <InlineEditableToggle
                                    :model-value="Boolean(data.is_active)"
                                    route-name="admin.products.inline.update"
                                    :route-params="data.id"
                                    field="is_active"
                                    :reload-only="['products']"
                                />
                                <EntityStatusBadge :status="Boolean(data.is_active)" />
                            </div>
                        </template>
                    </Column>
                    <Column header="Műveletek" :exportable="false">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Link
                                    :href="
                                        route('admin.recipes.index', {
                                            product_id: data.id,
                                        })
                                    "
                                    class="inline-flex min-h-11 items-center rounded-md border border-bakery-brown/20 px-3 py-2 text-xs font-medium text-bakery-brown hover:bg-bakery-brown/10"
                                >
                                    Recept
                                </Link>
                                <Button
                                    icon="pi pi-pencil"
                                    text
                                    rounded
                                    class="h-11! w-11!"
                                    aria-label="Termék szerkesztése"
                                    @click="openEdit(data)"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    text
                                    rounded
                                    severity="danger"
                                    class="h-11! w-11!"
                                    aria-label="Termék törlése"
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
            :categories="categories"
            :stock-statuses="stockStatuses"
            @submit="submitCreate"
        />
        <EditModal
            v-model:visible="editModalVisible"
            :form="form"
            :categories="categories"
            :stock-statuses="stockStatuses"
            @submit="submitEdit"
        />

        <ConfirmDialog />
    </div>
</template>
