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
import CreateModal from "@/Components/Admin/Purchases/CreateModal.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { perPageOptions } from "@/Utils/functions.js";
import { trans } from "laravel-vue-i18n";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    purchases: {
        type: Object,
        required: true,
    },
    suppliers: {
        type: Array,
        required: true,
    },
    ingredient_options: {
        type: Array,
        required: true,
    },
    statuses: {
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

const filterState = reactive({
    search: props.filters.search ?? "",
    status: props.filters.status ?? "",
    supplier_id: props.filters.supplier_id || null,
    sort_field: props.filters.sort_field ?? "purchase_date",
    sort_direction: props.filters.sort_direction ?? "desc",
    per_page: props.filters.per_page ?? 10,
});

const perPageOptions = perPageOptions(trans, [10, 20, 50]);
/*
const perPageOptions = [
    { label: '10 / oldal', value: 10 },
    { label: '20 / oldal', value: 20 },
    { label: '50 / oldal', value: 50 },
];
*/

const statusOptions = computed(() => [
    { label: "Mind", value: "" },
    ...props.statuses.map((status) => ({
        label: statusLabel(status),
        value: status,
    })),
]);

const supplierOptions = computed(() => [{ id: null, name: "Mind" }, ...props.suppliers]);
const ingredientOptions = computed(() =>
    props.ingredient_options.map((ingredient) => ({
        label: `${ingredient.name} (${ingredient.unit})`,
        value: ingredient.id,
        unit: ingredient.unit,
    }))
);

const newItem = () => ({ ingredient_id: null, quantity: 1, unit: "db", unit_cost: 0 });

const form = useForm({
    supplier_id: null,
    reference_number: "",
    purchase_date: new Date().toISOString().slice(0, 10),
    notes: "",
    items: [newItem()],
});

const sortOrder = computed(() => (filterState.sort_direction === "asc" ? 1 : -1));
const currentPage = computed(() => props.purchases.current_page ?? 1);
const first = computed(() => (currentPage.value - 1) * (props.purchases.per_page ?? 10));

const load = (extra = {}) => {
    loading.value = true;

    router.get(
        route("admin.purchases.index"),
        {
            search: filterState.search || undefined,
            status: filterState.status || undefined,
            supplier_id: filterState.supplier_id || undefined,
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
    filterState.supplier_id = null;
    filterState.sort_field = "purchase_date";
    filterState.sort_direction = "desc";
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
    form.reset();
    form.clearErrors();
    form.supplier_id = null;
    form.reference_number = "";
    form.purchase_date = new Date().toISOString().slice(0, 10);
    form.notes = "";
    form.items = [newItem()];
    createModalVisible.value = true;
};

const closeCreateModal = () => {
    createModalVisible.value = false;
};

const submitCreate = () => {
    form.post(route("admin.purchases.store"), {
        preserveScroll: true,
        onSuccess: () => {
            closeCreateModal();
            form.reset();
        },
    });
};

const postNow = (purchase) => {
    confirm.require({
        header: "Beszerzés könyvelése",
        message: `Könyvelés után a készlet azonnal frissül. Folytatod? (${
            purchase.reference_number || `#${purchase.id}`
        })`,
        rejectLabel: "Mégse",
        acceptLabel: "Könyvelés",
        accept: () => {
            router.post(
                route("admin.purchases.post", purchase.id),
                {},
                { preserveScroll: true }
            );
        },
    });
};

const cancelPurchase = (purchase) => {
    confirm.require({
        header: "Beszerzés stornózása",
        message: `Biztosan stornózod ezt a beszerzést? (${
            purchase.reference_number || `#${purchase.id}`
        })`,
        rejectLabel: "Mégse",
        acceptLabel: "Stornó",
        acceptClass: "p-button-danger",
        accept: () => {
            router.post(
                route("admin.purchases.cancel", purchase.id),
                {},
                { preserveScroll: true }
            );
        },
    });
};

const formatCurrency = (value) =>
    `${new Intl.NumberFormat("hu-HU", {
        maximumFractionDigits: 0,
    }).format(Number(value || 0))} Ft`;

const statusLabel = (status) => {
    const map = {
        draft: "Piszkozat",
        posted: "Könyvelt",
        cancelled: "Stornózott",
    };

    return map[status] ?? status;
};

const statusClass = (status) => {
    const map = {
        draft: "bg-amber-100 text-amber-800",
        posted: "bg-emerald-100 text-emerald-800",
        cancelled: "bg-rose-100 text-rose-800",
    };

    return map[status] ?? "bg-stone-100 text-stone-700";
};
</script>

<template>
    <Head title="Beszerzések" />

    <div class="space-y-6">
        <SectionTitle
            eyebrow="Admin / Beszerzések"
            title="Beszerzések"
            description="Termékek mintájára egységes táblás, szűrhető és modal alapú beszerzéskezelés."
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
                            placeholder="Referencia vagy megjegyzés"
                            @keyup.enter="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >Státusz</label
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
                            >Beszállító</label
                        >
                        <Select
                            v-model="filterState.supplier_id"
                            :options="supplierOptions"
                            option-label="name"
                            option-value="id"
                            class="w-full"
                            filter
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
                    <Button
                        icon="pi pi-filter-slash"
                        label="Szűrők törlése"
                        severity="secondary"
                        outlined
                        @click="clearFilters"
                    />
                    <Button icon="pi pi-search" label="Keresés" @click="submitFilters" />
                    <Button icon="pi pi-plus" label="Új beszerzés" @click="openCreate" />
                </template>
            </AdminTableToolbar>

            <div class="mt-4 overflow-x-auto">
                <DataTable
                    :value="purchases.data"
                    lazy
                    paginator
                    scrollable
                    :rows="purchases.per_page"
                    :first="first"
                    :total-records="purchases.total"
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
                            <p>Nincs megjeleníthető beszerzés.</p>
                            <div
                                class="mt-3 flex flex-wrap items-center justify-center gap-2"
                            >
                                <Button
                                    label="Szűrők törlése"
                                    outlined
                                    size="small"
                                    @click="clearFilters"
                                />
                                <Button
                                    label="Új beszerzés"
                                    size="small"
                                    @click="openCreate"
                                />
                            </div>
                        </div>
                    </template>

                    <Column field="purchase_date" header="Dátum" sortable>
                        <template #body="{ data }">
                            <span class="text-sm text-bakery-dark">{{
                                data.purchase_date || "-"
                            }}</span>
                        </template>
                    </Column>
                    <Column field="reference_number" header="Referencia" sortable>
                        <template #body="{ data }">
                            <span class="font-medium text-bakery-dark">{{
                                data.reference_number || `#${data.id}`
                            }}</span>
                        </template>
                    </Column>
                    <Column field="supplier_name" header="Beszállító">
                        <template #body="{ data }">
                            <span>{{ data.supplier_name || "Nincs megadva" }}</span>
                        </template>
                    </Column>
                    <Column field="items_count" header="Tételek" />
                    <Column field="total" header="Összesen" sortable>
                        <template #body="{ data }">
                            <span class="font-medium">{{
                                formatCurrency(data.total)
                            }}</span>
                        </template>
                    </Column>
                    <Column field="status" header="Státusz" sortable>
                        <template #body="{ data }">
                            <span
                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="statusClass(data.status)"
                            >
                                {{ statusLabel(data.status) }}
                            </span>
                        </template>
                    </Column>
                    <Column header="Műveletek" :exportable="false">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Link
                                    :href="route('admin.purchases.show', data.id)"
                                    class="inline-flex min-h-11 items-center rounded-md border border-bakery-brown/20 px-3 py-2 text-xs font-medium text-bakery-brown hover:bg-bakery-brown/10"
                                >
                                    Részletek
                                </Link>
                                <Button
                                    v-if="data.status === 'draft'"
                                    icon="pi pi-check"
                                    text
                                    rounded
                                    class="!h-11 !w-11"
                                    aria-label="Beszerzés könyvelése"
                                    @click="postNow(data)"
                                />
                                <Button
                                    v-if="data.status === 'draft'"
                                    icon="pi pi-times"
                                    text
                                    rounded
                                    severity="danger"
                                    class="!h-11 !w-11"
                                    aria-label="Beszerzés stornózása"
                                    @click="cancelPurchase(data)"
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
            :suppliers="suppliers"
            :ingredient-options="ingredientOptions"
            @submit="submitCreate"
        />
        <ConfirmDialog />
    </div>
</template>
