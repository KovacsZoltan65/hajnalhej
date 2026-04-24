<script setup>
import { computed } from 'vue';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    ingredientOptions: {
        type: Array,
        required: true,
    },
    productOptions: {
        type: Array,
        required: true,
    },
    wasteReasons: {
        type: Array,
        required: true,
    },
});

const selectedWasteTargetId = computed({
    get: () => (props.form.waste_type === 'product' ? props.form.product_id : props.form.ingredient_id),
    set: (value) => {
        if (props.form.waste_type === 'product') {
            props.form.product_id = value;
            return;
        }

        props.form.ingredient_id = value;
    },
});
</script>

<template>
    <div class="grid gap-4">
        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark">Selejt típusa</label>
            <Select
                v-model="form.waste_type"
                :options="[
                    { label: 'Alapanyag', value: 'ingredient' },
                    { label: 'Termék (BOM alapján)', value: 'product' },
                ]"
                option-label="label"
                option-value="value"
                class="w-full"
            />
            <p v-if="form.errors.waste_type" class="text-xs text-red-700">{{ form.errors.waste_type }}</p>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark">
                {{ form.waste_type === 'product' ? 'Termék' : 'Alapanyag' }}
            </label>
            <Select
                v-model="selectedWasteTargetId"
                :options="form.waste_type === 'product' ? productOptions : ingredientOptions"
                option-label="label"
                option-value="value"
                filter
                class="w-full"
            />
            <p v-if="form.waste_type === 'product' && form.errors.product_id" class="text-xs text-red-700">{{ form.errors.product_id }}</p>
            <p v-if="form.waste_type !== 'product' && form.errors.ingredient_id" class="text-xs text-red-700">{{ form.errors.ingredient_id }}</p>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark">Mennyiség</label>
            <InputText v-model="form.quantity" type="number" min="0.001" step="0.001" class="w-full" />
            <p v-if="form.errors.quantity" class="text-xs text-red-700">{{ form.errors.quantity }}</p>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark">Ok</label>
            <Select v-model="form.reason" :options="wasteReasons" option-label="label" option-value="value" class="w-full" />
            <p v-if="form.errors.reason" class="text-xs text-red-700">{{ form.errors.reason }}</p>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark">Dátum</label>
            <InputText v-model="form.occurred_at" type="date" class="w-full" />
            <p v-if="form.errors.occurred_at" class="text-xs text-red-700">{{ form.errors.occurred_at }}</p>
        </div>
    </div>
</template>
