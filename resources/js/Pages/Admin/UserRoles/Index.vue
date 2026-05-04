<script setup>
import { Head, router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import Button from "primevue/button";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import InputText from "primevue/inputtext";
import Select from "primevue/select";

import AdminTableToolbar from "@/Components/Admin/AdminTableToolbar.vue";
import RoleBadge from "@/Components/Admin/Roles/RoleBadge.vue";
import UserRoleAssignmentModal from "@/Components/Admin/UserRoles/UserRoleAssignmentModal.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { pageOptions as createPerPageOptions } from "@/Utils/functions";
import { trans } from "laravel-vue-i18n";
import { useAdminFilterState } from "@/composables/useAdminFilterState.js";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
    role_options: {
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

const loading = ref(false);
const modalVisible = ref(false);
const selectedUser = ref(null);
const selectedRoles = ref([]);

const form = useForm({
    roles: [],
});

const {
    filterState,
    sortOrder,
    load,
    submitFilters,
    clearFilters,
    onSort,
    onPage,
} = useAdminFilterState({
    filters: props.filters,
    defaults: {
    search: "",
    per_page: 15,
},
    routeName: "admin.user-roles.index",
    loading,
    toQuery: (state) => ({
            search: state.search || undefined,
            per_page: state.per_page,
        }),
});

const perPageOptions = createPerPageOptions(trans, [15, 30, 50]);
/*
const perPageOptions = [
    { label: '15 / oldal', value: 15 },
    { label: '30 / oldal', value: 30 },
    { label: '50 / oldal', value: 50 },
];
*/

const currentPage = computed(() => props.users.current_page ?? 1);
const first = computed(() => (currentPage.value - 1) * (props.users.per_page ?? 15));

const roleSystemMap = computed(() =>
    Object.fromEntries(
        props.role_options.map((option) => [option.name, option.is_system_role])
    )
);




const openAssignModal = (user) => {
    selectedUser.value = user;
    selectedRoles.value = [...user.roles];
    form.clearErrors();
    modalVisible.value = true;
};

const toggleRole = (roleName) => {
    if (selectedRoles.value.includes(roleName)) {
        selectedRoles.value = selectedRoles.value.filter((role) => role !== roleName);
        return;
    }

    selectedRoles.value = [...selectedRoles.value, roleName];
};

const saveRoles = () => {
    if (!selectedUser.value) return;

    form.roles = [...selectedRoles.value];
    form.put(route("admin.users.roles.update", selectedUser.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            modalVisible.value = false;
        },
    });
};
</script>

<template>
    <Head title="Felhasználói szerepkörök" />

    <div class="space-y-6">
        <SectionTitle
            eyebrow="Admin / Felhasználói szerepkörök"
            title="Felhasználói szerepkörök"
            description="Felhasználók szerepköreinek kezelése és effektív jogosultságaik áttekintése."
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <AdminTableToolbar
                :filters-grid-class="'grid gap-3 sm:grid-cols-2 lg:grid-cols-3'"
            >
                <template #filters>
                    <div class="space-y-1">
                        <label
                            class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80"
                            >Keresés</label
                        >
                        <InputText
                            v-model="filterState.search"
                            class="w-full"
                            placeholder="Név vagy email..."
                            @keyup.enter="submitFilters"
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
                    <Button icon="pi pi-search" label="Keresés" @click="submitFilters" />
                </template>
            </AdminTableToolbar>

            <div class="mt-4 overflow-x-auto">
                <DataTable
                    :value="users.data"
                    lazy
                    paginator
                    scrollable
                    :rows="users.per_page"
                    :first="first"
                    :total-records="users.total"
                    :loading="loading"
                    data-key="id"
                    paginator-template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                    @page="onPage"
                >
                    <template #empty>
                        <div
                            class="rounded-xl border border-dashed border-bakery-brown/25 bg-[#fcf7ef] p-6 text-center text-sm text-bakery-dark/70"
                        >
                            <p>Nincs megjeleníthető felhasználó.</p>
                            <div
                                class="mt-3 flex flex-wrap items-center justify-center gap-2"
                            >
                                <Button
                                    label="Szűrők törlése"
                                    outlined
                                    size="small"
                                    @click="clearFilters"
                                />
                            </div>
                        </div>
                    </template>

                    <Column field="name" header="Név" />
                    <Column field="email" header="Email" />

                    <Column header="Szerepkörök">
                        <template #body="{ data }">
                            <div class="flex flex-wrap gap-1.5">
                                <RoleBadge
                                    v-for="role in data.roles"
                                    :key="`${data.id}-${role}`"
                                    :role="role"
                                    :system="Boolean(roleSystemMap[role])"
                                />
                            </div>
                        </template>
                    </Column>

                    <Column v-if="can.view_permissions" header="Effektív jogosultságok">
                        <template #body="{ data }">
                            <span class="text-sm text-bakery-dark/75">{{
                                data.permissions.length
                            }}</span>
                        </template>
                    </Column>

                    <Column header="Műveletek" :style="{ width: '12rem' }">
                        <template #body="{ data }">
                            <Button
                                v-if="can.assign_roles"
                                label="Szerepkörök kezelése"
                                size="small"
                                outlined
                                class="min-h-11!"
                                @click="openAssignModal(data)"
                            />
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </div>

    <UserRoleAssignmentModal
        v-model:visible="modalVisible"
        :user="selectedUser"
        :role-options="role_options"
        :selected-roles="selectedRoles"
        :loading="form.processing"
        :can-view-permissions="can.view_permissions"
        @toggle-role="toggleRole"
        @save="saveRoles"
    />
</template>
