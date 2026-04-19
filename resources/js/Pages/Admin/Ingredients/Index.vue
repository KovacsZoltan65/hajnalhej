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
import IngredientFormModal from '../../../Components/Admin/Ingredients/IngredientFormModal.vue';
import IngredientStatusBadge from '../../../Components/Admin/Ingredients/IngredientStatusBadge.vue';
import IngredientStockBadge from '../../../Components/Admin/Ingredients/IngredientStockBadge.vue';
import SectionTitle from '../../../Components/SectionTitle.vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    ingredients: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    units: {
        type: Array,
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
    is_active: props.filters.is_active ?? '',
    unit: props.filters.unit ?? '',
    sort_field: props.filters.sort_field ?? 'name',
    sort_direction: props.filters.sort_direction ?? 'asc',
    per_page: props.filters.per_page ?? 10,
});

const perPageOptions = [
    { label: '10 / oldal', value: 10 },
    { label: '20 / oldal', value: 20 },
    { label: '50 / oldal', value: 50 },
];

const activeOptions = [
    { label: 'Mind', value: '' },
    { label: 'Aktiv', value: '1' },
    { label: 'Inaktiv', value: '0' },
];

const unitOptions = computed(() => [{ label: 'Mind', value: '' }, ...props.units.map((unit) => ({ label: unit, value: unit }))]);

const form = useForm({
    name: '',
    slug: '',
    sku: '',
    unit: 'db',
    current_stock: 0,
    minimum_stock: 0,
    is_active: true,
    notes: '',
});

const sortOrder = computed(() => (filterState.sort_direction === 'asc' ? 1 : -1));
const currentPage = computed(() => props.ingredients.current_page ?? 1);
const first = computed(() => (currentPage.value - 1) * (props.ingredients.per_page ?? 10));

const load = (extra = {}) => {
    loading.value = true;

    router.get(
        '/admin/ingredients',
        {
            search: filterState.search || undefined,
            is_active: filterState.is_active,
            unit: filterState.unit || undefined,
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
    form.sku = '';
    form.unit = props.units[0] ?? 'db';
    form.current_stock = 0;
    form.minimum_stock = 0;
    form.is_active = true;
    form.notes = '';
    modalVisible.value = true;
};

const openEdit = (ingredient) => {
    mode.value = 'edit';
    editingId.value = ingredient.id;
    form.clearErrors();
    form.name = ingredient.name;
    form.slug = ingredient.slug;
    form.sku = ingredient.sku ?? '';
    form.unit = ingredient.unit;
    form.current_stock = ingredient.current_stock;
    form.minimum_stock = ingredient.minimum_stock;
    form.is_active = ingredient.is_active;
    form.notes = ingredient.notes ?? '';
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
        form.post('/admin/ingredients', options);
        return;
    }

    form.put(`/admin/ingredients/${editingId.value}`, options);
};

const confirmDelete = (ingredient) => {
    confirm.require({
        header: 'Alapanyag torlese',
        message: `Biztosan torlod ezt az alapanyagot: ${ingredient.name}?`,
        rejectLabel: 'Megse',
        acceptLabel: 'Torles',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(`/admin/ingredients/${ingredient.id}`, {
                preserveScroll: true,
            });
        },
    });
};
</script>

<template>
    <Head title="Ingredients" />

    <div class="space-y-6">
        <SectionTitle
            eyebrow="Admin / Ingredients"
            title="Alapanyagok"
            description="Alapanyag torzs low-stock jelzessel, keszen a keszletkezeles es gyartasi kalkulacio kovetkezo lepeseihez."
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <div class="flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Kereses</label>
                        <InputText v-model="filterState.search" placeholder="Nev, slug vagy SKU" @keyup.enter="submitFilters" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Statusz</label>
                        <Select v-model="filterState.is_active" :options="activeOptions" option-label="label" option-value="value" @change="submitFilters" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Mertekegyseg</label>
                        <Select v-model="filterState.unit" :options="unitOptions" option-label="label" option-value="value" @change="submitFilters" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Talalat / oldal</label>
                        <Select v-model="filterState.per_page" :options="perPageOptions" option-label="label" option-value="value" @change="submitFilters" />
                    </div>
                </div>

                <div class="flex gap-2">
                    <Button icon="pi pi-search" label="Kereses" @click="submitFilters" />
                    <Button icon="pi pi-plus" label="Uj alapanyag" @click="openCreate" />
                </div>
            </div>

            <DataTable
                class="mt-4"
                :value="ingredients.data"
                lazy
                paginator
                :rows="ingredients.per_page"
                :first="first"
                :total-records="ingredients.total"
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
                        Nincs megjelenitheto alapanyag. Hozd letre az elsot.
                    </div>
                </template>

                <Column field="name" header="Alapanyag" sortable>
                    <template #body="{ data }">
                        <div>
                            <p class="font-semibold text-bakery-dark">{{ data.name }}</p>
                            <p class="text-xs text-bakery-dark/60">/{{ data.slug }} <span v-if="data.sku">| {{ data.sku }}</span></p>
                        </div>
                    </template>
                </Column>
                <Column field="unit" header="Unit" sortable />
                <Column field="current_stock" header="Keszlet" sortable>
                    <template #body="{ data }">
                        <IngredientStockBadge
                            :current-stock="data.current_stock"
                            :minimum-stock="data.minimum_stock"
                            :unit="data.unit"
                        />
                    </template>
                </Column>
                <Column field="is_active" header="Statusz" sortable>
                    <template #body="{ data }">
                        <IngredientStatusBadge :active="data.is_active" />
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

        <IngredientFormModal v-model:visible="modalVisible" :mode="mode" :form="form" :units="units" @submit="submitForm" />
        <ConfirmDialog />
    </div>
</template>
