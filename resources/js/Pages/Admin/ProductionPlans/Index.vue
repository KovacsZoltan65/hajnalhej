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
import { trans } from "laravel-vue-i18n";
import AdminTableToolbar from "@/Components/Admin/AdminTableToolbar.vue";
import CreateModal from "@/Components/Admin/ProductionPlans/CreateModal.vue";
import EditModal from "@/Components/Admin/ProductionPlans/EditModal.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { perPageOptions } from "@/Utils/functions.js";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    productionPlans: { type: Object, required: true },
    products: { type: Array, required: true },
    statuses: { type: Array, required: true },
    filters: { type: Object, required: true },
    summary: { type: Object, required: true },
});

const confirm = useConfirm();
const loading = ref(false);
const createModalVisible = ref(false);
const editModalVisible = ref(false);
const editingId = ref(null);

const filterState = reactive({
    search: props.filters.search ?? "",
    status: props.filters.status ?? "",
    target_from: props.filters.target_from ?? "",
    target_to: props.filters.target_to ?? "",
    sort_field: props.filters.sort_field ?? "target_at",
    sort_direction: props.filters.sort_direction ?? "asc",
    per_page: props.filters.per_page ?? 10,
});

const statusOptions = computed(() => [
    { value: "", label: trans("common.all") },
    ...props.statuses,
]);

const perPageOptions = perPageOptions(trans, [10, 20, 50]);
/*
const perPageOptions = computed(() =>
    [10, 20, 50].map((count) => ({
        label: trans('admin_production_plans.filters.per_page_option', { count }),
        value: count,
    })),
);
*/

const form = useForm({
    target_ready_at: "",
    status: "draft",
    is_locked: false,
    notes: "",
    items: [],
});

const selectedPlan = computed(
    () => props.productionPlans.data.find((plan) => plan.id === editingId.value) ?? null
);
const sortOrder = computed(() => (filterState.sort_direction === "asc" ? 1 : -1));
const currentPage = computed(() => props.productionPlans.current_page ?? 1);
const first = computed(
    () => (currentPage.value - 1) * (props.productionPlans.per_page ?? 10)
);

const makeDefaultItem = () => ({
    product_id: props.products[0]?.id ?? null,
    target_quantity: 1,
    unit_label: "db",
    sort_order: 0,
});

const mapItemsFromPlan = (plan) => {
    const items = plan?.items ?? [];

    if (items.length === 0) {
        return [makeDefaultItem()];
    }

    return items.map((item, index) => ({
        product_id: item.product_id,
        target_quantity: item.target_quantity,
        unit_label: item.unit_label ?? "db",
        sort_order: item.sort_order ?? index,
    }));
};

const mapDateTimeForInput = (value) => {
    if (!value) {
        return "";
    }

    return String(value).slice(0, 16);
};

const resetFormToDefaults = () => {
    form.clearErrors();
    form.target_ready_at = "";
    form.status = "draft";
    form.is_locked = false;
    form.notes = "";
    form.items = [makeDefaultItem()];
};

const load = (extra = {}) => {
    loading.value = true;

    router.get(
        route("admin.production-plans.index"),
        {
            search: filterState.search || undefined,
            status: filterState.status || undefined,
            target_from: filterState.target_from || undefined,
            target_to: filterState.target_to || undefined,
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
    filterState.status = "";
    filterState.target_from = "";
    filterState.target_to = "";
    filterState.sort_field = "target_at";
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
    editingId.value = null;
    editModalVisible.value = false;
    resetFormToDefaults();
    createModalVisible.value = true;
};

const openEdit = (plan) => {
    createModalVisible.value = false;
    editingId.value = plan.id;
    form.clearErrors();
    form.target_ready_at = mapDateTimeForInput(plan.target_ready_at ?? plan.target_at);
    form.status = plan.status;
    form.is_locked = Boolean(plan.is_locked);
    form.notes = plan.details?.notes ?? "";
    form.items = mapItemsFromPlan(plan);
    editModalVisible.value = true;
};

const closeCreateModal = () => {
    createModalVisible.value = false;
};

const closeEditModal = () => {
    editModalVisible.value = false;
    editingId.value = null;
};

const submitCreate = () => {
    form.post(route("admin.production-plans.store"), {
        preserveScroll: true,
        onSuccess: () => {
            closeCreateModal();
            resetFormToDefaults();
        },
    });
};

const submitEdit = () => {
    if (!editingId.value) {
        return;
    }

    form.put(route("admin.production-plans.update", editingId.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
            resetFormToDefaults();
        },
    });
};

const confirmDelete = (plan) => {
    confirm.require({
        header: trans("admin_production_plans.confirm_delete_header"),
        message: trans("admin_production_plans.confirm_delete_message", {
            number: plan.plan_number,
        }),
        rejectLabel: trans("common.cancel"),
        acceptLabel: trans("common.delete"),
        acceptClass: "p-button-danger",
        accept: () => {
            router.delete(route("admin.production-plans.destroy", plan.id), {
                preserveScroll: true,
            });
        },
    });
};
</script>

<template>
    <Head :title="trans('admin_production_plans.meta_title')" />

    <div class="space-y-6">
        <SectionTitle
            :eyebrow="trans('admin_production_plans.eyebrow')"
            :title="trans('admin_production_plans.title')"
            :description="trans('admin_production_plans.description')"
        />

        <div
            class="grid gap-3 rounded-2xl border border-bakery-brown/15 bg-white/85 p-4 sm:grid-cols-2 xl:grid-cols-4"
        >
            <div class="rounded-xl bg-[#fcf8f1] p-3">
                <p class="text-xs uppercase tracking-[0.14em] text-bakery-brown/75">
                    {{ trans("admin_production_plans.summary.total_plans") }}
                </p>
                <p class="mt-1 text-2xl font-semibold text-bakery-dark">
                    {{ summary.total_plans }}
                </p>
            </div>
            <div class="rounded-xl bg-[#fcf8f1] p-3">
                <p class="text-xs uppercase tracking-[0.14em] text-bakery-brown/75">
                    {{ trans("admin_production_plans.summary.ready_plans") }}
                </p>
                <p class="mt-1 text-2xl font-semibold text-bakery-dark">
                    {{ summary.ready_plans }}
                </p>
            </div>
            <div class="rounded-xl bg-[#fcf8f1] p-3">
                <p class="text-xs uppercase tracking-[0.14em] text-bakery-brown/75">
                    {{ trans("admin_production_plans.summary.draft_plans") }}
                </p>
                <p class="mt-1 text-2xl font-semibold text-bakery-dark">
                    {{ summary.draft_plans }}
                </p>
            </div>
            <div class="rounded-xl bg-[#fcf8f1] p-3">
                <p class="text-xs uppercase tracking-[0.14em] text-bakery-brown/75">
                    {{ trans("admin_production_plans.summary.total_recipe_minutes") }}
                </p>
                <p class="mt-1 text-2xl font-semibold text-bakery-dark">
                    {{
                        trans("admin_production_plans.units.minutes", {
                            count: summary.total_recipe_minutes,
                        })
                    }}
                </p>
            </div>
        </div>

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <AdminTableToolbar
                :filters-grid-class="'grid gap-3 sm:grid-cols-2 xl:grid-cols-5'"
            >
                <template #filters>
                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ trans("admin_production_plans.filters.search") }}</label
                        >
                        <InputText
                            v-model="filterState.search"
                            class="w-full"
                            :placeholder="
                                trans('admin_production_plans.filters.search_placeholder')
                            "
                            @keyup.enter="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ trans("admin_production_plans.filters.status") }}</label
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
                            >{{
                                trans("admin_production_plans.filters.target_from")
                            }}</label
                        >
                        <InputText
                            v-model="filterState.target_from"
                            type="date"
                            class="w-full"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{
                                trans("admin_production_plans.filters.target_to")
                            }}</label
                        >
                        <InputText
                            v-model="filterState.target_to"
                            type="date"
                            class="w-full"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ trans("admin_production_plans.filters.per_page") }}</label
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
                        :label="trans('common.search')"
                        @click="submitFilters"
                    />
                    <Button
                        icon="pi pi-plus"
                        :label="trans('admin_production_plans.actions.create')"
                        @click="openCreate"
                    />
                </template>
            </AdminTableToolbar>

            <div class="mt-4 overflow-x-auto">
                <DataTable
                    :value="productionPlans.data"
                    lazy
                    paginator
                    scrollable
                    :rows="productionPlans.per_page"
                    :first="first"
                    :total-records="productionPlans.total"
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
                            <p>{{ trans("admin_production_plans.empty") }}</p>
                            <div
                                class="mt-3 flex flex-wrap items-center justify-center gap-2"
                            >
                                <Button
                                    :label="trans('common.clear_filters')"
                                    outlined
                                    size="small"
                                    @click="clearFilters"
                                />
                                <Button
                                    :label="
                                        trans('admin_production_plans.actions.create')
                                    "
                                    size="small"
                                    @click="openCreate"
                                />
                            </div>
                        </div>
                    </template>

                    <Column
                        field="plan_number"
                        :header="trans('admin_production_plans.columns.plan')"
                        sortable
                    />
                    <Column
                        field="target_at"
                        :header="trans('admin_production_plans.columns.target_time')"
                        sortable
                    />
                    <Column
                        field="planned_start_at"
                        :header="trans('admin_production_plans.columns.planned_start')"
                        sortable
                    />
                    <Column
                        field="status"
                        :header="trans('admin_production_plans.columns.status')"
                        sortable
                    />
                    <Column
                        field="total_recipe_minutes"
                        :header="
                            trans('admin_production_plans.columns.total_time_minutes')
                        "
                        sortable
                    />
                    <Column
                        field="items_count"
                        :header="trans('admin_production_plans.columns.items_count')"
                        sortable
                    />
                    <Column :header="trans('common.actions')">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Button
                                    icon="pi pi-pencil"
                                    text
                                    rounded
                                    class="!h-11 !w-11"
                                    :aria-label="
                                        trans('admin_production_plans.actions.edit')
                                    "
                                    @click="openEdit(data)"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    text
                                    rounded
                                    severity="danger"
                                    class="!h-11 !w-11"
                                    :aria-label="
                                        trans('admin_production_plans.actions.delete')
                                    "
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
            :products="products"
            :statuses="statuses"
            @submit="submitCreate"
        />
        <EditModal
            v-model:visible="editModalVisible"
            :form="form"
            :products="products"
            :statuses="statuses"
            :selected-plan="selectedPlan"
            @submit="submitEdit"
        />
        <ConfirmDialog />
    </div>
</template>
