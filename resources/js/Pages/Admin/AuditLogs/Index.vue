<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { computed, reactive, ref } from "vue";
import Button from "primevue/button";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import InputText from "primevue/inputtext";
import Select from "primevue/select";

import AdminTableToolbar from "@/Components/Admin/AdminTableToolbar.vue";
import AuditEventBadge from "@/Components/Admin/AuditLogs/AuditEventBadge.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { trans } from "laravel-vue-i18n";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    logs: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    eventOptions: {
        type: Array,
        required: true,
    },
    eventLabels: {
        type: Object,
        required: true,
    },
    subjectTypeLabels: {
        type: Object,
        required: true,
    },
    logNameLabels: {
        type: Object,
        required: true,
    },
});

const loading = ref(false);

const filterState = reactive({
    search: props.filters.search ?? "",
    log_name: props.filters.log_name ?? "",
    event_key: props.filters.event_key ?? "",
    subject_type: props.filters.subject_type ?? "",
    per_page: props.filters.per_page ?? 20,
});

const currentPage = computed(() => props.logs.current_page ?? 1);
const first = computed(() => (currentPage.value - 1) * (props.logs.per_page ?? 20));

const perPageOptions = [
    {
        label: trans("audit_logs.filters.per_page_option", { count: 20 }),
        value: 20,
    },
    { label: trans("audit_logs.filters.per_page_option", { count: 50 }), value: 50 },
    { label: trans("audit_logs.filters.per_page_option", { count: 100 }), value: 100 },
];

const eventSelectOptions = computed(() => [
    { label: trans("audit_logs.filters.all_events"), value: "" },
    ...props.eventOptions.map((eventKey) => ({
        label: props.eventLabels[eventKey] ?? eventKey,
        value: eventKey,
    })),
]);

const logNameOptions = computed(() => [
    { label: trans("audit_logs.filters.all_domains"), value: "" },
    ...Object.entries(props.logNameLabels).map(([value, label]) => ({ value, label })),
]);

const subjectTypeOptions = computed(() => [
    { label: trans("audit_logs.filters.all_subjects"), value: "" },
    {
        label: props.subjectTypeLabels.role ?? trans("audit_logs.subject_types.role"),
        value: "role",
    },
    {
        label: props.subjectTypeLabels.user ?? trans("audit_logs.subject_types.user"),
        value: "user",
    },
    {
        label: props.subjectTypeLabels.order ?? trans("audit_logs.subject_types.order"),
        value: "order",
    },
]);

const load = (extra = {}) => {
    loading.value = true;

    router.get(
        route("admin.audit-logs.index"),
        {
            search: filterState.search || undefined,
            log_name: filterState.log_name || undefined,
            event_key: filterState.event_key || undefined,
            subject_type: filterState.subject_type || undefined,
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
    filterState.log_name = "";
    filterState.event_key = "";
    filterState.subject_type = "";
    filterState.per_page = 20;
    submitFilters();
};

const onPage = (event) => {
    filterState.per_page = event.rows;
    load({ page: event.page + 1, per_page: event.rows });
};
</script>

<template>
    <Head :title="$t('audit_logs.meta_title')" />

    <div class="space-y-6">
        <SectionTitle
            :eyebrow="$t('audit_logs.eyebrow')"
            :title="$t('audit_logs.title')"
            :description="$t('audit_logs.description')"
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <AdminTableToolbar>
                <template #filters>
                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("audit_logs.filters.search") }}</label
                        >
                        <InputText
                            v-model="filterState.search"
                            class="w-full"
                            :placeholder="$t('audit_logs.filters.search_placeholder')"
                            @keyup.enter="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("audit_logs.filters.domain") }}</label
                        >
                        <Select
                            v-model="filterState.log_name"
                            :options="logNameOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("audit_logs.filters.event") }}</label
                        >
                        <Select
                            v-model="filterState.event_key"
                            :options="eventSelectOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("audit_logs.filters.subject_type") }}</label
                        >
                        <Select
                            v-model="filterState.subject_type"
                            :options="subjectTypeOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >{{ $t("audit_logs.filters.per_page") }}</label
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
                        :label="$t('audit_logs.actions.filter')"
                        @click="submitFilters"
                    />
                </template>
            </AdminTableToolbar>

            <div class="mt-4 overflow-x-auto">
                <DataTable
                    :value="logs.data"
                    lazy
                    paginator
                    scrollable
                    :rows="logs.per_page"
                    :first="first"
                    :total-records="logs.total"
                    :loading="loading"
                    data-key="id"
                    paginator-template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                    @page="onPage"
                >
                    <template #empty>
                        <div
                            class="rounded-xl border border-dashed border-bakery-brown/25 bg-[#fcf7ef] p-6 text-center text-sm text-bakery-dark/70"
                        >
                            <p>{{ $t("audit_logs.empty") }}</p>
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
                        :header="$t('audit_logs.columns.created_at')"
                        field="created_at"
                    />

                    <Column :header="$t('audit_logs.columns.domain')">
                        <template #body="{ data }">
                            <span
                                class="text-xs font-semibold uppercase tracking-widest text-bakery-dark/70"
                                >{{ data.log_name }}</span
                            >
                        </template>
                    </Column>

                    <Column :header="$t('audit_logs.columns.event')">
                        <template #body="{ data }">
                            <AuditEventBadge
                                :event-key="data.event_key"
                                :label="eventLabels[data.event_key] ?? data.event_key"
                            />
                        </template>
                    </Column>

                    <Column :header="$t('audit_logs.columns.causer')">
                        <template #body="{ data }">
                            <div class="text-sm">
                                <p class="font-medium text-bakery-dark">
                                    {{ data.causer?.name ?? "-" }}
                                </p>
                                <p class="text-bakery-dark/70">
                                    {{ data.causer?.email ?? "-" }}
                                </p>
                            </div>
                        </template>
                    </Column>

                    <Column :header="$t('audit_logs.columns.subject')">
                        <template #body="{ data }">
                            <div class="text-sm">
                                <p class="font-medium text-bakery-dark">
                                    {{ data.subject?.label ?? "-" }}
                                </p>
                                <p class="text-bakery-dark/70">
                                    {{ data.subject?.type ?? "-" }}
                                </p>
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="description"
                        :header="$t('audit_logs.columns.description')"
                    />

                    <Column :header="$t('common.actions')" :style="{ width: '9rem' }">
                        <template #body="{ data }">
                            <Link :href="route('admin.audit-logs.show', data.id)">
                                <Button
                                    :label="$t('audit_logs.actions.details')"
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
</template>
