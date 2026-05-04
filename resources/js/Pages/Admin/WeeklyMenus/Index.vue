<script setup>
import { Head, router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import Button from "primevue/button";
import Column from "primevue/column";
import ConfirmDialog from "primevue/confirmdialog";
import DataTable from "primevue/datatable";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import { useConfirm } from "primevue/useconfirm";
import AdminTableToolbar from "@/Components/Admin/AdminTableToolbar.vue";
import CreateModal from "@/Components/Admin/WeeklyMenus/CreateModal.vue";
import EditModal from "@/Components/Admin/WeeklyMenus/EditModal.vue";
import WeeklyMenuItemsModal from "@/Components/Admin/WeeklyMenus/WeeklyMenuItemsModal.vue";
import WeeklyMenuStatusBadge from "@/Components/Admin/WeeklyMenus/WeeklyMenuStatusBadge.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { trans } from "laravel-vue-i18n";
import { useAdminFilterState } from "@/composables/useAdminFilterState.js";
import { pageOptions as createPerPageOptions } from "@/Utils/functions";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    weeklyMenus: { type: Object, required: true },
    filters: { type: Object, required: true },
    statuses: { type: Array, required: true },
    products: { type: Array, required: true },
});

const confirm = useConfirm();
const loading = ref(false);
const createModalVisible = ref(false);
const editModalVisible = ref(false);
const itemsVisible = ref(false);
const editingId = ref(null);

const selectedMenuId = ref(null);

const selectedMenu = computed(() => {
    if (!selectedMenuId.value) {
        return null;
    }

    return (
        props.weeklyMenus.data.find((menu) => menu.id === selectedMenuId.value) ?? null
    );
});

const {
    filterState,
    sortOrder,
    load,
    submitFilters,
    clearFilters,
    onSort,
    onPage,
} = useAdminFilterState({
    filters: props.filters,
    defaults: {
        search: "",
        status: "",
        sort_field: "week_start",
        sort_direction: "desc",
        per_page: 10,
    },
    routeName: "admin.weekly-menus.index",
    loading,
    toQuery: (state) => ({
        search: state.search || undefined,
        status: state.status || undefined,
        sort_field: state.sort_field,
        sort_direction: state.sort_direction,
        per_page: state.per_page,
    }),
});

const statusOptions = computed(() => [
    { value: "", label: trans("admin_weekly_menus.filters.all_statuses") },
    ...props.statuses,
]);

const perPageOptions = createPerPageOptions(trans, [10, 20, 50]);
/*
const perPageOptions = computed(() => [
    { label: trans("common.page_count", { count: 10 }), value: 10 },
    { label: trans("common.page_count", { count: 20 }), value: 20 },
    { label: trans("common.page_count", { count: 50 }), value: 50 },
]);
*/

const form = useForm({
    title: "",
    slug: "",
    week_start: "",
    week_end: "",
    status: "draft",
    public_note: "",
    internal_note: "",
    is_featured: false,
});

const currentPage = computed(() => props.weeklyMenus.current_page ?? 1);
const first = computed(
    () => (currentPage.value - 1) * (props.weeklyMenus.per_page ?? 10)
);

const openCreate = () => {
    editModalVisible.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    form.title = "";
    form.slug = "";
    form.week_start = "";
    form.week_end = "";
    form.status = "draft";
    form.public_note = "";
    form.internal_note = "";
    form.is_featured = false;
    createModalVisible.value = true;
};

const openEdit = (menu) => {
    createModalVisible.value = false;
    editingId.value = menu.id;
    form.clearErrors();
    form.title = menu.title;
    form.slug = menu.slug;
    form.week_start = menu.week_start;
    form.week_end = menu.week_end;
    form.status = menu.status;
    form.public_note = menu.public_note ?? "";
    form.internal_note = menu.internal_note ?? "";
    form.is_featured = menu.is_featured;
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

    form.post(route("admin.weekly-menus.store"), options);
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

    form.put(route("admin.weekly-menus.update", editingId.value), options);
};

const confirmDelete = (menu) => {
    confirm.require({
        header: trans("admin_weekly_menus.confirm_delete_header"),
        message: trans("admin_weekly_menus.confirm_delete_message", {
            title: menu.title,
        }),
        rejectLabel: trans("common.cancel"),
        acceptLabel: trans("common.delete"),
        acceptClass: "p-button-danger",
        accept: () => {
            router.delete(route("admin.weekly-menus.destroy", menu.id), {
                preserveScroll: true,
            });
        },
    });
};

const publish = (menu) =>
    router.post(
        route("admin.weekly-menus.publish", menu.id),
        {},
        { preserveScroll: true }
    );
const unpublish = (menu) =>
    router.post(
        route("admin.weekly-menus.unpublish", menu.id),
        {},
        { preserveScroll: true }
    );
/*
const openItems = (menu) => {
    selectedMenu.value = menu;
    itemsVisible.value = true;
};
*/
const openItems = (menu) => {
    selectedMenuId.value = menu.id;
    itemsVisible.value = true;
};

const saveItem = (payload) => {
    if (!selectedMenu.value) {
        return;
    }

    const menuId = selectedMenu.value.id;

    const options = {
        preserveScroll: true,
        preserveState: true,
        //onSuccess: () => {
        //    refreshMenus();
        //},
    };

    if (payload.id) {
        router.put(
            route("admin.weekly-menus.items.update", [menuId, payload.id]),
            payload,
            options
        );

        return;
    }

    router.post(route("admin.weekly-menus.items.store", menuId), payload, options);
};

const deleteItem = (item) => {
    if (!selectedMenu.value) {
        return;
    }

    const itemName =
        item.override_name ??
        item.product_name ??
        trans("admin_weekly_menus.unnamed_item");

    confirm.require({
        header: trans("admin_weekly_menus.confirm_delete_item_header"),
        message: trans("admin_weekly_menus.confirm_delete_item_message", {
            name: itemName,
        }),
        rejectLabel: trans("common.cancel"),
        acceptLabel: trans("common.delete"),
        acceptClass: "p-button-danger",
        accept: () => {
            router.delete(
                route("admin.weekly-menus.items.destroy", [
                    selectedMenu.value.id,
                    item.id,
                ]),
                {
                    preserveScroll: true,
                    preserveState: true,
                }
            );
        },
    });
};

const refreshMenus = () => {
    router.reload({
        only: ["weeklyMenus"],
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <Head :title="$t('admin_weekly_menus.title')" />

    <div class="space-y-6">
        <SectionTitle
            :eyebrow="$t('admin_weekly_menus.eyebrow')"
            :title="$t('admin_weekly_menus.title')"
            :description="$t('admin_weekly_menus.description')"
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <AdminTableToolbar
                :filters-grid-class="'grid gap-3 sm:grid-cols-2 xl:grid-cols-3'"
            >
                <template #filters>
                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_weekly_menus.filters.search") }}</label
                        >
                        <InputText
                            v-model="filterState.search"
                            class="w-full"
                            :placeholder="
                                $t('admin_weekly_menus.filters.search_placeholder')
                            "
                            @keyup.enter="submitFilters"
                        />
                    </div>
                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_weekly_menus.filters.status") }}</label
                        >
                        <Select
                            v-model="filterState.status"
                            :options="statusOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>
                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_weekly_menus.filters.per_page") }}</label
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
                        :label="$t('common.search')"
                        @click="submitFilters"
                    />
                    <Button
                        icon="pi pi-plus"
                        :label="$t('admin_weekly_menus.actions.create')"
                        @click="openCreate"
                    />
                </template>
            </AdminTableToolbar>

            <div class="mt-4 overflow-x-auto">
                <DataTable
                    :value="weeklyMenus.data"
                    lazy
                    paginator
                    scrollable
                    :rows="weeklyMenus.per_page"
                    :first="first"
                    :total-records="weeklyMenus.total"
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
                            <p>{{ $t("admin_weekly_menus.empty") }}</p>
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
                                    :label="$t('admin_weekly_menus.actions.create')"
                                    size="small"
                                    @click="openCreate"
                                />
                            </div>
                        </div>
                    </template>

                    <Column
                        field="title"
                        :header="$t('admin_weekly_menus.columns.title')"
                        sortable
                    >
                        <template #body="{ data }">
                            <div>
                                <p class="font-semibold text-bakery-dark">
                                    {{ data.title }}
                                </p>
                                <p class="text-xs text-bakery-dark/60">
                                    /{{ data.slug }}
                                </p>
                            </div>
                        </template>
                    </Column>
                    <Column
                        field="week_start"
                        :header="$t('admin_weekly_menus.columns.week')"
                        sortable
                    >
                        <template #body="{ data }"
                            >{{ data.week_start }} - {{ data.week_end }}</template
                        >
                    </Column>
                    <Column
                        field="status"
                        :header="$t('admin_weekly_menus.columns.status')"
                        sortable
                    >
                        <template #body="{ data }">
                            <WeeklyMenuStatusBadge :status="data.status" />
                        </template>
                    </Column>
                    <Column
                        field="items_count"
                        :header="$t('admin_weekly_menus.columns.items')"
                    />
                    <Column :header="$t('common.actions')">
                        <template #body="{ data }">
                            <div class="flex flex-wrap items-center gap-2">
                                <Button
                                    icon="pi pi-list"
                                    text
                                    rounded
                                    class="h-11! w-11!"
                                    :aria-label="$t('admin_weekly_menus.actions.items')"
                                    @click="openItems(data)"
                                />
                                <Button
                                    icon="pi pi-pencil"
                                    text
                                    rounded
                                    class="h-11! w-11!"
                                    :aria-label="$t('admin_weekly_menus.actions.edit')"
                                    @click="openEdit(data)"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    text
                                    rounded
                                    severity="danger"
                                    class="h-11! w-11!"
                                    :aria-label="$t('admin_weekly_menus.actions.delete')"
                                    @click="confirmDelete(data)"
                                />
                                <Button
                                    v-if="data.status !== 'published'"
                                    :label="$t('admin_weekly_menus.actions.publish')"
                                    size="small"
                                    text
                                    class="min-h-11! text-green-700!"
                                    @click="publish(data)"
                                />
                                <Button
                                    v-else
                                    :label="$t('admin_weekly_menus.actions.unpublish')"
                                    size="small"
                                    text
                                    class="min-h-11! text-amber-700!"
                                    @click="unpublish(data)"
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
            :statuses="statuses"
            @submit="submitCreate"
        />
        <EditModal
            v-model:visible="editModalVisible"
            :form="form"
            :statuses="statuses"
            @submit="submitEdit"
        />
        <WeeklyMenuItemsModal
            v-model:visible="itemsVisible"
            :menu="selectedMenu"
            :products="products"
            @save-item="saveItem"
            @delete-item="deleteItem"
        />
        <ConfirmDialog />
    </div>
</template>
