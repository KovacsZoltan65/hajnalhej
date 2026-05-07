<script setup>
import { Head, router } from "@inertiajs/vue3";
import { computed, onMounted, reactive, ref, watch } from "vue";
import Button from "primevue/button";
import ConfirmDialog from "primevue/confirmdialog";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import { useConfirm } from "primevue/useconfirm";
import AdminTableToolbar from "@/Components/Admin/AdminTableToolbar.vue";
import RecipeEditorModal from "@/Components/Admin/Recipes/RecipeEditorModal.vue";
import RecipeIngredientModal from "@/Components/Admin/Recipes/RecipeIngredientModal.vue";
import RecipeStepModal from "@/Components/Admin/Recipes/RecipeStepModal.vue";
import RecipeSummaryCard from "@/Components/Admin/Recipes/RecipeSummaryCard.vue";
import RecipeTable from "@/Components/Admin/Recipes/RecipeTable.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { pageOptions, activeOptions as createActiveOptions } from "@/Utils/functions.js";
import { trans } from "laravel-vue-i18n";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    recipes: {
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
    stepTypes: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    summary: {
        type: Object,
        required: true,
    },
});

const confirm = useConfirm();
const loading = ref(false);
const editorVisible = ref(false);
const editorRecipe = ref(null);
const ingredientModalVisible = ref(false);
const stepModalVisible = ref(false);
const editingIngredient = ref(null);
const editingStep = ref(null);
const ingredientErrors = ref({});
const stepErrors = ref({});

const filterState = reactive({
    product_id: props.filters.product_id ?? null,
    search: props.filters.search ?? "",
    category_id: props.filters.category_id ?? null,
    is_active: props.filters.is_active ?? "",
    recipe_presence: props.filters.recipe_presence ?? "all",
    has_low_stock_ingredient: props.filters.has_low_stock_ingredient ?? "",
    sort_field: props.filters.sort_field ?? "name",
    sort_direction: props.filters.sort_direction ?? "asc",
    per_page: props.filters.per_page ?? 10,
});

const perPageOptions = pageOptions(trans, [10, 20, 50]);
const activeOptions = createActiveOptions(trans);
/*
const activeOptions = [
    { label: trans("common.all"), value: "" },
    { label: trans("common.active"), value: "1" },
    { label: trans("common.inactive"), value: "0" },
];
*/

const recipePresenceOptions = [
    { label: trans("common.all"), value: "all" },
    { label: trans("admin_recipes.filters.with_recipe"), value: "with_recipe" },
    { label: trans("admin_recipes.filters.without_recipe"), value: "without_recipe" },
];

const lowStockOptions = [
    { label: trans("common.all"), value: "" },
    { label: trans("admin_recipes.filters.low_stock_affected"), value: "1" },
];

const categoryOptions = computed(() => [{ id: null, name: trans("common.all") }, ...props.categories]);
const sortOrder = computed(() => (filterState.sort_direction === "asc" ? 1 : -1));
const currentPage = computed(() => props.recipes.current_page ?? 1);
const first = computed(() => (currentPage.value - 1) * (props.recipes.per_page ?? 10));

const load = (extra = {}) => {
    loading.value = true;

    router.get(
        route("admin.recipes.index"),
        {
            product_id: filterState.product_id || undefined,
            search: filterState.search || undefined,
            category_id: filterState.category_id || undefined,
            is_active: filterState.is_active,
            recipe_presence: filterState.recipe_presence,
            has_low_stock_ingredient: filterState.has_low_stock_ingredient,
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
        }
    );
};

const submitFilters = () => load({ page: 1 });
const clearProductFocus = () => {
    filterState.product_id = null;
    load({ page: 1, product_id: undefined });
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

const openEditor = (recipe) => {
    editorRecipe.value = recipe;
    ingredientErrors.value = {};
    stepErrors.value = {};
    editorVisible.value = true;
};

onMounted(() => {
    const selectedProductId = Number(props.filters.product_id ?? 0);
    if (selectedProductId <= 0) {
        return;
    }

    const recipe = props.recipes.data.find((item) => item.id === selectedProductId) ?? null;
    if (!recipe) {
        return;
    }

    openEditor(recipe);
});

const saveRecipeItem = (payload) => {
    if (!editorRecipe.value) {
        return;
    }

    const url = payload.id
        ? route("admin.products.ingredients.update", [editorRecipe.value.id, payload.id])
        : route("admin.products.ingredients.store", editorRecipe.value.id);

    const options = {
        preserveScroll: true,
        preserveState: true,
        onError: (errors) => {
            ingredientErrors.value = errors;
        },
        onSuccess: () => {
            ingredientErrors.value = {};
            ingredientModalVisible.value = false;
            editingIngredient.value = null;
            load({ product_id: editorRecipe.value?.id ?? undefined });
        },
    };

    if (payload.id) {
        router.put(url, payload, options);
        return;
    }

    router.post(url, payload, options);
};

const saveRecipeStep = (payload) => {
    if (!editorRecipe.value) {
        return;
    }

    const url = payload.id
        ? route("admin.products.recipe-steps.update", [editorRecipe.value.id, payload.id])
        : route("admin.products.recipe-steps.store", editorRecipe.value.id);

    const options = {
        preserveScroll: true,
        preserveState: true,
        onError: (errors) => {
            stepErrors.value = errors;
        },
        onSuccess: () => {
            stepErrors.value = {};
            stepModalVisible.value = false;
            editingStep.value = null;
            load({ product_id: editorRecipe.value?.id ?? undefined });
        },
    };

    if (payload.id) {
        router.put(url, payload, options);
        return;
    }

    router.post(url, payload, options);
};

const deleteRecipeItem = (item) => {
    if (!editorRecipe.value) {
        return;
    }

    confirm.require({
        header: trans("admin_recipes.confirm_delete_item_header"),
        message: trans("admin_recipes.confirm_delete_item_message", {
            ingredient: item.ingredient_name,
        }),
        rejectLabel: trans("common.cancel"),
        acceptLabel: trans("common.delete"),
        acceptClass: "p-button-danger",
        accept: () => {
            router.delete(route("admin.products.ingredients.destroy", [editorRecipe.value.id, item.id]), {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => load({ product_id: editorRecipe.value?.id ?? undefined }),
            });
        },
    });
};

const deleteRecipeStep = (step) => {
    if (!editorRecipe.value) {
        return;
    }

    confirm.require({
        header: trans("admin_recipes.confirm_delete_step_header"),
        message: trans("admin_recipes.confirm_delete_step_message", {
            step: step.title,
        }),
        rejectLabel: trans("common.cancel"),
        acceptLabel: trans("common.delete"),
        acceptClass: "p-button-danger",
        accept: () => {
            router.delete(route("admin.products.recipe-steps.destroy", [editorRecipe.value.id, step.id]), {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => load({ product_id: editorRecipe.value?.id ?? undefined }),
            });
        },
    });
};

const openIngredientCreate = () => {
    editingIngredient.value = null;
    ingredientErrors.value = {};
    ingredientModalVisible.value = true;
};

const openIngredientEdit = (item) => {
    editingIngredient.value = item;
    ingredientErrors.value = {};
    ingredientModalVisible.value = true;
};

const openStepCreate = () => {
    editingStep.value = null;
    stepErrors.value = {};
    stepModalVisible.value = true;
};

const openStepEdit = (step) => {
    editingStep.value = step;
    stepErrors.value = {};
    stepModalVisible.value = true;
};

watch(
    () => props.recipes.data,
    (recipes) => {
        if (!editorRecipe.value) {
            return;
        }

        const fresh = recipes.find((recipe) => recipe.id === editorRecipe.value.id) ?? null;
        editorRecipe.value = fresh;

        if (!fresh) {
            editorVisible.value = false;
            ingredientModalVisible.value = false;
            stepModalVisible.value = false;
        }
    }
);
</script>

<template>
    <Head :title="trans('admin_recipes.meta_title')" />

    <div class="space-y-6">
        <SectionTitle
            :eyebrow="trans('admin_recipes.eyebrow')"
            :title="trans('admin_recipes.title')"
            :description="trans('admin_recipes.description')"
        />

        <RecipeSummaryCard :summary="summary" />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <div
                v-if="filterState.product_id"
                class="mb-3 flex items-center justify-between rounded-lg border border-bakery-gold/40 bg-[#fdf8ec] px-3 py-2"
            >
                <p class="text-sm text-bakery-dark/80">
                    {{ trans("admin_recipes.focus.active") }}
                </p>
                <Button size="small" text :label="trans('admin_recipes.focus.clear')" @click="clearProductFocus" />
            </div>
            <AdminTableToolbar :filters-grid-class="'grid gap-3 sm:grid-cols-2 xl:grid-cols-5'">
                <template #filters>
                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">
                            {{ trans("common.search") }}
                        </label>
                        <InputText
                            v-model="filterState.search"
                            class="w-full"
                            :placeholder="trans('admin_recipes.filters.search_placeholder')"
                            @keyup.enter="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">
                            {{ trans("admin_recipes.filters.category") }}
                        </label>
                        <Select
                            v-model="filterState.category_id"
                            :options="categoryOptions"
                            option-label="name"
                            option-value="id"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">
                            {{ trans("common.status") }}
                        </label>
                        <Select
                            v-model="filterState.is_active"
                            :options="activeOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">
                            {{ trans("admin_recipes.filters.recipe_presence") }}
                        </label>
                        <Select
                            v-model="filterState.recipe_presence"
                            :options="recipePresenceOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">
                            {{ trans("admin_recipes.filters.low_stock") }}
                        </label>
                        <Select
                            v-model="filterState.has_low_stock_ingredient"
                            :options="lowStockOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                            @change="submitFilters"
                        />
                    </div>
                </template>

                <template #actions>
                    <Select
                        v-model="filterState.per_page"
                        :options="perPageOptions"
                        option-label="label"
                        option-value="value"
                        class="w-36"
                        @change="submitFilters"
                    />
                    <Button icon="pi pi-search" :label="trans('common.search')" @click="submitFilters" />
                </template>
            </AdminTableToolbar>

            <div class="mt-4">
                <RecipeTable
                    :recipes="recipes"
                    :loading="loading"
                    :first="first"
                    :sort-field="filterState.sort_field"
                    :sort-order="sortOrder"
                    @sort="onSort"
                    @page="onPage"
                    @open-editor="openEditor"
                />
            </div>
        </div>

        <RecipeEditorModal
            v-model:visible="editorVisible"
            :recipe="editorRecipe"
            @open-ingredient-create="openIngredientCreate"
            @open-ingredient-edit="openIngredientEdit"
            @delete-ingredient="deleteRecipeItem"
            @open-step-create="openStepCreate"
            @open-step-edit="openStepEdit"
            @delete-step="deleteRecipeStep"
        />
        <RecipeIngredientModal
            v-model:visible="ingredientModalVisible"
            :item="editingIngredient"
            :ingredients="ingredients"
            :errors="ingredientErrors"
            @submit="saveRecipeItem"
        />
        <RecipeStepModal
            v-model:visible="stepModalVisible"
            :item="editingStep"
            :step-types="stepTypes"
            :errors="stepErrors"
            @submit="saveRecipeStep"
        />
        <ConfirmDialog />
    </div>
</template>
