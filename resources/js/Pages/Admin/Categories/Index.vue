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
import CategoryStatusBadge from "@/Components/Admin/Categories/CategoryStatusBadge.vue";
import CreateModal from "@/Components/Admin/Categories/CreateModal.vue";
import EditModal from "@/Components/Admin/Categories/EditModal.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { trans } from "laravel-vue-i18n";
import { perPageOptions } from "@/Utils/functions.js";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    categories: {
        type: Object,
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
    sort_field: props.filters.sort_field ?? "sort_order",
    sort_direction: props.filters.sort_direction ?? "asc",
    per_page: props.filters.per_page ?? 10,
});

const perPageOptions = perPageOptions(trans, [10, 20, 50]);
/*
const perPageOptions = [
    { label: trans("common.page_count", { count: 10 }), value: 10 },
    { label: trans("common.page_count", { count: 20 }), value: 20 },
    { label: trans("common.page_count", { count: 50 }), value: 50 },
];
*/

const form = useForm({
    name: "",
    slug: "",
    description: "",
    is_active: true,
    sort_order: 0,
});

const sortOrder = computed(() => (filterState.sort_direction === "asc" ? 1 : -1));
const currentPage = computed(() => props.categories.current_page ?? 1);
const first = computed(() => (currentPage.value - 1) * (props.categories.per_page ?? 10));

const load = (extra = {}) => {
    loading.value = true;

    router.get(
        route("admin.categories.index"),
        {
            search: filterState.search || undefined,
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
    filterState.per_page = 10;
    filterState.sort_field = "sort_order";
    filterState.sort_direction = "asc";
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
    form.description = "";
    form.is_active = true;
    form.sort_order = 0;
    createModalVisible.value = true;
};

const openEdit = (category) => {
    createModalVisible.value = false;
    editingId.value = category.id;
    form.clearErrors();
    form.name = category.name;
    form.slug = category.slug;
    form.description = category.description ?? "";
    form.is_active = category.is_active;
    form.sort_order = category.sort_order;
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

    form.post(route("admin.categories.store"), options);
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

    form.put(route("admin.categories.update", editingId.value), options);
};

const confirmDelete = (category) => {
    confirm.require({
        header: trans("admin_categories.confirm_delete_header"),
        message: trans("admin_categories.confirm_delete_message", {
            name: category.name,
        }),
        rejectLabel: trans("common.cancel"),
        acceptLabel: trans("common.delete"),
        acceptClass: "p-button-danger",
        accept: () => {
            router.delete(route("admin.categories.destroy", category.id), {
                preserveScroll: true,
            });
        },
    });
};
</script>

<template>
    <Head :title="$t('admin_categories.meta_title')" />

    <div class="space-y-6">
        <SectionTitle
            :eyebrow="$t('admin_categories.eyebrow')"
            :title="$t('admin_categories.title')"
            :description="$t('admin_categories.description')"
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <AdminTableToolbar
                :filters-grid-class="'grid gap-3 sm:grid-cols-2 xl:grid-cols-2'"
            >
                <template #filters>
                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                        >
                            {{ $t("admin_categories.filters.search") }}
                        </label>
                        <InputText
                            v-model="filterState.search"
                            :placeholder="
                                $t('admin_categories.filters.search_placeholder')
                            "
                            class="w-full"
                            @keyup.enter="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                        >
                            {{ $t("admin_categories.filters.per_page") }}
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
                    <Button
                        icon="pi pi-search"
                        :label="$t('admin_categories.actions.search')"
                        @click="submitFilters"
                    />
                    <Button
                        icon="pi pi-plus"
                        :label="$t('admin_categories.actions.create')"
                        @click="openCreate"
                    />
                </template>
            </AdminTableToolbar>

            <div class="mt-4 overflow-x-auto">
                <DataTable
                    :value="categories.data"
                    lazy
                    paginator
                    scrollable
                    :rows="categories.per_page"
                    :first="first"
                    :total-records="categories.total"
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
                            <p>{{ $t("admin_categories.empty") }}</p>
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
                                    :label="$t('admin_categories.actions.create')"
                                    size="small"
                                    @click="openCreate"
                                />
                            </div>
                        </div>
                    </template>

                    <Column field="name" :header="$t('common.name')" sortable>
                        <template #body="{ data }">
                            <div>
                                <p class="font-semibold text-bakery-dark">
                                    {{ data.name }}
                                </p>
                                <p class="text-xs text-bakery-dark/60">
                                    /{{ data.slug }}
                                </p>
                            </div>
                        </template>
                    </Column>
                    <Column
                        field="sort_order"
                        :header="$t('admin_categories.columns.sort_order')"
                        sortable
                    />
                    <Column field="products_count" :header="$t('nav.products')" />
                    <Column
                        field="is_active"
                        :header="$t('admin_categories.columns.status')"
                        sortable
                    >
                        <template #body="{ data }">
                            <CategoryStatusBadge :active="data.is_active" />
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
                                    :aria-label="$t('admin_categories.actions.edit')"
                                    @click="openEdit(data)"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    text
                                    rounded
                                    severity="danger"
                                    class="!h-11 !w-11"
                                    :aria-label="$t('admin_categories.actions.delete')"
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
            @submit="submitCreate"
        />
        <EditModal v-model:visible="editModalVisible" :form="form" @submit="submitEdit" />
        <ConfirmDialog />
    </div>
</template>
