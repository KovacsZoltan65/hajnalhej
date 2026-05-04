<script setup>
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { computed, reactive, ref } from "vue";
import { trans } from "laravel-vue-i18n";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import InputText from "primevue/inputtext";
import Select from "primevue/select";

import AdminTableToolbar from "@/Components/Admin/AdminTableToolbar.vue";
import PermissionBadge from "@/Components/Admin/Permissions/PermissionBadge.vue";
import PermissionDangerBadge from "@/Components/Admin/Permissions/PermissionDangerBadge.vue";
import PermissionRegistryStateBadge from "@/Components/Admin/Permissions/PermissionRegistryStateBadge.vue";
import PermissionSyncSummaryModal from "@/Components/Admin/Permissions/PermissionSyncSummaryModal.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    permissions: {
        type: Object,
        required: true,
    },
    modules: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    can: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const loading = ref(false);
const syncing = ref(false);
const syncSummaryVisible = ref(Boolean(page.props.flash?.sync_summary));

const filterState = reactive({
    search: props.filters.search ?? "",
    module: props.filters.module ?? "",
    dangerous_only: props.filters.dangerous_only ?? false,
    usage_state: props.filters.usage_state ?? "",
    registry_state: props.filters.registry_state ?? "",
    sort_field: props.filters.sort_field ?? "name",
    sort_direction: props.filters.sort_direction ?? "asc",
    per_page: props.filters.per_page ?? 20,
});

const currentPage = computed(() => props.permissions.current_page ?? 1);
const first = computed(
    () => (currentPage.value - 1) * (props.permissions.per_page ?? 20)
);
const moduleLabel = (moduleName) => moduleName;

const moduleOptions = computed(() => [
    { label: trans("admin_permissions.filters.all_modules"), value: "" },
    ...props.modules.map((moduleName) => ({
        label: moduleLabel(moduleName),
        value: moduleName,
    })),
]);

const usageOptions = [
    { label: trans("common.all"), value: "" },
    { label: trans("admin_permissions.usage_states.used"), value: "used" },
    { label: trans("admin_permissions.usage_states.unused"), value: "unused" },
];

const registryStateOptions = [
    { label: trans("admin_permissions.filters.all_registry_states"), value: "" },
    { label: trans("admin_permissions.registry_states.synced"), value: "synced" },
    {
        label: trans("admin_permissions.registry_states.missing_in_db"),
        value: "missing_in_db",
    },
    {
        label: trans("admin_permissions.registry_states.orphan_db_only"),
        value: "orphan_db_only",
    },
];

const sortFieldOptions = [
    { label: trans("common.name"), value: "name" },
    { label: trans("admin_permissions.sort_fields.module"), value: "module" },
    { label: trans("admin_permissions.sort_fields.roles_count"), value: "roles_count" },
    { label: trans("admin_permissions.sort_fields.users_count"), value: "users_count" },
    {
        label: trans("admin_permissions.sort_fields.registry_state"),
        value: "registry_state",
    },
];

const sortDirectionOptions = [
    { label: trans("admin_permissions.sort_directions.asc"), value: "asc" },
    { label: trans("admin_permissions.sort_directions.desc"), value: "desc" },
];

import { perPageOptions } from "@/Utils/functions.js";
import { trans } from "laravel-vue-i18n";
const perPageOptions = perPageOptions(trans, [20, 50, 100]);
/*
const perPageOptions = [
    { label: trans("common.page_count", { count: 20 }), value: 20 },
    { label: trans("common.page_count", { count: 50 }), value: 50 },
    { label: trans("common.page_count", { count: 100 }), value: 100 },
];
*/

const load = (extra = {}) => {
    loading.value = true;

    router.get(
        route("admin.permissions.index"),
        {
            search: filterState.search || undefined,
            module: filterState.module || undefined,
            dangerous_only: filterState.dangerous_only ? 1 : undefined,
            usage_state: filterState.usage_state || undefined,
            registry_state: filterState.registry_state || undefined,
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

const onPage = (event) => {
    filterState.per_page = event.rows;
    load({ page: event.page + 1, per_page: event.rows });
};

const runSync = () => {
    syncing.value = true;
    router.post(
        route("admin.permissions.sync"),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                syncing.value = false;
                syncSummaryVisible.value = Boolean(page.props.flash?.sync_summary);
            },
        }
    );
};
const clearFilters = () => {
    filterState.search = "";
    filterState.module = "";
    filterState.dangerous_only = false;
    filterState.usage_state = "";
    filterState.registry_state = "";
    filterState.sort_field = "name";
    filterState.sort_direction = "asc";
    filterState.per_page = 20;
    submitFilters();
};
</script>

<template>
    <Head :title="$t('admin_permissions.meta_title')" />

    <div class="space-y-6">
        <SectionTitle
            :eyebrow="$t('admin_permissions.eyebrow')"
            :title="$t('admin_permissions.title')"
            :description="$t('admin_permissions.description')"
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <AdminTableToolbar
                :filters-grid-class="'grid gap-3 md:grid-cols-2 xl:grid-cols-4'"
            >
                <template #filters>
                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_permissions.filters.search") }}</label
                        >
                        <InputText
                            v-model="filterState.search"
                            class="w-full"
                            :placeholder="
                                $t('admin_permissions.filters.search_placeholder')
                            "
                            @keyup.enter="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_permissions.filters.module") }}</label
                        >
                        <Select
                            v-model="filterState.module"
                            :options="moduleOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_permissions.filters.usage") }}</label
                        >
                        <Select
                            v-model="filterState.usage_state"
                            :options="usageOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_permissions.filters.registry_state") }}</label
                        >
                        <Select
                            v-model="filterState.registry_state"
                            :options="registryStateOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_permissions.filters.sort_field") }}</label
                        >
                        <Select
                            v-model="filterState.sort_field"
                            :options="sortFieldOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_permissions.filters.sort_direction") }}</label
                        >
                        <Select
                            v-model="filterState.sort_direction"
                            :options="sortDirectionOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_permissions.filters.per_page") }}</label
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

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("admin_permissions.filters.dangerous_only") }}</label
                        >
                        <div
                            class="flex h-10 items-center gap-2 rounded-lg border border-bakery-brown/15 px-3"
                        >
                            <Checkbox
                                v-model="filterState.dangerous_only"
                                binary
                                input-id="dangerous-only"
                                @change="submitFilters"
                            />
                            <label
                                for="dangerous-only"
                                class="text-sm text-bakery-dark"
                                >{{
                                    $t("admin_permissions.filters.dangerous_only")
                                }}</label
                            >
                        </div>
                    </div>
                </template>

                <template #actions>
                    <Button
                        icon="pi pi-search"
                        :label="$t('admin_permissions.actions.filter')"
                        @click="submitFilters"
                    />
                    <Button
                        v-if="can.sync"
                        icon="pi pi-sync"
                        :label="$t('admin_permissions.actions.registry_sync')"
                        severity="contrast"
                        :loading="syncing"
                        :disabled="syncing"
                        @click="runSync"
                    />
                </template>
            </AdminTableToolbar>

            <div class="mt-4 overflow-x-auto">
                <DataTable
                    :value="permissions.data"
                    lazy
                    paginator
                    scrollable
                    :rows="permissions.per_page"
                    :first="first"
                    :total-records="permissions.total"
                    :loading="loading"
                    data-key="name"
                    paginator-template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                    @page="onPage"
                >
                    <template #empty>
                        <div
                            class="rounded-xl border border-dashed border-bakery-brown/25 bg-[#fcf7ef] p-6 text-center text-sm text-bakery-dark/70"
                        >
                            <p>{{ $t("admin_permissions.empty") }}</p>
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
                                    v-if="can.sync"
                                    :label="$t('admin_permissions.actions.registry_sync')"
                                    size="small"
                                    @click="runSync"
                                />
                            </div>
                        </div>
                    </template>

                    <Column
                        field="name"
                        :header="$t('admin_permissions.columns.permission')"
                    >
                        <template #body="{ data }">
                            <PermissionBadge :name="data.name" />
                        </template>
                    </Column>

                    <Column
                        field="module"
                        :header="$t('admin_permissions.columns.module')"
                    >
                        <template #body="{ data }">
                            {{ moduleLabel(data.module) }}
                        </template>
                    </Column>

                    <Column
                        field="dangerous"
                        :header="$t('admin_permissions.columns.risk')"
                    >
                        <template #body="{ data }">
                            <PermissionDangerBadge :dangerous="data.dangerous" />
                        </template>
                    </Column>

                    <Column
                        field="registry_state"
                        :header="$t('admin_permissions.columns.registry_state')"
                    >
                        <template #body="{ data }">
                            <PermissionRegistryStateBadge :state="data.registry_state" />
                        </template>
                    </Column>

                    <Column
                        field="roles_count"
                        :header="$t('admin_permissions.columns.roles')"
                    />
                    <Column
                        field="users_count"
                        :header="$t('admin_permissions.columns.users')"
                    />
                    <Column
                        field="guard_name"
                        :header="$t('admin_permissions.columns.guard')"
                    />

                    <Column :header="$t('common.actions')" :style="{ width: '9rem' }">
                        <template #body="{ data }">
                            <Link :href="route('admin.permissions.show', data.name)">
                                <Button
                                    :label="$t('admin_permissions.actions.details')"
                                    size="small"
                                    text
                                    class="!min-h-11"
                                />
                            </Link>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </div>

    <PermissionSyncSummaryModal
        v-model:visible="syncSummaryVisible"
        :summary="page.props.flash?.sync_summary ?? null"
    />
</template>
