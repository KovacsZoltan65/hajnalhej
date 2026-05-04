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

    router.get(route('admin.roles.index'), {
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
const clearFilters = () => {
    filterState.search = '';
    filterState.per_page = 15;
    submitFilters();
};

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
    createForm.post(route('admin.roles.store'), {
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

    editForm.put(route('admin.roles.update', editingRole.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            editVisible.value = false;
        },
    });
};

const destroyRole = (role) => {
    confirm.require({
        message: `Biztosan törlöd a(z) ${role.name} szerepkört?`,
        header: 'Szerepkör törlése',
        rejectLabel: 'Mégse',
        acceptLabel: 'Törlés',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('admin.roles.destroy', role.id), { preserveScroll: true });
        },
    });
};
</script>

<template>
    <Head title="Szerepkörök" />

    <div class="space-y-6">
        <SectionTitle
            eyebrow="Admin / Szerepkörök és jogosultságok"
            title="Szerepkörök"
            description="Szerepkörlista, átnevezés és jogosultságmátrix kezelése biztonságosan."
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <AdminTableToolbar :filters-grid-class="'grid gap-3 sm:grid-cols-2 lg:grid-cols-3'">
                <template #filters>
                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Keresés</label>
                        <InputText
                            v-model="filterState.search"
                            class="w-full"
                            placeholder="Szerepkör neve..."
                            @keyup.enter="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Találat / oldal</label>
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
                    <Button icon="pi pi-search" label="Keresés" @click="submitFilters" />
                    <Button
                        v-if="can.create"
                        icon="pi pi-plus"
                        label="Új szerepkör"
                        severity="contrast"
                        @click="openCreate"
                    />
                </template>
            </AdminTableToolbar>

            <div class="mt-4 overflow-x-auto">
                <DataTable
                    :value="roles.data"
                    lazy
                    paginator
                    scrollable
                    :rows="roles.per_page"
                    :first="first"
                    :total-records="roles.total"
                    :loading="loading"
                    data-key="id"
                    paginator-template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                    @page="onPage"
                >
                    <template #empty>
                        <div class="rounded-xl border border-dashed border-bakery-brown/25 bg-[#fcf7ef] p-6 text-center text-sm text-bakery-dark/70">
                            <p>Nincs megjeleníthető szerepkör.</p>
                            <div class="mt-3 flex flex-wrap items-center justify-center gap-2">
                                <Button label="Szűrők törlése" outlined size="small" @click="clearFilters" />
                                <Button v-if="can.create" label="Új szerepkör" size="small" @click="openCreate" />
                            </div>
                        </div>
                    </template>

                <Column field="name" header="Szerepkör">
                    <template #body="{ data }">
                        <RoleBadge :role="data.name" :system="data.is_system_role" />
                    </template>
                </Column>

                <Column field="guard_name" header="Guard" />
                <Column field="permissions_count" header="Jogosultságok" />
                <Column field="users_count" header="Felhasználók" />

                <Column header="Műveletek" :style="{ width: '17rem' }">
                    <template #body="{ data }">
                        <div class="flex flex-wrap gap-2">
                            <Link :href="route('admin.roles.show', data.id)">
                                <Button label="Részletek" size="small" text class="!min-h-11" />
                            </Link>
                            <Button
                                v-if="can.update"
                                label="Átnevezés"
                                size="small"
                                outlined
                                class="!min-h-11"
                                :disabled="data.is_system_role"
                                @click="openEdit(data)"
                            />
                            <Button
                                v-if="can.delete"
                                label="Törlés"
                                size="small"
                                severity="danger"
                                text
                                class="!min-h-11"
                                :disabled="data.is_system_role"
                                @click="destroyRole(data)"
                            />
                        </div>
                    </template>
                </Column>
                </DataTable>
            </div>
        </div>
    </div>

    <ConfirmDialog />

    <RoleFormModal
        v-model:visible="createVisible"
        :form="createForm"
        title="Új szerepkör létrehozása"
        submit-label="Létrehozás"
        @submit="submitCreate"
    />

    <RoleFormModal
        v-model:visible="editVisible"
        :form="editForm"
        title="Szerepkör átnevezése"
        submit-label="Mentés"
        @submit="submitEdit"
    />
</template>


