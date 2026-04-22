<script setup>
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { computed, reactive, ref } from "vue";
import Button from "primevue/button";
import Column from "primevue/column";
import ConfirmDialog from "primevue/confirmdialog";
import DataTable from "primevue/datatable";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import { useConfirm } from "primevue/useconfirm";

import AdminTableToolbar from "@/Components/Admin/AdminTableToolbar.vue";
import CreateModal from "@/Components/Admin/Products/CreateModal.vue";
import EditModal from "@/Components/Admin/Products/EditModal.vue";
import CategoryStatusBadge from "@/Components/Admin/Categories/CategoryStatusBadge.vue";
import ProductPrice from "@/Components/Admin/Products/ProductPrice.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";

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

const filterState = reactive({
    search: props.filters.search ?? "",
    category_id: props.filters.category_id ?? null,
    is_active: props.filters.is_active ?? "",
    sort_field: props.filters.sort_field ?? "sort_order",
    sort_direction: props.filters.sort_direction ?? "asc",
    per_page: props.filters.per_page ?? 10,
});

const perPageOptions = [
    { label: "10 / oldal", value: 10 },
    { label: "20 / oldal", value: 20 },
    { label: "50 / oldal", value: 50 },
];

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

const sortOrder = computed(() => (filterState.sort_direction === "asc" ? 1 : -1));
const currentPage = computed(() => props.products.current_page ?? 1);
const first = computed(() => (currentPage.value - 1) * (props.products.per_page ?? 10));

const load = (extra = {}) => {
    loading.value = true;

    router.get(
        "/admin/products",
        {
            search: filterState.search || undefined,
            category_id: filterState.category_id || undefined,
            is_active: filterState.is_active,
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

    form.post("/admin/products", options);
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

    form.put(`/admin/products/${editingId.value}`, options);
};

const confirmDelete = (product) => {
    confirm.require({
        header: "Termek törlése",
        message: `Biztosan torlod ezt a terméket: ${product.name}?`,
        rejectLabel: "Mégse",
        acceptLabel: "Torles",
        acceptClass: "p-button-danger",
        accept: () => {
            router.delete(`/admin/products/${product.id}`, {
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
            title="Termekek"
            description="A Categories referencia modul mintajara epitett teljes Products CRUD."
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <AdminTableToolbar>
                <template #filters>
                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
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
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >Kategoria</label
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
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
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
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
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
                        href="/admin/recipes"
                        class="inline-flex items-center whitespace-nowrap rounded-lg border border-bakery-brown/20 px-3 py-2 text-sm font-medium text-bakery-brown hover:bg-bakery-brown/10"
                    >
                        Receptek oldal
                    </Link>
                    <Button icon="pi pi-search" label="Keresés" @click="submitFilters" />
                    <Button icon="pi pi-plus" label="Uj termék" @click="openCreate" />
                </template>
            </AdminTableToolbar>

            <DataTable
                class="mt-4"
                :value="products.data"
                lazy
                paginator
                :rows="products.per_page"
                :first="first"
                :total-records="products.total"
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
                        Nincs megjeleníthető termék. Hozd létre az elsőt.
                    </div>
                </template>

                <Column field="name" header="Név" sortable />
                <Column field="slug" header="Slug" sortable>
                    <template #body="{ data }">
                        <code class="text-xs text-bakery-dark/70">/{{ data.slug }}</code>
                    </template>
                </Column>
                <Column field="price" header="Ar" sortable>
                    <template #body="{ data }">
                        <ProductPrice :price="data.price" />
                    </template>
                </Column>
                <Column field="sort_order" header="Sorrend" sortable />
                <Column field="is_active" header="Státusz" sortable>
                    <template #body="{ data }">
                        <CategoryStatusBadge :active="data.is_active" />
                    </template>
                </Column>
                <Column header="Muveletek" :exportable="false">
                    <template #body="{ data }">
                        <div class="flex items-center gap-2">
                            <Link
                                :href="`/admin/recipes?product_id=${data.id}`"
                                class="inline-flex items-center rounded-md border border-bakery-brown/20 px-2.5 py-1.5 text-xs font-medium text-bakery-brown hover:bg-bakery-brown/10"
                            >
                                Recept
                            </Link>
                            <Button
                                icon="pi pi-pencil"
                                size="small"
                                text
                                rounded
                                @click="openEdit(data)"
                            />
                            <Button
                                icon="pi pi-trash"
                                size="small"
                                text
                                rounded
                                severity="danger"
                                @click="confirmDelete(data)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
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


