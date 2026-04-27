<script setup>
import { computed, reactive, watch } from "vue";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import InputNumber from "primevue/inputnumber";
import InputText from "primevue/inputtext";
import Select from "primevue/select";

const props = defineProps({
    visible: { type: Boolean, required: true },
    item: { type: Object, default: null },
    ingredients: { type: Array, required: true },
    errors: { type: Object, default: () => ({}) },
});

const emit = defineEmits(["update:visible", "submit"]);

const form = reactive({
    id: null,
    ingredient_id: null,
    quantity: null,
    sort_order: 0,
    notes: "",
});

const ingredientOptions = computed(() =>
    props.ingredients.map((ingredient) => ({
        id: ingredient.id,
        name: ingredient.name,
        unit: ingredient.unit,
        is_low_stock: ingredient.is_low_stock,
    }))
);

const selectedIngredient = computed(
    () =>
        ingredientOptions.value.find(
            (ingredient) => ingredient.id === form.ingredient_id
        ) ?? null
);

const resetForm = () => {
    form.id = null;
    form.ingredient_id = ingredientOptions.value[0]?.id ?? null;
    form.quantity = null;
    form.sort_order = 0;
    form.notes = "";
};

const fillForm = () => {
    if (!props.item) {
        resetForm();
        return;
    }

    form.id = props.item.id;
    form.ingredient_id = props.item.ingredient_id;
    form.quantity = props.item.quantity;
    form.sort_order = props.item.sort_order ?? 0;
    form.notes = props.item.notes ?? "";
};

watch(
    () => props.visible,
    (visible) => {
        if (visible) {
            fillForm();
        }
    }
);

const submit = () => {
    emit("submit", {
        id: form.id,
        ingredient_id: form.ingredient_id,
        quantity: form.quantity,
        sort_order: form.sort_order ?? 0,
        notes: form.notes || null,
    });
};

const close = () => emit("update:visible", false);
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        :header="item ? 'Hozzávaló szerkesztése' : 'Új hozzávaló'"
        :style="{ width: '44rem', maxWidth: '97vw' }"
        :content-style="{ maxHeight: '70vh', overflowY: 'auto' }"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <form
            id="recipe-ingredient-form"
            class="grid gap-3 rounded-2xl border border-bakery-brown/15 bg-white/85 p-4 md:grid-cols-4"
            @submit.prevent="submit"
        >
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
                                <p class="text-xs text-bakery-dark/70">
                                    {{ slotProps.option.unit }}
                                </p>
                            </div>
                            <span
                                class="rounded-full px-2 py-0.5 text-xs"
                                :class="
                                    slotProps.option.is_low_stock
                                        ? 'bg-amber-100 text-amber-800'
                                        : 'bg-emerald-100 text-emerald-800'
                                "
                            >
                                {{
                                    slotProps.option.is_low_stock
                                        ? "Alacsony készlet"
                                        : "OK"
                                }}
                            </span>
                        </div>
                    </template>
                </Select>
                <p v-if="errors.ingredient_id" class="mt-1 text-xs text-red-700">
                    {{ errors.ingredient_id }}
                </p>
            </div>

            <div>
                <InputNumber
                    v-model="form.quantity"
                    mode="decimal"
                    :min="0.001"
                    :min-fraction-digits="0"
                    :max-fraction-digits="3"
                    placeholder="Mennyiség"
                    fluid
                />
                <p class="mt-1 text-xs text-bakery-dark/65">
                    {{ selectedIngredient ? selectedIngredient.unit : "Mertekegyseg" }}
                </p>
                <p v-if="errors.quantity" class="text-xs text-red-700">
                    {{ errors.quantity }}
                </p>
            </div>

            <div>
                <InputNumber
                    v-model="form.sort_order"
                    :min="0"
                    placeholder="Sorrend"
                    fluid
                />
                <p v-if="errors.sort_order" class="mt-1 text-xs text-red-700">
                    {{ errors.sort_order }}
                </p>
            </div>

            <div class="md:col-span-4">
                <InputText v-model="form.notes" placeholder="Megjegyzés" class="w-full" />
                <p v-if="errors.notes" class="mt-1 text-xs text-red-700">
                    {{ errors.notes }}
                </p>
            </div>
        </form>
        <template #footer>
            <div class="flex justify-end gap-2">
                <Button type="button" severity="secondary" label="Mégse" @click="close" />
                <Button
                    type="submit"
                    form="recipe-ingredient-form"
                    :label="item ? 'Mentés' : 'Hozzáadás'"
                />
            </div>
        </template>
    </Dialog>
</template>
