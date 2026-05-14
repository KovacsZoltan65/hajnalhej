<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import Button from "primevue/button";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import AdminTableToolbar from "@/Components/Admin/AdminTableToolbar.vue";
import BaseDataTable from "@/Components/Admin/Table/BaseDataTable.vue";
import RowActionMenu from "@/Components/Admin/Table/RowActionMenu.vue";
import CourierStatusBadge from "@/Components/Admin/Couriers/CourierStatusBadge.vue";
import CreateModal from "@/Components/Admin/Couriers/CreateModal.vue";
import DeleteModal from "@/Components/Admin/Couriers/DeleteModal.vue";
import EditModal from "@/Components/Admin/Couriers/EditModal.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { trans } from "laravel-vue-i18n";
import { useAdminFilterState } from "@/composables/useAdminFilterState.js";
import { useLocaleFormat } from "@/composables/useLocaleFormat.js";
import { pageOptions as createPerPageOptions } from "@/Utils/functions.js";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    couriers: { type: Object, required: true },
    filters: { type: Object, required: true },
    options: { type: Object, required: true },
    can: {
        type: Object,
        default: () => ({ create: false, update: false, delete: false }),
    },
});

const loading = ref(false);
const createModalVisible = ref(false);
const editModalVisible = ref(false);
const deleteModalVisible = ref(false);
const editingId = ref(null);
const deletingCourier = ref(null);
const deleteForm = useForm({});
const { formatDateTime } = useLocaleFormat();

const { filterState, sortOrder, submitFilters, clearFilters, onSort, onPage } = useAdminFilterState({
    filters: props.filters,
    defaults: {
        search: "",
        status: "",
        sort_field: "name",
        sort_direction: "asc",
        per_page: 10,
    },
    routeName: "admin.couriers.index",
    loading,
    toQuery: (state) => ({
        search: state.search || undefined,
        status: state.status || undefined,
        sort_field: state.sort_field,
        sort_direction: state.sort_direction,
        per_page: state.per_page,
    }),
});

const perPageOptions = createPerPageOptions(trans, [10, 20, 50]);
const formStatusOptions = computed(() => props.options.statusOptions.filter((option) => option.value !== ""));
const currentPage = computed(() => props.couriers.current_page ?? 1);
const first = computed(() => (currentPage.value - 1) * (props.couriers.per_page ?? 10));

const form = useForm({
    name: "",
    phone: "",
    email: "",
    status: "active",
    notes: "",
});

const formPayload = (data) => {
    return {
        name: data.name,
        phone: data.phone || null,
        email: data.email || null,
        status: data.status,
        notes: data.notes || null,
    };
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.name = "";
    form.phone = "";
    form.email = "";
    form.status = "active";
    form.notes = "";
};

const openCreate = () => {
    editModalVisible.value = false;
    editingId.value = null;
    resetForm();
    createModalVisible.value = true;
};

const openEdit = (courier) => {
    createModalVisible.value = false;
    editingId.value = courier.id;
    form.clearErrors();
    form.name = courier.name;
    form.phone = courier.phone ?? "";
    form.email = courier.email ?? "";
    form.status = courier.status;
    form.notes = courier.notes ?? "";
    editModalVisible.value = true;
};

const submitCreate = () => {
    form.transform(formPayload).post(route("admin.couriers.store"), {
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

    form.transform(formPayload).put(route("admin.couriers.update", editingId.value), {
        preserveScroll: true,
        onSuccess: () => {
            editModalVisible.value = false;
            resetForm();
            editingId.value = null;
        },
    });
};

const confirmDelete = (courier) => {
    deletingCourier.value = courier;
    deleteModalVisible.value = true;
};

const deleteCourier = () => {
    if (!deletingCourier.value) {
        return;
    }

    deleteForm.delete(route("admin.couriers.destroy", deletingCourier.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleteModalVisible.value = false;
            deletingCourier.value = null;
        },
    });
};

const actionItemsFor = (courier) =>
    [
        props.can.update
            ? {
                  label: trans("admin_couriers.actions.edit"),
                  icon: "pi pi-pencil",
                  command: () => openEdit(courier),
              }
            : null,
        props.can.delete
            ? {
                  label: trans("admin_couriers.actions.delete"),
                  icon: "pi pi-trash",
                  command: () => confirmDelete(courier),
              }
            : null,
    ].filter(Boolean);
</script>

<template>
    <Head :title="$t('admin_couriers.meta_title')" />

    <div class="space-y-6">
        <SectionTitle
            :eyebrow="$t('admin_couriers.eyebrow')"
            :title="$t('admin_couriers.title')"
            :description="$t('admin_couriers.description')"
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <AdminTableToolbar :filters-grid-class="'grid gap-3 sm:grid-cols-2 xl:grid-cols-4'">
                <template #filters>
                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">
                            {{ $t("common.search") }}
                        </label>
                        <InputText
                            v-model="filterState.search"
                            :placeholder="$t('admin_couriers.search_placeholder')"
                            class="w-full"
                            @keyup.enter="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">
                            {{ $t("common.status") }}
                        </label>
                        <Select
                            v-model="filterState.status"
                            :options="options.statusOptions"
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
                    <Button
                        v-if="can.create"
                        icon="pi pi-plus"
                        :label="$t('admin_couriers.actions.create')"
                        @click="openCreate"
                    />
                </template>
            </AdminTableToolbar>

            <div class="mt-4 overflow-x-auto">
                <BaseDataTable
                    :value="couriers.data"
                    lazy
                    paginator
                    scrollable
                    :rows="couriers.per_page"
                    :first="first"
                    :total-records="couriers.total"
                    :loading="loading"
                    data-key="id"
                    sort-mode="single"
                    :sort-field="filterState.sort_field"
                    :sort-order="sortOrder"
                    :empty-title="$t('admin_couriers.empty_title')"
                    :empty-description="$t('admin_couriers.empty')"
                    :empty-primary-label="can.create ? $t('admin_couriers.actions.create') : ''"
                    :empty-secondary-label="$t('common.clear_filters')"
                    @sort="onSort"
                    @page="onPage"
                    @empty-primary="openCreate"
                    @empty-secondary="clearFilters"
                >
                    <Column field="name" :header="$t('common.name')" sortable>
                        <template #body="{ data }">
                            <div>
                                <p class="font-semibold text-bakery-dark">{{ data.name }}</p>
                                <p class="text-xs text-bakery-dark/60">{{ data.email || "-" }}</p>
                            </div>
                        </template>
                    </Column>
                    <Column field="phone" :header="$t('common.phone')" sortable />
                    <Column field="email" :header="$t('common.email')" sortable>
                        <template #body="{ data }">
                            {{ data.email || "-" }}
                        </template>
                    </Column>
                    <Column field="status" :header="$t('common.status')" sortable>
                        <template #body="{ data }">
                            <CourierStatusBadge :status="data.status" />
                        </template>
                    </Column>
                    <Column field="created_at" :header="$t('common.create')" sortable>
                        <template #body="{ data }">
                            {{ formatDateTime(data.created_at) }}
                        </template>
                    </Column>
                    <Column :header="$t('common.actions')" :exportable="false">
                        <template #body="{ data }">
                            <RowActionMenu v-if="actionItemsFor(data).length" :items="actionItemsFor(data)" />
                        </template>
                    </Column>
                </BaseDataTable>
            </div>
        </div>

        <CreateModal
            v-model:visible="createModalVisible"
            :form="form"
            :status-options="formStatusOptions"
            @submit="submitCreate"
        />
        <EditModal
            v-model:visible="editModalVisible"
            :form="form"
            :status-options="formStatusOptions"
            @submit="submitEdit"
        />
        <DeleteModal
            v-model:visible="deleteModalVisible"
            :courier="deletingCourier"
            :processing="deleteForm.processing"
            @confirm="deleteCourier"
        />
    </div>
</template>
