<script setup>
import Button from "primevue/button";
import InputNumber from "primevue/inputnumber";
import Select from "primevue/select";
import { computed, reactive } from "vue";
import { Link } from "@inertiajs/vue3";

const rows = defineModel({ type: Array, required: true });

const props = defineProps({
    ingredients: { type: Array, required: true },
    errors: { type: Object, default: () => ({}) },
});

const draft = reactive({
    ingredient_id: null,
    quantity: 1,
});

const selectedIds = computed(() => rows.value.map((row) => row.ingredient_id));
const availableIngredients = computed(() =>
    props.ingredients.filter((ingredient) => !selectedIds.value.includes(ingredient.id))
);

const ingredientById = (id) => props.ingredients.find((ingredient) => ingredient.id === id);

const addRow = () => {
    if (!draft.ingredient_id || !draft.quantity) {
        return;
    }

    rows.value.push({
        ingredient_id: draft.ingredient_id,
        quantity: draft.quantity,
        sort_order: rows.value.length + 1,
        notes: "",
    });

    draft.ingredient_id = null;
    draft.quantity = 1;
};

const removeRow = (index) => {
    rows.value.splice(index, 1);
};
</script>

<template>
    <div class="space-y-4">
        <div
            class="grid gap-3 rounded-lg border border-bakery-brown/15 bg-[#fcf7ef] p-3 lg:grid-cols-[minmax(0,1fr)_10rem_auto]"
        >
            <Select
                v-model="draft.ingredient_id"
                :options="availableIngredients"
                option-label="name"
                option-value="id"
                :placeholder="$t('admin.products.flow.placeholders.ingredient')"
                class="w-full"
            >
                <template #option="{ option }">
                    <div class="flex w-full items-center justify-between gap-3">
                        <span>{{ option.name }} / {{ option.unit }}</span>
                        <span
                            v-if="option.is_low_stock"
                            class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-800"
                        >
                            {{ $t("admin.products.flow.low_stock") }}
                        </span>
                    </div>
                </template>
            </Select>
            <InputNumber
                v-model="draft.quantity"
                :min="0"
                :min-fraction-digits="0"
                :max-fraction-digits="3"
                class="w-full"
            />
            <Button icon="pi pi-plus" :label="$t('admin.products.flow.actions.add_ingredient')" @click="addRow" />
        </div>

        <div
            v-if="rows.length === 0"
            class="rounded-lg border border-dashed border-bakery-brown/20 p-5 text-center text-sm text-bakery-dark/70"
        >
            <p>{{ $t("admin.products.flow.empty.ingredients") }}</p>
            <Link
                :href="route('admin.ingredients.index')"
                class="mt-3 inline-flex rounded-lg border border-bakery-brown/20 px-3 py-2 text-sm font-semibold text-bakery-brown hover:bg-bakery-brown/10"
            >
                {{ $t("admin.products.flow.actions.create_ingredient") }}
            </Link>
        </div>

        <div v-else class="space-y-2">
            <div
                v-for="(row, index) in rows"
                :key="`${row.ingredient_id}-${index}`"
                class="grid items-center gap-3 rounded-lg border border-bakery-brown/10 bg-white p-3 sm:grid-cols-[minmax(0,1fr)_9rem_auto]"
            >
                <div>
                    <p class="font-semibold text-bakery-dark">
                        {{ ingredientById(row.ingredient_id)?.name }}
                    </p>
                    <p class="text-xs text-bakery-dark/60">
                        {{ ingredientById(row.ingredient_id)?.unit }}
                        <span v-if="ingredientById(row.ingredient_id)?.is_low_stock"
                            >/ {{ $t("admin.products.flow.low_stock") }}</span
                        >
                    </p>
                </div>
                <InputNumber v-model="row.quantity" :min="0" :max-fraction-digits="3" />
                <Button icon="pi pi-trash" text rounded severity="danger" @click="removeRow(index)" />
            </div>
        </div>
        <p v-if="errors.ingredients" class="text-xs text-red-700">
            {{ errors.ingredients.startsWith?.("admin.") ? $t(errors.ingredients) : errors.ingredients }}
        </p>
    </div>
</template>
