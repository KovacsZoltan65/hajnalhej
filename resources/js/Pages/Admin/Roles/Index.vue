<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import Button from 'primevue/button';
import Column from 'primevue/column';
import ConfirmDialog from 'primevue/confirmdialog';
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import { useConfirm } from 'primevue/useconfirm';

import AdminTableToolbar from '@/Components/Admin/AdminTableToolbar.vue';
import RoleBadge from '@/Components/Admin/Roles/RoleBadge.vue';
import RoleFormModal from '@/Components/Admin/Roles/RoleFormModal.vue';
import SectionTitle from '@/Components/SectionTitle.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    roles: {
        type: Object,
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

const confirm = useConfirm();
const loading = ref(false);
const createVisible = ref(false);
const editVisible = ref(false);
const editingRole = ref(null);

const createForm = useForm({ name: '' });
const editForm = useForm({ name: '' });

const filterState = reactive({
    search: props.filters.search ?? '',
    per_page: props.filters.per_page ?? 15,
});

const perPageOptions = [
    { label: '15 / oldal', value: 15 },
    { label: '30 / oldal', value: 30 },
    { label: '50 / oldal', value: 50 },
];

const currentPage = computed(() => props.roles.current_page ?? 1);
const first = computed(() => (currentPage.value - 1) * (props.roles.per_page ?? 15));

const load = (extra = {}) => {
    loading.value = true;

    router.get('/admin/roles', {
        search: filterState.search || undefined,
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

const openCreate = () => {
    createForm.reset();
    createForm.clearErrors();
    createVisible.value = true;
};

const submitCreate = () => {
    createForm.post('/admin/roles', {
        preserveScroll: true,
        onSuccess: () => {
            createVisible.value = false;
            createForm.reset();
        },
    });
};

const openEdit = (role) => {
    editingRole.value = role;
    editForm.clearErrors();
    editForm.name = role.name;
    editVisible.value = true;
};

const submitEdit = () => {
    if (!editingRole.value) return;

    editForm.put(`/admin/roles/${editingRole.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editVisible.value = false;
        },
    });
};

const destroyRole = (role) => {
    confirm.require({
        message: `Biztosan torlod a(z) ${role.name} szerepkort?`,
        header: 'Szerepkor torlese',
        rejectLabel: 'Megse',
        acceptLabel: 'Torles',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(`/admin/roles/${role.id}`, { preserveScroll: true });
        },
    });
};
</script>

<template>
    <Head title="Roles" />

    <div class="space-y-6">
        <SectionTitle
            eyebrow="Admin / Roles & Permissions"
            title="Szerepkorok"
            description="Role lista, atnevezes es jogosultsag-matrix kezelese biztonsagosan."
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <AdminTableToolbar :filters-grid-class="'grid gap-3 sm:grid-cols-2 lg:grid-cols-3'">
                <template #filters>
                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Kereses</label>
                        <InputText
                            v-model="filterState.search"
                            class="w-full"
                            placeholder="Szerepkor nev..."
                            @keyup.enter="submitFilters"
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
                    <Button icon="pi pi-search" label="Kereses" @click="submitFilters" />
                    <Button
                        v-if="can.create"
                        icon="pi pi-plus"
                        label="Uj szerepkor"
                        severity="contrast"
                        @click="openCreate"
                    />
                </template>
            </AdminTableToolbar>

            <DataTable
                class="mt-4"
                :value="roles.data"
                lazy
                paginator
                :rows="roles.per_page"
                :first="first"
                :total-records="roles.total"
                :loading="loading"
                data-key="id"
                paginator-template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                @page="onPage"
            >
                <template #empty>
                    <div class="py-8 text-center text-sm text-bakery-dark/70">Nincs megjelenitheto szerepkor.</div>
                </template>

                <Column field="name" header="Szerepkor">
                    <template #body="{ data }">
                        <RoleBadge :role="data.name" :system="data.is_system_role" />
                    </template>
                </Column>

                <Column field="guard_name" header="Guard" />
                <Column field="permissions_count" header="Jogosultsagok" />
                <Column field="users_count" header="Felhasznalok" />

                <Column header="Muveletek" :style="{ width: '17rem' }">
                    <template #body="{ data }">
                        <div class="flex flex-wrap gap-2">
                            <Link :href="`/admin/roles/${data.id}`">
                                <Button label="Reszletek" size="small" text />
                            </Link>
                            <Button
                                v-if="can.update"
                                label="Atnevezes"
                                size="small"
                                outlined
                                :disabled="data.is_system_role"
                                @click="openEdit(data)"
                            />
                            <Button
                                v-if="can.delete"
                                label="Torles"
                                size="small"
                                severity="danger"
                                text
                                :disabled="data.is_system_role"
                                @click="destroyRole(data)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>

    <ConfirmDialog />

    <RoleFormModal
        v-model:visible="createVisible"
        :form="createForm"
        title="Uj szerepkor letrehozasa"
        submit-label="Letrehozas"
        @submit="submitCreate"
    />

    <RoleFormModal
        v-model:visible="editVisible"
        :form="editForm"
        title="Szerepkor atnevezese"
        submit-label="Mentes"
        @submit="submitEdit"
    />
</template>
