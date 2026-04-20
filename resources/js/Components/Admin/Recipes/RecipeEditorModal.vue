<script setup>
import { computed, reactive, watch } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import RecipeItemForm from './RecipeItemForm.vue';
import RecipeIngredientList from './RecipeIngredientList.vue';

const props = defineProps({
    visible: { type: Boolean, required: true },
    recipe: { type: Object, default: null },
    ingredients: { type: Array, required: true },
    errors: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['update:visible', 'save-item', 'delete-item']);

const form = reactive({
    id: null,
    ingredient_id: null,
    quantity: null,
    sort_order: 0,
    notes: '',
});

const ingredientOptions = computed(() =>
    props.ingredients.map((ingredient) => ({
        id: ingredient.id,
        name: ingredient.name,
        unit: ingredient.unit,
        is_low_stock: ingredient.is_low_stock,
    })),
);

const selectedIngredient = computed(() => ingredientOptions.value.find((ingredient) => ingredient.id === form.ingredient_id) ?? null);

const resetForm = () => {
    form.id = null;
    form.ingredient_id = ingredientOptions.value[0]?.id ?? null;
    form.quantity = null;
    form.sort_order = 0;
    form.notes = '';
};

watch(
    () => props.visible,
    (open) => {
        if (open) {
            resetForm();
        }
    },
);

const editItem = (item) => {
    form.id = item.id;
    form.ingredient_id = item.ingredient_id;
    form.quantity = item.quantity;
    form.sort_order = item.sort_order;
    form.notes = item.notes ?? '';
};

const submit = () => {
    emit('save-item', {
        id: form.id,
        ingredient_id: form.ingredient_id,
        quantity: form.quantity,
        sort_order: form.sort_order ?? 0,
        notes: form.notes || null,
    });
};
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        :header="recipe ? `Recept szerkesztes: ${recipe.name}` : 'Recept szerkesztese'"
        :style="{ width: '64rem', maxWidth: '98vw' }"
        :content-style="{ maxHeight: '70vh', overflowY: 'auto' }"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <div class="space-y-6">
            <div class="grid gap-2 rounded-2xl border border-bakery-brown/10 bg-[#fcf7ef] p-3 sm:grid-cols-2">
                <p class="text-sm text-bakery-dark/80">Kategoria: <span class="font-medium text-bakery-dark">{{ recipe?.category_name ?? 'Nincs' }}</span></p>
                <p class="text-sm text-bakery-dark/80">
                    Low stock erintett: <span class="font-medium text-bakery-dark">{{ recipe?.low_stock_ingredients_count ?? 0 }}</span>
                </p>
            </div>

            <RecipeItemForm
                :form="form"
                :ingredient-options="ingredientOptions"
                :selected-ingredient="selectedIngredient"
                :errors="errors"
                @submit="submit"
                @reset="resetForm"
            />

            <section class="space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="font-medium text-bakery-dark">Recepttetel lista</h4>
                    <span class="text-sm text-bakery-dark/70">{{ recipe?.recipe_items_count ?? 0 }} tetel</span>
                </div>

                <RecipeIngredientList :items="recipe?.product_ingredients ?? []" />

                <div v-if="(recipe?.product_ingredients?.length ?? 0) > 0" class="space-y-2">
                    <p class="text-xs uppercase tracking-[0.12em] text-bakery-brown/75">Muveletek</p>
                    <div class="space-y-2">
                        <div
                            v-for="item in recipe?.product_ingredients ?? []"
                            :key="`actions-${item.id}`"
                            class="flex items-center justify-between rounded-xl border border-bakery-brown/10 bg-white/80 px-3 py-2"
                        >
                            <p class="text-sm text-bakery-dark">{{ item.ingredient_name }}</p>
                            <div class="flex items-center gap-2">
                                <Button icon="pi pi-pencil" text size="small" rounded @click="editItem(item)" />
                                <Button
                                    icon="pi pi-trash"
                                    text
                                    size="small"
                                    rounded
                                    severity="danger"
                                    @click="emit('delete-item', item)"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <template #footer>
            <div class="flex justify-end">
                <Button type="button" severity="secondary" label="Bezaras" @click="emit('update:visible', false)" />
            </div>
        </template>
    </Dialog>
</template>
