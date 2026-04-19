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
import CategoryFormModal from '../../../Components/Admin/Categories/CategoryFormModal.vue';
import CategoryStatusBadge from '../../../Components/Admin/Categories/CategoryStatusBadge.vue';
import SectionTitle from '../../../Components/SectionTitle.vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    categories: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const confirm = useConfirm();
const loading = ref(false);
const modalVisible = ref(false);
const mode = ref('create');
const editingId = ref(null);

const filterState = reactive({
    search: props.filters.search ?? '',
    sort_field: props.filters.sort_field ?? 'sort_order',
    sort_direction: props.filters.sort_direction ?? 'asc',
    per_page: props.filters.per_page ?? 10,
});

const perPageOptions = [
    { label: '10 / oldal', value: 10 },
    { label: '20 / oldal', value: 20 },
    { label: '50 / oldal', value: 50 },
];

const form = useForm({
    name: '',
    slug: '',
    description: '',
    is_active: true,
    sort_order: 0,
});

const sortOrder = computed(() => (filterState.sort_direction === 'asc' ? 1 : -1));
const currentPage = computed(() => props.categories.current_page ?? 1);
const first = computed(() => (currentPage.value - 1) * (props.categories.per_page ?? 10));

const load = (extra = {}) => {
    loading.value = true;

    router.get(
        '/admin/categories',
        {
            search: filterState.search || undefined,
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
    mode.value = 'create';
    editingId.value = null;
    form.reset();
    form.clearErrors();
    form.name = '';
    form.slug = '';
    form.description = '';
    form.is_active = true;
    form.sort_order = 0;
    modalVisible.value = true;
};

const openEdit = (category) => {
    mode.value = 'edit';
    editingId.value = category.id;
    form.clearErrors();
    form.name = category.name;
    form.slug = category.slug;
    form.description = category.description ?? '';
    form.is_active = category.is_active;
    form.sort_order = category.sort_order;
    modalVisible.value = true;
};

const submitForm = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            modalVisible.value = false;
            form.reset();
        },
    };

    if (mode.value === 'create') {
        form.post('/admin/categories', options);
        return;
    }

    form.put(`/admin/categories/${editingId.value}`, options);
};

const confirmDelete = (category) => {
    confirm.require({
        header: 'Kategoria torlese',
        message: `Biztosan torlod ezt a kategoriat: ${category.name}?`,
        rejectLabel: 'Megse',
        acceptLabel: 'Torles',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(`/admin/categories/${category.id}`, {
                preserveScroll: true,
            });
        },
    });
};
</script>

<template>
    <Head title="Categories" />

    <div class="space-y-6">
        <SectionTitle
            eyebrow="Admin / Categories"
            title="Kategoriak"
            description="Referencia CRUD modul teljes repository-service-policy architekturaval."
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Kereses</label>
                        <InputText v-model="filterState.search" placeholder="Nev vagy slug" class="min-w-64" @keyup.enter="submitFilters" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Talalat / oldal</label>
                        <Select v-model="filterState.per_page" :options="perPageOptions" option-label="label" option-value="value" class="min-w-40" @change="submitFilters" />
                    </div>
                    <Button icon="pi pi-search" label="Kereses" @click="submitFilters" />
                </div>

                <Button icon="pi pi-plus" label="Uj kategoria" @click="openCreate" />
            </div>

            <DataTable
                class="mt-4"
                :value="categories.data"
                lazy
                paginator
                :rows="categories.per_page"
                :first="first"
                :total-records="categories.total"
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
                        Nincs megjelenitheto kategoria. Hozd letre az elsot.
                    </div>
                </template>

                <Column field="name" header="Nev" sortable>
                    <template #body="{ data }">
                        <div>
                            <p class="font-semibold text-bakery-dark">{{ data.name }}</p>
                            <p class="text-xs text-bakery-dark/60">/{{ data.slug }}</p>
                        </div>
                    </template>
                </Column>
                <Column field="sort_order" header="Sorrend" sortable />
                <Column field="products_count" header="Termekek" />
                <Column field="is_active" header="Statusz" sortable>
                    <template #body="{ data }">
                        <CategoryStatusBadge :active="data.is_active" />
                    </template>
                </Column>
                <Column header="Muveletek" :exportable="false">
                    <template #body="{ data }">
                        <div class="flex items-center gap-2">
                            <Button icon="pi pi-pencil" size="small" text rounded @click="openEdit(data)" />
                            <Button icon="pi pi-trash" size="small" text rounded severity="danger" @click="confirmDelete(data)" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <CategoryFormModal v-model:visible="modalVisible" :mode="mode" :form="form" @submit="submitForm" />
        <ConfirmDialog />
    </div>
</template>
