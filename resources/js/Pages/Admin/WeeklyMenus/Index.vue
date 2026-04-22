<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import Button from 'primevue/button';
import Column from 'primevue/column';
import ConfirmDialog from 'primevue/confirmdialog';
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import { useConfirm } from 'primevue/useconfirm';
import AdminTableToolbar from '@/Components/Admin/AdminTableToolbar.vue';
import CreateModal from '@/Components/Admin/WeeklyMenus/CreateModal.vue';
import EditModal from '@/Components/Admin/WeeklyMenus/EditModal.vue';
import WeeklyMenuItemsModal from '../../../Components/Admin/WeeklyMenus/WeeklyMenuItemsModal.vue';
import WeeklyMenuStatusBadge from '../../../Components/Admin/WeeklyMenus/WeeklyMenuStatusBadge.vue';
import SectionTitle from '../../../Components/SectionTitle.vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    weeklyMenus: { type: Object, required: true },
    filters: { type: Object, required: true },
    statuses: { type: Array, required: true },
    products: { type: Array, required: true },
});

const confirm = useConfirm();
const loading = ref(false);
const createModalVisible = ref(false);
const editModalVisible = ref(false);
const itemsVisible = ref(false);
const editingId = ref(null);
const selectedMenu = ref(null);

const filterState = reactive({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
    sort_field: props.filters.sort_field ?? 'week_start',
    sort_direction: props.filters.sort_direction ?? 'desc',
    per_page: props.filters.per_page ?? 10,
});

const statusOptions = [{ value: '', label: 'Mind' }, ...props.statuses];
const perPageOptions = [
    { label: '10 / oldal', value: 10 },
    { label: '20 / oldal', value: 20 },
    { label: '50 / oldal', value: 50 },
];

const form = useForm({
    title: '',
    slug: '',
    week_start: '',
    week_end: '',
    status: 'draft',
    public_note: '',
    internal_note: '',
    is_featured: false,
});

const sortOrder = computed(() => (filterState.sort_direction === 'asc' ? 1 : -1));
const currentPage = computed(() => props.weeklyMenus.current_page ?? 1);
const first = computed(() => (currentPage.value - 1) * (props.weeklyMenus.per_page ?? 10));

const load = (extra = {}) => {
    loading.value = true;

    router.get(
        '/admin/weekly-menus',
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
            replace: true,
            preserveScroll: true,
            onFinish: () => {
                loading.value = false;
            },
        },
    );
};

const submitFilters = () => load({ page: 1 });

const onSort = (event) => {
    filterState.sort_field = event.sortField;
    filterState.sort_direction = event.sortOrder === 1 ? 'asc' : 'desc';
    load({ page: 1 });
};

const onPage = (event) => {
    filterState.per_page = event.rows;
    load({ page: event.page + 1, per_page: event.rows });
};

const openCreate = () => {
    editModalVisible.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    form.title = '';
    form.slug = '';
    form.week_start = '';
    form.week_end = '';
    form.status = 'draft';
    form.public_note = '';
    form.internal_note = '';
    form.is_featured = false;
    createModalVisible.value = true;
};

const openEdit = (menu) => {
    createModalVisible.value = false;
    editingId.value = menu.id;
    form.clearErrors();
    form.title = menu.title;
    form.slug = menu.slug;
    form.week_start = menu.week_start;
    form.week_end = menu.week_end;
    form.status = menu.status;
    form.public_note = menu.public_note ?? '';
    form.internal_note = menu.internal_note ?? '';
    form.is_featured = menu.is_featured;
    editModalVisible.value = true;
};

const closeCreateModal = () => {
    createModalVisible.value = false;
};

const closeEditModal = () => {
    editModalVisible.value = false;
};

const submitCreate = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            closeCreateModal();
            form.reset();
        },
    };

    form.post('/admin/weekly-menus', options);
};

const submitEdit = () => {
    if (!editingId.value) {
        return;
    }

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
            form.reset();
            editingId.value = null;
        },
    };

    form.put(`/admin/weekly-menus/${editingId.value}`, options);
};

const confirmDelete = (menu) => {
    confirm.require({
        header: 'Heti menu törlése',
        message: `Biztosan torlod: ${menu.title}?`,
        rejectLabel: 'Mégse',
        acceptLabel: 'Torles',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(`/admin/weekly-menus/${menu.id}`, { preserveScroll: true });
        },
    });
};

const publish = (menu) => router.post(`/admin/weekly-menus/${menu.id}/publish`, {}, { preserveScroll: true });
const unpublish = (menu) => router.post(`/admin/weekly-menus/${menu.id}/unpublish`, {}, { preserveScroll: true });

const openItems = (menu) => {
    selectedMenu.value = menu;
    itemsVisible.value = true;
};

const saveItem = (payload) => {
    if (!selectedMenu.value) {
        return;
    }

    if (payload.id) {
        router.put(`/admin/weekly-menus/${selectedMenu.value.id}/items/${payload.id}`, payload, { preserveScroll: true });
        return;
    }

    router.post(`/admin/weekly-menus/${selectedMenu.value.id}/items`, payload, { preserveScroll: true });
};

const deleteItem = (item) => {
    if (!selectedMenu.value) {
        return;
    }

    router.delete(`/admin/weekly-menus/${selectedMenu.value.id}/items/${item.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Heti menük" />

    <div class="space-y-6">
        <SectionTitle
            eyebrow="Admin / Heti menük"
            title="Heti menuk"
            description="A heti kínálat kezelési modulja termék-hozzárendeléssel és publikációs workflow-val."
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <AdminTableToolbar :filters-grid-class="'grid gap-3 sm:grid-cols-2 xl:grid-cols-3'">
                <template #filters>
                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Keresés</label>
                        <InputText v-model="filterState.search" class="w-full" placeholder="Cim vagy slug" @keyup.enter="submitFilters" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Státusz</label>
                        <Select v-model="filterState.status" :options="statusOptions" option-label="label" option-value="value" class="w-full" @change="submitFilters" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Találat / oldal</label>
                        <Select v-model="filterState.per_page" :options="perPageOptions" option-label="label" option-value="value" class="w-full" @change="submitFilters" />
                    </div>
                </template>

                <template #actions>
                    <Button icon="pi pi-search" label="Keresés" @click="submitFilters" />
                    <Button icon="pi pi-plus" label="Új heti menu" @click="openCreate" />
                </template>
            </AdminTableToolbar>

            <DataTable
                class="mt-4"
                :value="weeklyMenus.data"
                lazy
                paginator
                :rows="weeklyMenus.per_page"
                :first="first"
                :total-records="weeklyMenus.total"
                :loading="loading"
                data-key="id"
                sort-mode="single"
                :sort-field="filterState.sort_field"
                :sort-order="sortOrder"
                @sort="onSort"
                @page="onPage"
            >
                <template #empty>
                    <div class="rounded-xl border border-dashed border-bakery-brown/25 bg-[#fcf7ef] p-6 text-center text-sm text-bakery-dark/70">
                        Nincs heti menu. Hozd letre az elso menut.
                    </div>
                </template>

                <Column field="title" header="Cim" sortable>
                    <template #body="{ data }">
                        <div>
                            <p class="font-semibold text-bakery-dark">{{ data.title }}</p>
                            <p class="text-xs text-bakery-dark/60">/{{ data.slug }}</p>
                        </div>
                    </template>
                </Column>
                <Column field="week_start" header="Het" sortable>
                    <template #body="{ data }">{{ data.week_start }} - {{ data.week_end }}</template>
                </Column>
                <Column field="status" header="Státusz" sortable>
                    <template #body="{ data }">
                        <WeeklyMenuStatusBadge :status="data.status" />
                    </template>
                </Column>
                <Column field="items_count" header="Tetelek" />
                <Column header="Muveletek">
                    <template #body="{ data }">
                        <div class="flex flex-wrap items-center gap-2">
                            <Button icon="pi pi-list" text size="small" rounded @click="openItems(data)" />
                            <Button icon="pi pi-pencil" text size="small" rounded @click="openEdit(data)" />
                            <Button icon="pi pi-trash" text size="small" rounded severity="danger" @click="confirmDelete(data)" />
                            <Button
                                v-if="data.status !== 'published'"
                                label="Közzététel"
                                size="small"
                                text
                                class="!text-green-700"
                                @click="publish(data)"
                            />
                            <Button
                                v-else
                                label="Közzététel visszavonása"
                                size="small"
                                text
                                class="!text-amber-700"
                                @click="unpublish(data)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <CreateModal v-model:visible="createModalVisible" :form="form" :statuses="statuses" @submit="submitCreate" />
        <EditModal v-model:visible="editModalVisible" :form="form" :statuses="statuses" @submit="submitEdit" />
        <WeeklyMenuItemsModal
            v-model:visible="itemsVisible"
            :menu="selectedMenu"
            :products="products"
            @save-item="saveItem"
            @delete-item="deleteItem"
        />
        <ConfirmDialog />
    </div>
</template>


