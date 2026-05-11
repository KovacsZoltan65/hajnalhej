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
import BranchStatusBadge from "@/Components/Admin/Branches/BranchStatusBadge.vue";
import BranchTypeBadge from "@/Components/Admin/Branches/BranchTypeBadge.vue";
import CreateModal from "@/Components/Admin/Branches/CreateModal.vue";
import EditModal from "@/Components/Admin/Branches/EditModal.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { trans } from "laravel-vue-i18n";
import { useAdminFilterState } from "@/composables/useAdminFilterState.js";
import { pageOptions as createPerPageOptions } from "@/Utils/functions.js";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    branches: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    options: {
        type: Object,
        required: true,
    },
    can: {
        type: Object,
        default: () => ({ create: false, update: false, delete: false }),
    },
});

const confirm = useConfirm();
const loading = ref(false);
const createModalVisible = ref(false);
const editModalVisible = ref(false);
const editingId = ref(null);

const { filterState, sortOrder, submitFilters, clearFilters, onSort, onPage } = useAdminFilterState({
    filters: props.filters,
    defaults: {
        search: "",
        type: "",
        active: "",
        sort_field: "name",
        sort_direction: "asc",
        per_page: 10,
    },
    routeName: "admin.branches.index",
    loading,
    toQuery: (state) => ({
        search: state.search || undefined,
        type: state.type || undefined,
        active: state.active === "" ? undefined : state.active,
        sort_field: state.sort_field,
        sort_direction: state.sort_direction,
        per_page: state.per_page,
    }),
});

const perPageOptions = createPerPageOptions(trans, [10, 20, 50]);
const typeFilterOptions = computed(() => [{ value: "", label: trans("common.all") }, ...props.options.types]);

const form = useForm({
    name: "",
    code: "",
    type: "bakery",
    email: "",
    phone: "",
    address: "",
    active: true,
    meta_json: "",
});

const currentPage = computed(() => props.branches.current_page ?? 1);
const first = computed(() => (currentPage.value - 1) * (props.branches.per_page ?? 10));

const metaToText = (meta) => (meta && Object.keys(meta).length > 0 ? JSON.stringify(meta, null, 2) : "");

const formPayload = (data) => {
    const metaText = String(data.meta_json ?? "").trim();

    return {
        name: data.name,
        code: data.code,
        type: data.type,
        email: data.email || null,
        phone: data.phone || null,
        address: data.address || null,
        active: data.active,
        meta: metaText === "" ? null : JSON.parse(metaText),
    };
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.name = "";
    form.code = "";
    form.type = "bakery";
    form.email = "";
    form.phone = "";
    form.address = "";
    form.active = true;
    form.meta_json = "";
};

const openCreate = () => {
    editModalVisible.value = false;
    editingId.value = null;
    resetForm();
    createModalVisible.value = true;
};

const openEdit = (branch) => {
    createModalVisible.value = false;
    editingId.value = branch.id;
    form.clearErrors();
    form.name = branch.name;
    form.code = branch.code;
    form.type = branch.type;
    form.email = branch.email ?? "";
    form.phone = branch.phone ?? "";
    form.address = branch.address ?? "";
    form.active = branch.active;
    form.meta_json = metaToText(branch.meta);
    editModalVisible.value = true;
};

const submitCreate = () => {
    try {
        form.transform(formPayload).post(route("admin.branches.store"), {
            preserveScroll: true,
            onSuccess: () => {
                createModalVisible.value = false;
                resetForm();
            },
        });
    } catch {
        form.setError("meta", trans("admin_branches.meta_invalid"));
    }
};

const submitEdit = () => {
    if (!editingId.value) {
        return;
    }

    try {
        form.transform(formPayload).put(route("admin.branches.update", editingId.value), {
            preserveScroll: true,
            onSuccess: () => {
                editModalVisible.value = false;
                resetForm();
                editingId.value = null;
            },
        });
    } catch {
        form.setError("meta", trans("admin_branches.meta_invalid"));
    }
};

const confirmDelete = (branch) => {
    confirm.require({
        header: trans("admin_branches.confirm_delete_header"),
        message: trans("admin_branches.confirm_delete_message", { name: branch.name }),
        rejectLabel: trans("common.cancel"),
        acceptLabel: trans("common.delete"),
        acceptClass: "p-button-danger",
        accept: () => {
            router.delete(route("admin.branches.destroy", branch.id), {
                preserveScroll: true,
            });
        },
    });
};
</script>

<template>
    <Head :title="$t('admin_branches.meta_title')" />

    <div class="space-y-6">
        <SectionTitle
            :eyebrow="$t('admin_branches.eyebrow')"
            :title="$t('admin_branches.title')"
            :description="$t('admin_branches.description')"
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
                            :placeholder="$t('admin_branches.search_placeholder')"
                            class="w-full"
                            @keyup.enter="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">
                            {{ $t("admin_branches.branch_type") }}
                        </label>
                        <Select
                            v-model="filterState.type"
                            :options="typeFilterOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">
                            {{ $t("common.status") }}
                        </label>
                        <Select
                            v-model="filterState.active"
                            :options="options.activeOptions"
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
                        :label="$t('admin_branches.actions.create')"
                        @click="openCreate"
                    />
                </template>
            </AdminTableToolbar>

            <div class="mt-4 overflow-x-auto">
                <DataTable
                    :value="branches.data"
                    lazy
                    paginator
                    scrollable
                    :rows="branches.per_page"
                    :first="first"
                    :total-records="branches.total"
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
                            <p>{{ $t("admin_branches.empty") }}</p>
                            <div class="mt-3 flex flex-wrap items-center justify-center gap-2">
                                <Button
                                    :label="$t('common.clear_filters')"
                                    outlined
                                    size="small"
                                    @click="clearFilters"
                                />
                                <Button
                                    v-if="can.create"
                                    :label="$t('admin_branches.actions.create')"
                                    size="small"
                                    @click="openCreate"
                                />
                            </div>
                        </div>
                    </template>

                    <Column field="name" :header="$t('common.name')" sortable>
                        <template #body="{ data }">
                            <div>
                                <p class="font-semibold text-bakery-dark">{{ data.name }}</p>
                                <p class="text-xs text-bakery-dark/60">{{ data.code }}</p>
                            </div>
                        </template>
                    </Column>
                    <Column field="type" :header="$t('admin_branches.branch_type')" sortable>
                        <template #body="{ data }">
                            <BranchTypeBadge :type="data.type" :label="data.type_label" />
                        </template>
                    </Column>
                    <Column field="email" :header="$t('common.email')" sortable />
                    <Column field="phone" :header="$t('common.phone')" sortable />
                    <Column field="address" :header="$t('common.address')" sortable />
                    <Column field="active" :header="$t('common.status')" sortable>
                        <template #body="{ data }">
                            <BranchStatusBadge :active="data.active" />
                        </template>
                    </Column>
                    <Column :header="$t('common.actions')" :exportable="false">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Button
                                    v-if="can.update"
                                    icon="pi pi-pencil"
                                    text
                                    rounded
                                    class="h-11! w-11!"
                                    :aria-label="$t('admin_branches.actions.edit')"
                                    @click="openEdit(data)"
                                />
                                <Button
                                    v-if="can.delete"
                                    icon="pi pi-trash"
                                    text
                                    rounded
                                    severity="danger"
                                    class="h-11! w-11!"
                                    :aria-label="$t('admin_branches.actions.delete')"
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
            :type-options="options.types"
            @submit="submitCreate"
        />
        <EditModal v-model:visible="editModalVisible" :form="form" :type-options="options.types" @submit="submitEdit" />
        <ConfirmDialog />
    </div>
</template>
