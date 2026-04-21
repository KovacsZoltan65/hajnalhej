<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';

import AdminTableToolbar from '@/Components/Admin/AdminTableToolbar.vue';
import AuditEventBadge from '@/Components/Admin/AuditLogs/AuditEventBadge.vue';
import SectionTitle from '@/Components/SectionTitle.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

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
    search: props.filters.search ?? '',
    log_name: props.filters.log_name ?? '',
    event_key: props.filters.event_key ?? '',
    subject_type: props.filters.subject_type ?? '',
    per_page: props.filters.per_page ?? 20,
});

const currentPage = computed(() => props.logs.current_page ?? 1);
const first = computed(() => (currentPage.value - 1) * (props.logs.per_page ?? 20));

const perPageOptions = [
    { label: '20 / oldal', value: 20 },
    { label: '50 / oldal', value: 50 },
    { label: '100 / oldal', value: 100 },
];

const eventSelectOptions = computed(() => ([
    { label: 'Minden esemeny', value: '' },
    ...props.eventOptions.map((eventKey) => ({
        label: props.eventLabels[eventKey] ?? eventKey,
        value: eventKey,
    })),
]));

const logNameOptions = computed(() => ([
    { label: 'Minden domain', value: '' },
    ...Object.entries(props.logNameLabels).map(([value, label]) => ({ value, label })),
]));

const subjectTypeOptions = computed(() => ([
    { label: 'Minden subject', value: '' },
    { label: props.subjectTypeLabels.role ?? 'Role', value: 'role' },
    { label: props.subjectTypeLabels.user ?? 'User', value: 'user' },
    { label: props.subjectTypeLabels.order ?? 'Order', value: 'order' },
]));

const load = (extra = {}) => {
    loading.value = true;

    router.get('/admin/audit-logs', {
        search: filterState.search || undefined,
        log_name: filterState.log_name || undefined,
        event_key: filterState.event_key || undefined,
        subject_type: filterState.subject_type || undefined,
        per_page: filterState.per_page,
        ...extra,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onFinish: () => {
            loading.value = false;
        },
    });
};

const submitFilters = () => load({ page: 1 });

const onPage = (event) => {
    filterState.per_page = event.rows;
    load({ page: event.page + 1, per_page: event.rows });
};
</script>

<template>
    <Head title="Audit Logs" />

    <div class="space-y-6">
        <SectionTitle
            eyebrow="Admin / Audit Logs"
            title="Teljes audit naplo"
            description="Authorization, felhasznaloi activity es rendelesi domain kritikus esemenyei egy helyen."
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <AdminTableToolbar>
                <template #filters>
                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Kereses</label>
                        <InputText
                            v-model="filterState.search"
                            class="w-full"
                            placeholder="Actor nev vagy email..."
                            @keyup.enter="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Domain</label>
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
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Esemeny</label>
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
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Subject tipusa</label>
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
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Talalat / oldal</label>
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
                    <Button icon="pi pi-search" label="Szures" @click="submitFilters" />
                </template>
            </AdminTableToolbar>

            <DataTable
                class="mt-4"
                :value="logs.data"
                lazy
                paginator
                :rows="logs.per_page"
                :first="first"
                :total-records="logs.total"
                :loading="loading"
                data-key="id"
                paginator-template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                @page="onPage"
            >
                <template #empty>
                    <div class="py-8 text-center text-sm text-bakery-dark/70">Nincs audit bejegyzes a szurok szerint.</div>
                </template>

                <Column header="Idopont" field="created_at" />

                <Column header="Domain">
                    <template #body="{ data }">
                        <span class="text-xs font-semibold uppercase tracking-[0.1em] text-bakery-dark/70">{{ data.log_name }}</span>
                    </template>
                </Column>

                <Column header="Esemeny">
                    <template #body="{ data }">
                        <AuditEventBadge :event-key="data.event_key" :label="eventLabels[data.event_key] ?? data.event_key" />
                    </template>
                </Column>

                <Column header="Actor">
                    <template #body="{ data }">
                        <div class="text-sm">
                            <p class="font-medium text-bakery-dark">{{ data.causer?.name ?? '-' }}</p>
                            <p class="text-bakery-dark/70">{{ data.causer?.email ?? '-' }}</p>
                        </div>
                    </template>
                </Column>

                <Column header="Subject">
                    <template #body="{ data }">
                        <div class="text-sm">
                            <p class="font-medium text-bakery-dark">{{ data.subject?.label ?? '-' }}</p>
                            <p class="text-bakery-dark/70">{{ data.subject?.type ?? '-' }}</p>
                        </div>
                    </template>
                </Column>

                <Column field="description" header="Leiras" />

                <Column header="Muveletek" :style="{ width: '9rem' }">
                    <template #body="{ data }">
                        <Link :href="`/admin/audit-logs/${data.id}`">
                            <Button label="Reszletek" size="small" text />
                        </Link>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>
