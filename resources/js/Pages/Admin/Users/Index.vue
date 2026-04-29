<script setup>
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { computed, reactive, ref } from "vue";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import Column from "primevue/column";
import ConfirmDialog from "primevue/confirmdialog";
import DataTable from "primevue/datatable";
import Dialog from "primevue/dialog";
import InputNumber from "primevue/inputnumber";
import InputText from "primevue/inputtext";
import MultiSelect from "primevue/multiselect";
import Select from "primevue/select";
import Tag from "primevue/tag";
import Textarea from "primevue/textarea";
import ToggleSwitch from "primevue/toggleswitch";
import { useConfirm } from "primevue/useconfirm";

import AdminTableToolbar from "@/Components/Admin/AdminTableToolbar.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";

import { trans } from "laravel-vue-i18n";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    users: { type: Object, required: true },
    roles: { type: Array, required: true },
    permissions: { type: Array, required: true },
    filters: { type: Object, required: true },
    status_options: { type: Array, required: true },
    discount_types: { type: Array, required: true },
    can: { type: Object, required: true },
});

const title = computed(() => props.item?.title ?? trans("nav.users"));
/*
const title = computed(() => {
    get: () => {}
    set: () => {}
});
*/
const confirm = useConfirm();
const loading = ref(false);
const createVisible = ref(false);
const editVisible = ref(false);
const selectedUser = ref(null);
const selectedDiscountId = ref(null);

const filterState = reactive({
    search: props.filters.search ?? "",
    status: props.filters.status ?? "",
    sort_field: props.filters.sort_field ?? "created_at",
    sort_direction: props.filters.sort_direction ?? "desc",
    per_page: props.filters.per_page ?? 15,
});

const perPageOptions = [
    { label: "15 / oldal", value: 15 },
    { label: "30 / oldal", value: 30 },
    { label: "50 / oldal", value: 50 },
];

const statusOptions = computed(() => [
    { label: "Mind", value: "" },
    ...props.status_options.map((status) => ({
        label: status === "active" ? "Aktív" : "Inaktív",
        value: status,
    })),
]);

const roleOptions = computed(() =>
    props.roles.map((role) => ({ label: role.name, value: role.name }))
);
const permissionOptions = computed(() =>
    props.permissions.map((permission) => ({
        label: permission.name,
        value: permission.name,
    }))
);
const discountTypeOptions = computed(() =>
    props.discount_types.map((type) => ({
        label: type === "percent" ? "Százalék" : "Fix összeg",
        value: type,
    }))
);
const sortOrder = computed(() => (filterState.sort_direction === "asc" ? 1 : -1));
const currentPage = computed(() => props.users.current_page ?? 1);
const first = computed(() => (currentPage.value - 1) * (props.users.per_page ?? 15));

const userForm = useForm({
    name: "",
    email: "",
    phone: "",
    status: "active",
    password: "",
    roles: [],
});

const temporaryPermissionForm = useForm({
    permission_name: "",
    starts_at: "",
    expires_at: "",
    reason: "",
});

const discountForm = useForm({
    type: "percent",
    value: 0,
    starts_at: "",
    expires_at: "",
    active: true,
    reason: "",
});

const load = (extra = {}) => {
    loading.value = true;

    router.get(
        "/admin/users",
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
    filterState.sort_field = "created_at";
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

const resetUserForm = () => {
    userForm.reset();
    userForm.clearErrors();
    userForm.name = "";
    userForm.email = "";
    userForm.phone = "";
    userForm.status = "active";
    userForm.password = "";
    userForm.roles = [];
};

const openCreate = () => {
    selectedUser.value = null;
    resetUserForm();
    createVisible.value = true;
};

const openEdit = (user) => {
    selectedUser.value = user;
    resetUserForm();
    userForm.name = user.name;
    userForm.email = user.email;
    userForm.phone = user.phone ?? "";
    userForm.status = user.status;
    userForm.roles = [...user.roles];
    editVisible.value = true;
};

const submitCreate = () => {
    userForm.post("/admin/users", {
        preserveScroll: true,
        onSuccess: () => {
            createVisible.value = false;
            resetUserForm();
        },
    });
};

const submitEdit = () => {
    if (!selectedUser.value) return;

    userForm.put(`/admin/users/${selectedUser.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editVisible.value = false;
            selectedUser.value = null;
        },
    });
};

const confirmDeactivate = (user) => {
    confirm.require({
        header: "Felhasználó inaktiválása",
        message: `Biztosan inaktiválod ezt a felhasználót: ${user.name}?`,
        rejectLabel: "Mégse",
        acceptLabel: "Inaktiválás",
        acceptClass: "p-button-danger",
        accept: () => router.delete(`/admin/users/${user.id}`, { preserveScroll: true }),
    });
};

const resetTemporaryPermissionForm = () => {
    temporaryPermissionForm.reset();
    temporaryPermissionForm.clearErrors();
    temporaryPermissionForm.permission_name = "";
    temporaryPermissionForm.starts_at = "";
    temporaryPermissionForm.expires_at = "";
    temporaryPermissionForm.reason = "";
};

const createTemporaryPermission = () => {
    if (!selectedUser.value) return;

    temporaryPermissionForm.post(
        `/admin/users/${selectedUser.value.id}/temporary-permissions`,
        {
            preserveScroll: true,
            onSuccess: () => resetTemporaryPermissionForm(),
        }
    );
};

const revokeTemporaryPermission = (permission) => {
    if (!selectedUser.value) return;

    router.delete(
        `/admin/users/${selectedUser.value.id}/temporary-permissions/${permission.id}`,
        {
            preserveScroll: true,
        }
    );
};

const resetDiscountForm = () => {
    selectedDiscountId.value = null;
    discountForm.reset();
    discountForm.clearErrors();
    discountForm.type = "percent";
    discountForm.value = 0;
    discountForm.starts_at = "";
    discountForm.expires_at = "";
    discountForm.active = true;
    discountForm.reason = "";
};

const editDiscount = (discount) => {
    selectedDiscountId.value = discount.id;
    discountForm.clearErrors();
    discountForm.type = discount.type;
    discountForm.value = discount.value;
    discountForm.starts_at = discount.starts_at ?? "";
    discountForm.expires_at = discount.expires_at ?? "";
    discountForm.active = discount.active;
    discountForm.reason = discount.reason ?? "";
};

const submitDiscount = () => {
    if (!selectedUser.value) return;

    const options = {
        preserveScroll: true,
        onSuccess: () => resetDiscountForm(),
    };

    if (selectedDiscountId.value) {
        discountForm.put(
            `/admin/users/${selectedUser.value.id}/discounts/${selectedDiscountId.value}`,
            options
        );
        return;
    }

    discountForm.post(`/admin/users/${selectedUser.value.id}/discounts`, options);
};

const deactivateDiscount = (discount) => {
    if (!selectedUser.value) return;

    router.delete(`/admin/users/${selectedUser.value.id}/discounts/${discount.id}`, {
        preserveScroll: true,
    });
};

const statusSeverity = (status) => (status === "active" ? "success" : "secondary");
const statusLabel = (status) => (status === "active" ? "Aktív" : "Inaktív");
const discountLabel = (discount) =>
    discount.type === "percent" ? `${discount.value}%` : `${discount.value} Ft`;
</script>

<template>
    <Head :title="title" />

    <div class="space-y-6">
        <SectionTitle
            eyebrow="Admin / Felhasználók"
            title="Felhasználók"
            description="Felhasználói adatok, szerepkörök, rendelési előzmények és kedvezmények kezelése."
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <AdminTableToolbar
                :filters-grid-class="'grid gap-3 sm:grid-cols-2 xl:grid-cols-3'"
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
                            placeholder="Név, email vagy telefon"
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
                    <Button
                        v-if="can.create"
                        icon="pi pi-plus"
                        label="Új felhasználó"
                        @click="openCreate"
                    />
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
                                <Button
                                    v-if="can.create"
                                    label="Új felhasználó"
                                    size="small"
                                    @click="openCreate"
                                />
                            </div>
                        </div>
                    </template>

                    <Column field="name" header="Név" sortable>
                        <template #body="{ data }">
                            <div>
                                <p class="font-semibold text-bakery-dark">
                                    {{ data.name }}
                                </p>
                                <p class="text-xs text-bakery-dark/60">
                                    {{ data.email }}
                                </p>
                            </div>
                        </template>
                    </Column>
                    <Column field="phone" header="Telefon" />
                    <Column field="status" header="Státusz" sortable>
                        <template #body="{ data }">
                            <Tag
                                :value="statusLabel(data.status)"
                                :severity="statusSeverity(data.status)"
                            />
                        </template>
                    </Column>
                    <Column header="Szerepkörök">
                        <template #body="{ data }">
                            <div class="flex flex-wrap gap-1.5">
                                <Tag
                                    v-for="role in data.roles"
                                    :key="`${data.id}-${role}`"
                                    :value="role"
                                    severity="info"
                                />
                            </div>
                        </template>
                    </Column>
                    <Column header="Rendelések">
                        <template #body="{ data }">
                            <span class="text-sm text-bakery-dark/75">{{
                                data.orders_count
                            }}</span>
                        </template>
                    </Column>
                    <Column header="Műveletek" :exportable="false">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Button
                                    icon="pi pi-pencil"
                                    text
                                    rounded
                                    class="!h-11 !w-11"
                                    aria-label="Felhasználó szerkesztése"
                                    @click="openEdit(data)"
                                />
                                <Button
                                    v-if="can.delete && data.status === 'active'"
                                    icon="pi pi-ban"
                                    text
                                    rounded
                                    severity="danger"
                                    class="!h-11 !w-11"
                                    aria-label="Felhasználó inaktiválása"
                                    @click="confirmDeactivate(data)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <!-- CREATE MODAL -->
        <Dialog
            v-model:visible="createVisible"
            modal
            header="Új felhasználó"
            :style="{ width: '44rem', maxWidth: '95vw' }"
        >
            <form id="user-create-form" class="space-y-4" @submit.prevent="submitCreate">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-bakery-dark">Név</label
                        ><InputText v-model="userForm.name" class="w-full" />
                        <p v-if="userForm.errors.name" class="text-xs text-red-700">
                            {{ userForm.errors.name }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-bakery-dark">Email</label
                        ><InputText v-model="userForm.email" class="w-full" />
                        <p v-if="userForm.errors.email" class="text-xs text-red-700">
                            {{ userForm.errors.email }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-bakery-dark">Telefon</label
                        ><InputText v-model="userForm.phone" class="w-full" />
                        <p v-if="userForm.errors.phone" class="text-xs text-red-700">
                            {{ userForm.errors.phone }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-bakery-dark">Státusz</label
                        ><Select
                            v-model="userForm.status"
                            :options="statusOptions.filter((item) => item.value)"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                        />
                    </div>
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-sm font-medium text-bakery-dark">Jelszó</label
                        ><InputText
                            v-model="userForm.password"
                            type="password"
                            class="w-full"
                        />
                        <p v-if="userForm.errors.password" class="text-xs text-red-700">
                            {{ userForm.errors.password }}
                        </p>
                    </div>
                    <div v-if="can.manage_roles" class="space-y-2 md:col-span-2">
                        <label class="text-sm font-medium text-bakery-dark"
                            >Szerepkörök</label
                        ><MultiSelect
                            v-model="userForm.roles"
                            :options="roleOptions"
                            option-label="label"
                            option-value="value"
                            display="chip"
                            class="w-full"
                        />
                    </div>
                </div>
            </form>
            <template #footer>
                <Button
                    label="Mégse"
                    severity="secondary"
                    @click="createVisible = false"
                />
                <Button
                    type="submit"
                    form="user-create-form"
                    label="Létrehozás"
                    :loading="userForm.processing"
                />
            </template>
        </Dialog>

        <!-- EDIT MODAL -->
        <Dialog
            v-model:visible="editVisible"
            modal
            header="Felhasználó szerkesztése"
            :style="{ width: '70rem', maxWidth: '96vw' }"
            :content-style="{ maxHeight: '76vh', overflowY: 'auto' }"
        >
            <form id="user-edit-form" class="space-y-4" @submit.prevent="submitEdit">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-bakery-dark">Név</label
                        ><InputText v-model="userForm.name" class="w-full" />
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-bakery-dark">Email</label
                        ><InputText v-model="userForm.email" class="w-full" />
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-bakery-dark">Telefon</label
                        ><InputText v-model="userForm.phone" class="w-full" />
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-bakery-dark">Státusz</label
                        ><Select
                            v-model="userForm.status"
                            :options="statusOptions.filter((item) => item.value)"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                        />
                    </div>
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-sm font-medium text-bakery-dark"
                            >Új jelszó</label
                        ><InputText
                            v-model="userForm.password"
                            type="password"
                            class="w-full"
                            placeholder="Csak akkor módosul, ha kitöltöd"
                        />
                    </div>
                    <div v-if="can.manage_roles" class="space-y-2 md:col-span-2">
                        <label class="text-sm font-medium text-bakery-dark"
                            >Szerepkörök</label
                        ><MultiSelect
                            v-model="userForm.roles"
                            :options="roleOptions"
                            option-label="label"
                            option-value="value"
                            display="chip"
                            class="w-full"
                        />
                    </div>
                </div>
            </form>

            <div v-if="selectedUser" class="mt-6 grid gap-4 xl:grid-cols-3">
                <section
                    class="rounded-xl border border-bakery-brown/15 p-4 xl:col-span-3"
                >
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="font-semibold text-bakery-dark">Rendelések</h3>
                        <span class="text-xs text-bakery-dark/60"
                            >{{ selectedUser.orders_count }} rendelés</span
                        >
                    </div>
                    <DataTable :value="selectedUser.orders" size="small">
                        <template #empty
                            ><p class="py-3 text-sm text-bakery-dark/60">
                                Nincs rendelés ehhez a felhasználóhoz.
                            </p></template
                        >
                        <Column field="order_number" header="Rendelés" />
                        <Column field="status" header="Státusz" />
                        <Column field="total" header="Végösszeg" />
                        <Column header="Átvétel"
                            ><template #body="{ data }"
                                >{{ data.pickup_date }}
                                {{ data.pickup_time_slot }}</template
                            ></Column
                        >
                        <Column field="created_at" header="Létrehozva" />
                        <Column header=""
                            ><template #body="{ data }"
                                ><Link
                                    v-if="can.view_orders"
                                    :href="data.show_url"
                                    class="text-sm font-semibold text-bakery-brown"
                                    >Részletek</Link
                                ></template
                            ></Column
                        >
                    </DataTable>
                </section>

                <section
                    class="rounded-xl border border-bakery-brown/15 p-4 xl:col-span-1"
                >
                    <h3 class="mb-3 font-semibold text-bakery-dark">
                        Időleges jogosultságok
                    </h3>
                    <form
                        v-if="can.manage_temporary_permissions"
                        class="space-y-3"
                        @submit.prevent="createTemporaryPermission"
                    >
                        <Select
                            v-model="temporaryPermissionForm.permission_name"
                            :options="permissionOptions"
                            option-label="label"
                            option-value="value"
                            filter
                            class="w-full"
                            placeholder="Jogosultság"
                        />
                        <InputText
                            v-model="temporaryPermissionForm.starts_at"
                            type="datetime-local"
                            class="w-full"
                        />
                        <InputText
                            v-model="temporaryPermissionForm.expires_at"
                            type="datetime-local"
                            class="w-full"
                        />
                        <Textarea
                            v-model="temporaryPermissionForm.reason"
                            rows="2"
                            class="w-full"
                            placeholder="Indok"
                        />
                        <Button
                            label="Jogosultság hozzáadása"
                            size="small"
                            :loading="temporaryPermissionForm.processing"
                        />
                    </form>
                    <div class="mt-4 space-y-2">
                        <div
                            v-for="permission in selectedUser.temporary_permissions"
                            :key="permission.id"
                            class="rounded-lg bg-[#fcf7ef] p-3 text-sm"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-medium">{{
                                    permission.permission_name
                                }}</span
                                ><Tag
                                    :value="
                                        permission.is_active ? 'Érvényes' : 'Nem aktív'
                                    "
                                    :severity="
                                        permission.is_active ? 'success' : 'secondary'
                                    "
                                />
                            </div>
                            <p class="mt-1 text-xs text-bakery-dark/60">
                                {{ permission.expires_at ?? "Nincs lejárat" }}
                            </p>
                            <Button
                                v-if="
                                    can.manage_temporary_permissions &&
                                    !permission.revoked_at
                                "
                                label="Visszavonás"
                                text
                                size="small"
                                severity="danger"
                                @click="revokeTemporaryPermission(permission)"
                            />
                        </div>
                    </div>
                </section>

                <section
                    class="rounded-xl border border-bakery-brown/15 p-4 xl:col-span-2"
                >
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="font-semibold text-bakery-dark">Kedvezmények</h3>
                        <Button
                            v-if="selectedDiscountId"
                            label="Új kedvezmény"
                            text
                            size="small"
                            @click="resetDiscountForm"
                        />
                    </div>
                    <form
                        v-if="can.manage_discounts"
                        class="grid gap-3 md:grid-cols-2"
                        @submit.prevent="submitDiscount"
                    >
                        <Select
                            v-model="discountForm.type"
                            :options="discountTypeOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                        />
                        <InputNumber
                            v-model="discountForm.value"
                            :min="0"
                            :max="discountForm.type === 'percent' ? 100 : undefined"
                            :min-fraction-digits="0"
                            :max-fraction-digits="2"
                            fluid
                        />
                        <InputText
                            v-model="discountForm.starts_at"
                            type="datetime-local"
                            class="w-full"
                        />
                        <InputText
                            v-model="discountForm.expires_at"
                            type="datetime-local"
                            class="w-full"
                        />
                        <div class="flex items-center gap-2">
                            <ToggleSwitch v-model="discountForm.active" /><span
                                class="text-sm text-bakery-dark/75"
                                >Aktív</span
                            >
                        </div>
                        <Textarea
                            v-model="discountForm.reason"
                            rows="2"
                            class="w-full md:col-span-2"
                            placeholder="Indok"
                        />
                        <p
                            v-if="discountForm.errors.value"
                            class="text-xs text-red-700 md:col-span-2"
                        >
                            {{ discountForm.errors.value }}
                        </p>
                        <Button
                            class="md:col-span-2"
                            :label="
                                selectedDiscountId
                                    ? 'Kedvezmény mentése'
                                    : 'Kedvezmény hozzáadása'
                            "
                            :loading="discountForm.processing"
                        />
                    </form>
                    <DataTable class="mt-4" :value="selectedUser.discounts" size="small">
                        <template #empty
                            ><p class="py-3 text-sm text-bakery-dark/60">
                                Nincs kedvezmény.
                            </p></template
                        >
                        <Column header="Érték"
                            ><template #body="{ data }">{{
                                discountLabel(data)
                            }}</template></Column
                        >
                        <Column field="reason" header="Indok" />
                        <Column header="Aktív"
                            ><template #body="{ data }"
                                ><Checkbox
                                    :model-value="data.active"
                                    binary
                                    disabled /></template
                        ></Column>
                        <Column header="Műveletek"
                            ><template #body="{ data }"
                                ><Button
                                    icon="pi pi-pencil"
                                    text
                                    rounded
                                    @click="editDiscount(data)" /><Button
                                    icon="pi pi-ban"
                                    text
                                    rounded
                                    severity="danger"
                                    @click="deactivateDiscount(data)" /></template
                        ></Column>
                    </DataTable>
                </section>
            </div>

            <template #footer>
                <Button label="Mégse" severity="secondary" @click="editVisible = false" />
                <Button
                    type="submit"
                    form="user-edit-form"
                    label="Mentés"
                    :loading="userForm.processing"
                />
            </template>
        </Dialog>

        <ConfirmDialog />
    </div>
</template>
