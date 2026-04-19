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
import CategoryStatusBadge from '../../../Components/Admin/Categories/CategoryStatusBadge.vue';
import ProductFormModal from '../../../Components/Admin/Products/ProductFormModal.vue';
import ProductPrice from '../../../Components/Admin/Products/ProductPrice.vue';
import ProductRecipeModal from '../../../Components/Admin/Products/ProductRecipeModal.vue';
import ProductStockBadge from '../../../Components/Admin/Products/ProductStockBadge.vue';
import SectionTitle from '../../../Components/SectionTitle.vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    products: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    ingredients: {
        type: Array,
        required: true,
    },
    stockStatuses: {
        type: Array,
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
const recipeModalVisible = ref(false);
const recipeProductId = ref(null);
const recipeErrors = ref({});

const filterState = reactive({
    search: props.filters.search ?? '',
    category_id: props.filters.category_id ?? null,
    is_active: props.filters.is_active ?? '',
    sort_field: props.filters.sort_field ?? 'sort_order',
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

const form = useForm({
    category_id: null,
    name: '',
    slug: '',
    short_description: '',
    description: '',
    price: 0,
    is_active: true,
    is_featured: false,
    stock_status: 'in_stock',
    image_path: '',
    sort_order: 0,
});

const sortOrder = computed(() => (filterState.sort_direction === 'asc' ? 1 : -1));
const currentPage = computed(() => props.products.current_page ?? 1);
const first = computed(() => (currentPage.value - 1) * (props.products.per_page ?? 10));
const recipeProduct = computed(() => props.products.data.find((product) => product.id === recipeProductId.value) ?? null);

const load = (extra = {}) => {
    loading.value = true;

    router.get(
        '/admin/products',
        {
            search: filterState.search || undefined,
            category_id: filterState.category_id || undefined,
            is_active: filterState.is_active,
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
    form.category_id = props.categories[0]?.id ?? null;
    form.name = '';
    form.slug = '';
    form.short_description = '';
    form.description = '';
    form.price = 0;
    form.is_active = true;
    form.is_featured = false;
    form.stock_status = 'in_stock';
    form.image_path = '';
    form.sort_order = 0;
    modalVisible.value = true;
};

const openEdit = (product) => {
    mode.value = 'edit';
    editingId.value = product.id;
    form.clearErrors();
    form.category_id = product.category_id;
    form.name = product.name;
    form.slug = product.slug;
    form.short_description = product.short_description ?? '';
    form.description = product.description ?? '';
    form.price = product.price;
    form.is_active = product.is_active;
    form.is_featured = product.is_featured;
    form.stock_status = product.stock_status;
    form.image_path = product.image_path ?? '';
    form.sort_order = product.sort_order;
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
        form.post('/admin/products', options);
        return;
    }

    form.put(`/admin/products/${editingId.value}`, options);
};

const confirmDelete = (product) => {
    confirm.require({
        header: 'Termek torlese',
        message: `Biztosan torlod ezt a termeket: ${product.name}?`,
        rejectLabel: 'Megse',
        acceptLabel: 'Torles',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(`/admin/products/${product.id}`, {
                preserveScroll: true,
            });
        },
    });
};

const openRecipe = (product) => {
    recipeProductId.value = product.id;
    recipeErrors.value = {};
    recipeModalVisible.value = true;
};

const saveRecipeItem = (payload) => {
    if (!recipeProduct.value) {
        return;
    }

    const url = payload.id
        ? `/admin/products/${recipeProduct.value.id}/ingredients/${payload.id}`
        : `/admin/products/${recipeProduct.value.id}/ingredients`;

    const request = payload.id ? router.put : router.post;

    request(url, payload, {
        preserveScroll: true,
        preserveState: true,
        onError: (errors) => {
            recipeErrors.value = errors;
        },
        onSuccess: () => {
            recipeErrors.value = {};
        },
    });
};

const deleteRecipeItem = (item) => {
    if (!recipeProduct.value) {
        return;
    }

    confirm.require({
        header: 'Recept tetel torlese',
        message: `Biztosan torlod ezt a recept tetelt: ${item.ingredient_name}?`,
        rejectLabel: 'Megse',
        acceptLabel: 'Torles',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(`/admin/products/${recipeProduct.value.id}/ingredients/${item.id}`, {
                preserveScroll: true,
                preserveState: true,
            });
        },
    });
};
</script>

<template>
    <Head title="Products" />

    <div class="space-y-6">
        <SectionTitle
            eyebrow="Admin / Products"
            title="Termekek"
            description="A Categories referencia modul mintajara epitett teljes Products CRUD."
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <div class="flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Kereses</label>
                        <InputText v-model="filterState.search" placeholder="Nev vagy slug" @keyup.enter="submitFilters" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Kategoria</label>
                        <Select
                            v-model="filterState.category_id"
                            :options="[{ id: null, name: 'Mind' }, ...categories]"
                            option-label="name"
                            option-value="id"
                            @change="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Statusz</label>
                        <Select v-model="filterState.is_active" :options="activeOptions" option-label="label" option-value="value" @change="submitFilters" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Talalat / oldal</label>
                        <Select v-model="filterState.per_page" :options="perPageOptions" option-label="label" option-value="value" @change="submitFilters" />
                    </div>
                </div>

                <div class="flex gap-2">
                    <Button icon="pi pi-search" label="Kereses" @click="submitFilters" />
                    <Button icon="pi pi-plus" label="Uj termek" @click="openCreate" />
                </div>
            </div>

            <DataTable
                class="mt-4"
                :value="products.data"
                lazy
                paginator
                :rows="products.per_page"
                :first="first"
                :total-records="products.total"
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
                        Nincs megjelenitheto termek. Hozd letre az elsot.
                    </div>
                </template>

                <Column field="name" header="Termek" sortable>
                    <template #body="{ data }">
                        <div>
                            <p class="font-semibold text-bakery-dark">{{ data.name }}</p>
                            <p class="text-xs text-bakery-dark/60">/{{ data.slug }}</p>
                        </div>
                    </template>
                </Column>
                <Column field="category_name" header="Kategoria" />
                <Column field="price" header="Ar" sortable>
                    <template #body="{ data }">
                        <ProductPrice :price="data.price" />
                    </template>
                </Column>
                <Column field="stock_status" header="Keszlet">
                    <template #body="{ data }">
                        <ProductStockBadge :status="data.stock_status" />
                    </template>
                </Column>
                <Column field="is_active" header="Aktiv" sortable>
                    <template #body="{ data }">
                        <CategoryStatusBadge :active="data.is_active" />
                    </template>
                </Column>
                <Column field="sort_order" header="Sorrend" sortable />
                <Column header="Muveletek" :exportable="false">
                    <template #body="{ data }">
                        <div class="flex items-center gap-2">
                            <Button icon="pi pi-list-check" size="small" text rounded @click="openRecipe(data)" />
                            <Button icon="pi pi-pencil" size="small" text rounded @click="openEdit(data)" />
                            <Button icon="pi pi-trash" size="small" text rounded severity="danger" @click="confirmDelete(data)" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <ProductFormModal
            v-model:visible="modalVisible"
            :mode="mode"
            :form="form"
            :categories="categories"
            :stock-statuses="stockStatuses"
            @submit="submitForm"
        />
        <ProductRecipeModal
            v-model:visible="recipeModalVisible"
            :product="recipeProduct"
            :ingredients="ingredients"
            :errors="recipeErrors"
            @save-item="saveRecipeItem"
            @delete-item="deleteRecipeItem"
        />
        <ConfirmDialog />
    </div>
</template>
