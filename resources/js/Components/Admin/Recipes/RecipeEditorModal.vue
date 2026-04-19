<script setup>
import { computed, reactive, watch } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
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
        @update:visible="(value) => emit('update:visible', value)"
    >
        <div class="space-y-6">
            <div class="grid gap-2 rounded-2xl border border-bakery-brown/10 bg-[#fcf7ef] p-3 sm:grid-cols-2">
                <p class="text-sm text-bakery-dark/80">Kategoria: <span class="font-medium text-bakery-dark">{{ recipe?.category_name ?? 'Nincs' }}</span></p>
                <p class="text-sm text-bakery-dark/80">
                    Low stock erintett: <span class="font-medium text-bakery-dark">{{ recipe?.low_stock_ingredients_count ?? 0 }}</span>
                </p>
            </div>

            <form class="grid gap-3 rounded-2xl border border-bakery-brown/15 bg-white/85 p-4 md:grid-cols-4" @submit.prevent="submit">
                <div class="md:col-span-2">
                    <Select
                        v-model="form.ingredient_id"
                        :options="ingredientOptions"
                        option-label="name"
                        option-value="id"
                        placeholder="Alapanyag"
                        class="w-full"
                        filter
                    >
                        <template #option="slotProps">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-medium">{{ slotProps.option.name }}</p>
                                    <p class="text-xs text-bakery-dark/70">{{ slotProps.option.unit }}</p>
                                </div>
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs"
                                    :class="slotProps.option.is_low_stock ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'"
                                >
                                    {{ slotProps.option.is_low_stock ? 'Low stock' : 'OK' }}
                                </span>
                            </div>
                        </template>
                    </Select>
                    <p v-if="errors.ingredient_id" class="mt-1 text-xs text-red-700">{{ errors.ingredient_id }}</p>
                </div>

                <div>
                    <InputNumber
                        v-model="form.quantity"
                        mode="decimal"
                        :min="0.001"
                        :min-fraction-digits="0"
                        :max-fraction-digits="3"
                        placeholder="Mennyiseg"
                        fluid
                    />
                    <p class="mt-1 text-xs text-bakery-dark/65">{{ selectedIngredient ? selectedIngredient.unit : 'Mertekegyseg' }}</p>
                    <p v-if="errors.quantity" class="text-xs text-red-700">{{ errors.quantity }}</p>
                </div>

                <div>
                    <InputNumber v-model="form.sort_order" :min="0" placeholder="Sorrend" fluid />
                    <p v-if="errors.sort_order" class="mt-1 text-xs text-red-700">{{ errors.sort_order }}</p>
                </div>

                <div class="md:col-span-3">
                    <InputText v-model="form.notes" placeholder="Megjegyzes" class="w-full" />
                    <p v-if="errors.notes" class="mt-1 text-xs text-red-700">{{ errors.notes }}</p>
                </div>

                <div class="flex justify-end gap-2 md:col-span-1">
                    <Button type="button" severity="secondary" label="Uj" @click="resetForm" />
                    <Button type="submit" :label="form.id ? 'Frissites' : 'Hozzaadas'" />
                </div>
            </form>

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
    </Dialog>
</template>
