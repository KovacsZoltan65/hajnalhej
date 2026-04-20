<script setup>
import Button from 'primevue/button';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';

defineProps({
    form: {
        type: Object,
        required: true,
    },
    ingredientOptions: {
        type: Array,
        required: true,
    },
    selectedIngredient: {
        type: Object,
        default: null,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['submit', 'reset']);
</script>

<template>
    <form id="recipe-item-form" class="grid gap-3 rounded-2xl border border-bakery-brown/15 bg-white/85 p-4 md:grid-cols-4" @submit.prevent="emit('submit')">
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
            <Button type="button" severity="secondary" label="Uj" @click="emit('reset')" />
            <Button type="submit" :label="form.id ? 'Frissites' : 'Hozzaadas'" />
        </div>
    </form>
</template>
