<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { computed, reactive, ref } from "vue";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Button from "primevue/button";

import AdminTableToolbar from "@/Components/Admin/AdminTableToolbar.vue";
import OrderStatusBadge from "@/Components/Orders/OrderStatusBadge.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { trans } from "laravel-vue-i18n";
import { perPageOptions } from "@/Utils/functions.js";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    orders: {
        type: Object,
        required: true,
    },
    statusOptions: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const loading = ref(false);

const filterState = reactive({
    search: props.filters.search ?? "",
    status: props.filters.status ?? "",
    sort_field: props.filters.sort_field ?? "placed_at",
    sort_direction: props.filters.sort_direction ?? "desc",
    per_page: props.filters.per_page ?? 15,
});

const currentPage = computed(() => props.orders.current_page ?? 1);
const first = computed(() => (currentPage.value - 1) * (props.orders.per_page ?? 15));
const sortOrder = computed(() => (filterState.sort_direction === "asc" ? 1 : -1));

const perPageOptions = perPageOptions(trans, [10, 20, 50]);

/*
const perPageOptions = [
    { label: trans("common.page_count", { count: 15 }), value: 15 },
    { label: trans("common.page_count", { count: 30 }), value: 30 },
    { label: trans("common.page_count", { count: 50 }), value: 50 },
];
*/

const statusSelectOptions = computed(() => [
    { label: trans("common.all"), value: "" },
    ...props.statusOptions.map((status) => ({ label: status, value: status })),
]);

const formatCurrency = (value) =>
    new Intl.NumberFormat(trans("common.locale"), {
        style: "currency",
        currency: trans("common.currency"),
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));

const load = (extra = {}) => {
    loading.value = true;

    router.get(
        route("admin.orders.index"),
        {
            search: filterState.search || undefined,
            status: filterState.status || undefined,
            sort_field: filterState.sort_field,
            sort_direction: filterState.sort_direction,
            per_page: filterState.per_page,
            ...extra,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
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
    filterState.sort_field = "placed_at";
    filterState.sort_direction = "desc";
    filterState.per_page = 15;
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
</script>

<template>
    <Head :title="$t('admin_orders.meta_title')" />

    <div class="space-y-6">
        <SectionTitle
            :eyebrow="$t('admin_orders.eyebrow')"
            :title="$t('admin_orders.title')"
            :description="$t('admin_orders.description')"
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <AdminTableToolbar>
                <template #filters>
                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_orders.filters.search") }}</label
                        >
                        <InputText
                            v-model="filterState.search"
                            class="w-full"
                            :placeholder="$t('admin_orders.filters.search_placeholder')"
                            @keyup.enter="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_orders.filters.status") }}</label
                        >
                        <Select
                            v-model="filterState.status"
                            :options="statusSelectOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_orders.filters.per_page") }}</label
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
                        :label="$t('admin_orders.actions.search')"
                        @click="submitFilters"
                    />
                </template>
            </AdminTableToolbar>

            <div class="mt-4 overflow-x-auto">
                <DataTable
                    :value="orders.data"
                    lazy
                    paginator
                    scrollable
                    :rows="orders.per_page"
                    :first="first"
                    :total-records="orders.total"
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
                            <p>{{ $t("admin_orders.empty") }}</p>
                            <div
                                class="mt-3 flex flex-wrap items-center justify-center gap-2"
                            >
                                <Button
                                    :label="$t('common.clear_filters')"
                                    outlined
                                    size="small"
                                    @click="clearFilters"
                                />
                            </div>
                        </div>
                    </template>

                    <Column
                        field="order_number"
                        :header="$t('admin_orders.columns.identifier')"
                        sortable
                    />
                    <Column
                        field="customer_name"
                        :header="$t('admin_orders.columns.customer')"
                        sortable
                    >
                        <template #body="{ data }">
                            <div>
                                <p class="font-medium text-bakery-dark">
                                    {{ data.customer_name }}
                                </p>
                                <p class="text-xs text-bakery-dark/65">
                                    {{ data.customer_email }}
                                </p>
                            </div>
                        </template>
                    </Column>
                    <Column
                        field="status"
                        :header="$t('admin_orders.columns.status')"
                        sortable
                    >
                        <template #body="{ data }">
                            <OrderStatusBadge :status="data.status" />
                        </template>
                    </Column>
                    <Column
                        field="pickup_date"
                        :header="$t('admin_orders.columns.pickup')"
                        sortable
                    >
                        <template #body="{ data }">
                            <div>
                                <p>{{ data.pickup_date || "-" }}</p>
                                <p class="text-xs text-bakery-dark/65">
                                    {{ data.pickup_time_slot || "-" }}
                                </p>
                            </div>
                        </template>
                    </Column>
                    <Column
                        field="total"
                        :header="$t('admin_orders.columns.total')"
                        sortable
                    >
                        <template #body="{ data }">
                            {{ formatCurrency(data.total) }}
                        </template>
                    </Column>
                    <Column :header="$t('common.actions')">
                        <template #body="{ data }">
                            <Link
                                :href="route('admin.orders.show', data.id)"
                                class="inline-flex min-h-11 items-center rounded-md border border-bakery-brown/20 px-3 py-2 text-xs font-medium text-bakery-brown hover:bg-bakery-brown/10"
                            >
                                {{ $t("admin_orders.actions.details") }}
                            </Link>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </div>
</template>
