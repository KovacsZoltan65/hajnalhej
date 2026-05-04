<script setup>
import { computed } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    suppliers: {
        type: Array,
        required: true,
    },
    ingredientOptions: {
        type: Array,
        required: true,
    },
});

const unitOptions = [
    { label: 'g', value: 'g' },
    { label: 'kg', value: 'kg' },
    { label: 'ml', value: 'ml' },
    { label: 'l', value: 'l' },
    { label: 'db', value: 'db' },
];

const supplierOptions = computed(() => [{ id: null, name: 'Nincs megadva' }, ...props.suppliers]);

const newItem = () => ({ ingredient_id: null, quantity: 1, unit: 'db', unit_cost: 0 });

const addItem = () => {
    props.form.items.push(newItem());
};

const removeItem = (index) => {
    if (props.form.items.length <= 1) {
        return;
    }

    props.form.items.splice(index, 1);
};

const onIngredientChange = (index) => {
    const selected = props.ingredientOptions.find((option) => option.value === props.form.items[index].ingredient_id);
    if (!selected) {
        return;
    }

    props.form.items[index].unit = selected.unit;
};

const itemError = (index, field) => props.form.errors[`items.${index}.${field}`];

const total = computed(() =>
    props.form.items.reduce(
        (sum, item) => sum + Number(item.quantity || 0) * Number(item.unit_cost || 0),
        0,
    ),
);

const formatMoney = (value) =>
    new Intl.NumberFormat('hu-HU', {
        maximumFractionDigits: 0,
    }).format(Number(value || 0));
</script>

<template>
    <div class="space-y-4">
        <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
                <label for="purchase-supplier" class="text-sm font-medium text-bakery-dark">Beszállító</label>
                <Select
                    id="purchase-supplier"
                    v-model="form.supplier_id"
                    :options="supplierOptions"
                    option-label="name"
                    option-value="id"
                    class="w-full"
                />
                <p v-if="form.errors.supplier_id" class="text-xs text-red-700">{{ form.errors.supplier_id }}</p>
            </div>

            <div class="space-y-2">
                <label for="purchase-date" class="text-sm font-medium text-bakery-dark">Dátum</label>
                <InputText id="purchase-date" v-model="form.purchase_date" type="date" class="w-full" />
                <p v-if="form.errors.purchase_date" class="text-xs text-red-700">{{ form.errors.purchase_date }}</p>
            </div>

            <div class="space-y-2 md:col-span-2">
                <label for="purchase-reference" class="text-sm font-medium text-bakery-dark">Referencia szám</label>
                <InputText id="purchase-reference" v-model="form.reference_number" class="w-full" />
                <p v-if="form.errors.reference_number" class="text-xs text-red-700">{{ form.errors.reference_number }}</p>
            </div>

            <div class="space-y-2 md:col-span-2">
                <label for="purchase-notes" class="text-sm font-medium text-bakery-dark">Megjegyzés</label>
                <Textarea id="purchase-notes" v-model="form.notes" rows="3" class="w-full" auto-resize />
                <p v-if="form.errors.notes" class="text-xs text-red-700">{{ form.errors.notes }}</p>
            </div>
        </div>

        <div class="space-y-3 rounded-xl border border-bakery-brown/15 bg-[#fcf8f2] p-3">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">Tételek</h3>
                <Button label="Tétel hozzáadása" icon="pi pi-plus" outlined class="min-h-11!" @click="addItem" />
            </div>

            <p v-if="form.errors.items" class="text-xs text-red-700">{{ form.errors.items }}</p>

            <div
                v-for="(item, index) in form.items"
                :key="index"
                class="grid gap-3 rounded-lg border border-bakery-brown/10 bg-white p-3 md:grid-cols-12"
            >
                <div class="space-y-1 md:col-span-4">
                    <label class="text-xs font-medium text-bakery-dark/80">Alapanyag</label>
                    <Select
                        v-model="item.ingredient_id"
                        :options="ingredientOptions"
                        option-label="label"
                        option-value="value"
                        filter
                        class="w-full"
                        @update:model-value="onIngredientChange(index)"
                    />
                    <p v-if="itemError(index, 'ingredient_id')" class="text-xs text-red-700">{{ itemError(index, 'ingredient_id') }}</p>
                </div>

                <div class="space-y-1 md:col-span-2">
                    <label class="text-xs font-medium text-bakery-dark/80">Mennyiség</label>
                    <InputText v-model="item.quantity" type="number" min="0.001" step="0.001" class="w-full" />
                    <p v-if="itemError(index, 'quantity')" class="text-xs text-red-700">{{ itemError(index, 'quantity') }}</p>
                </div>

                <div class="space-y-1 md:col-span-2">
                    <label class="text-xs font-medium text-bakery-dark/80">Egység</label>
                    <Select v-model="item.unit" :options="unitOptions" option-label="label" option-value="value" class="w-full" />
                    <p v-if="itemError(index, 'unit')" class="text-xs text-red-700">{{ itemError(index, 'unit') }}</p>
                </div>

                <div class="space-y-1 md:col-span-2">
                    <label class="text-xs font-medium text-bakery-dark/80">Egységár (Ft)</label>
                    <InputText v-model="item.unit_cost" type="number" min="0" step="0.0001" class="w-full" />
                    <p v-if="itemError(index, 'unit_cost')" class="text-xs text-red-700">{{ itemError(index, 'unit_cost') }}</p>
                </div>

                <div class="space-y-1 md:col-span-2">
                    <label class="text-xs font-medium text-bakery-dark/80">Sor összesen</label>
                    <div class="flex min-h-11 items-center rounded-md border border-bakery-brown/15 px-3 text-sm font-medium text-bakery-dark">
                        {{ formatMoney(Number(item.quantity || 0) * Number(item.unit_cost || 0)) }} Ft
                    </div>
                </div>

                <div class="md:col-span-12 flex justify-end">
                    <Button
                        label="Sor törlése"
                        icon="pi pi-trash"
                        severity="danger"
                        text
                        class="min-h-11!"
                        :disabled="form.items.length <= 1"
                        @click="removeItem(index)"
                    />
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between border-t border-bakery-brown/10 pt-3">
            <p class="text-sm text-bakery-dark/80">Összesen</p>
            <p class="text-base font-semibold text-bakery-dark">{{ formatMoney(total) }} Ft</p>
        </div>
    </div>
</template>
