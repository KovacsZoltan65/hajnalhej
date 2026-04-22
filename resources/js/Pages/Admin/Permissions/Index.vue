<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';

import AdminTableToolbar from '@/Components/Admin/AdminTableToolbar.vue';
import PermissionBadge from '@/Components/Admin/Permissions/PermissionBadge.vue';
import PermissionDangerBadge from '@/Components/Admin/Permissions/PermissionDangerBadge.vue';
import PermissionRegistryStateBadge from '@/Components/Admin/Permissions/PermissionRegistryStateBadge.vue';
import PermissionSyncSummaryModal from '@/Components/Admin/Permissions/PermissionSyncSummaryModal.vue';
import SectionTitle from '@/Components/SectionTitle.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

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
    search: props.filters.search ?? '',
    module: props.filters.module ?? '',
    dangerous_only: props.filters.dangerous_only ?? false,
    usage_state: props.filters.usage_state ?? '',
    registry_state: props.filters.registry_state ?? '',
    sort_field: props.filters.sort_field ?? 'name',
    sort_direction: props.filters.sort_direction ?? 'asc',
    per_page: props.filters.per_page ?? 20,
});

const currentPage = computed(() => props.permissions.current_page ?? 1);
const first = computed(() => (currentPage.value - 1) * (props.permissions.per_page ?? 20));
const moduleLabels = {
    Admin: 'Admin',
    Orders: 'Rendelések',
    Products: 'Termékek',
    Categories: 'Kategóriák',
    Ingredients: 'Alapanyagok',
    'Weekly Menu': 'Heti menü',
    'Production Plans': 'Gyártási tervek',
    Account: 'Fiók',
    'Roles & Permissions': 'Szerepkörök és jogosultságok',
    Security: 'Biztonság',
};
const moduleLabel = (moduleName) => moduleLabels[moduleName] ?? moduleName;

const moduleOptions = computed(() => ([
    { label: 'Minden modul', value: '' },
    ...props.modules.map((moduleName) => ({ label: moduleLabel(moduleName), value: moduleName })),
]));

const usageOptions = [
    { label: 'Mind', value: '' },
    { label: 'Hasznalt', value: 'used' },
    { label: 'Nem hasznalt', value: 'unused' },
];

const registryStateOptions = [
    { label: 'Minden allapot', value: '' },
    { label: 'Szinkronban', value: 'synced' },
    { label: 'Hiányzik az adatbázisból', value: 'missing_in_db' },
    { label: 'Csak adatbázisban', value: 'orphan_db_only' },
];

const sortFieldOptions = [
    { label: 'Név', value: 'name' },
    { label: 'Modul', value: 'module' },
    { label: 'Szerepkör használat', value: 'roles_count' },
    { label: 'Felhasználói használat', value: 'users_count' },
    { label: 'Registry állapot', value: 'registry_state' },
];

const sortDirectionOptions = [
    { label: 'Novekvo', value: 'asc' },
    { label: 'Csokkeno', value: 'desc' },
];

const perPageOptions = [
    { label: '20 / oldal', value: 20 },
    { label: '50 / oldal', value: 50 },
    { label: '100 / oldal', value: 100 },
];

const load = (extra = {}) => {
    loading.value = true;

    router.get('/admin/permissions', {
        search: filterState.search || undefined,
        module: filterState.module || undefined,
        dangerous_only: filterState.dangerous_only ? 1 : undefined,
        usage_state: filterState.usage_state || undefined,
        registry_state: filterState.registry_state || undefined,
        sort_field: filterState.sort_field,
        sort_direction: filterState.sort_direction,
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

const runSync = () => {
    syncing.value = true;
    router.post('/admin/permissions/sync', {}, {
        preserveScroll: true,
        onFinish: () => {
            syncing.value = false;
            syncSummaryVisible.value = Boolean(page.props.flash?.sync_summary);
        },
    });
};
</script>

<template>
    <Head title="Jogosultságok" />

    <div class="space-y-6">
        <SectionTitle
            eyebrow="Admin / Szerepkörök és jogosultságok"
            title="Jogosultságok"
            description="Registry-first permission lista, usage nezet es drift ellenorzes."
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <AdminTableToolbar :filters-grid-class="'grid gap-3 md:grid-cols-2 xl:grid-cols-4'">
                <template #filters>
                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Keresés</label>
                        <InputText v-model="filterState.search" class="w-full" placeholder="Név, label, leiras..." @keyup.enter="submitFilters" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Modul</label>
                        <Select v-model="filterState.module" :options="moduleOptions" option-label="label" option-value="value" class="w-full" @change="submitFilters" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Használat</label>
                        <Select v-model="filterState.usage_state" :options="usageOptions" option-label="label" option-value="value" class="w-full" @change="submitFilters" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Registry állapot</label>
                        <Select v-model="filterState.registry_state" :options="registryStateOptions" option-label="label" option-value="value" class="w-full" @change="submitFilters" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Rendezes mezo</label>
                        <Select v-model="filterState.sort_field" :options="sortFieldOptions" option-label="label" option-value="value" class="w-full" @change="submitFilters" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Irany</label>
                        <Select v-model="filterState.sort_direction" :options="sortDirectionOptions" option-label="label" option-value="value" class="w-full" @change="submitFilters" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Találat / oldal</label>
                        <Select v-model="filterState.per_page" :options="perPageOptions" option-label="label" option-value="value" class="w-full" @change="submitFilters" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Csak veszélyes</label>
                        <div class="flex h-10 items-center gap-2 rounded-lg border border-bakery-brown/15 px-3">
                            <Checkbox v-model="filterState.dangerous_only" binary input-id="dangerous-only" @change="submitFilters" />
                            <label for="dangerous-only" class="text-sm text-bakery-dark">Csak veszelyes</label>
                        </div>
                    </div>
                </template>

                <template #actions>
                    <Button icon="pi pi-search" label="Szures" @click="submitFilters" />
                    <Button
                        v-if="can.sync"
                        icon="pi pi-sync"
                        label="Registry szinkron"
                        severity="contrast"
                        :loading="syncing"
                        :disabled="syncing"
                        @click="runSync"
                    />
                </template>
            </AdminTableToolbar>

            <DataTable
                class="mt-4"
                :value="permissions.data"
                lazy
                paginator
                :rows="permissions.per_page"
                :first="first"
                :total-records="permissions.total"
                :loading="loading"
                data-key="name"
                paginator-template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                @page="onPage"
            >
                <template #empty>
                    <div class="py-8 text-center text-sm text-bakery-dark/70">Nincs megjeleníthető jogosultság.</div>
                </template>

                <Column field="name" header="Jogosultság">
                    <template #body="{ data }">
                        <PermissionBadge :name="data.name" />
                    </template>
                </Column>

                <Column field="module" header="Modul">
                    <template #body="{ data }">
                        {{ moduleLabel(data.module) }}
                    </template>
                </Column>

                <Column field="dangerous" header="Kockázat">
                    <template #body="{ data }">
                        <PermissionDangerBadge :dangerous="data.dangerous" />
                    </template>
                </Column>

                <Column field="registry_state" header="Registry állapot">
                    <template #body="{ data }">
                        <PermissionRegistryStateBadge :state="data.registry_state" />
                    </template>
                </Column>

                <Column field="roles_count" header="Szerepkörök" />
                <Column field="users_count" header="Felhasználók" />
                <Column field="guard_name" header="Guard" />

                <Column header="Muveletek" :style="{ width: '9rem' }">
                    <template #body="{ data }">
                        <Link :href="`/admin/permissions/${encodeURIComponent(data.name)}`">
                            <Button label="Reszletek" size="small" text />
                        </Link>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>

    <PermissionSyncSummaryModal
        v-model:visible="syncSummaryVisible"
        :summary="page.props.flash?.sync_summary ?? null"
    />
</template>

