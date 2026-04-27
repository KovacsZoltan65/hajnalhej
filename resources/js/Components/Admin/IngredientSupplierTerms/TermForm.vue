<script setup>
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import ToggleSwitch from 'primevue/toggleswitch';

defineProps({
    form: {
        type: Object,
        required: true,
    },
    ingredients: {
        type: Array,
        required: true,
    },
    suppliers: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div class="grid gap-4 md:grid-cols-2">
        <div class="space-y-2">
            <label for="term-ingredient" class="text-sm font-medium text-bakery-dark">Alapanyag</label>
            <Select
                id="term-ingredient"
                v-model="form.ingredient_id"
                :options="ingredients"
                option-label="name"
                option-value="id"
                filter
                class="w-full"
                :invalid="Boolean(form.errors.ingredient_id)"
            />
            <p v-if="form.errors.ingredient_id" class="text-xs text-red-700">{{ form.errors.ingredient_id }}</p>
        </div>

        <div class="space-y-2">
            <label for="term-supplier" class="text-sm font-medium text-bakery-dark">Beszállító</label>
            <Select
                id="term-supplier"
                v-model="form.supplier_id"
                :options="suppliers"
                option-label="name"
                option-value="id"
                filter
                class="w-full"
                :invalid="Boolean(form.errors.supplier_id)"
            />
            <p v-if="form.errors.supplier_id" class="text-xs text-red-700">{{ form.errors.supplier_id }}</p>
        </div>

        <div class="space-y-2">
            <label for="term-lead-time" class="text-sm font-medium text-bakery-dark">Lead time (nap)</label>
            <InputNumber id="term-lead-time" v-model="form.lead_time_days" class="w-full" input-class="w-full" :min="0" :max="365" />
            <p v-if="form.errors.lead_time_days" class="text-xs text-red-700">{{ form.errors.lead_time_days }}</p>
        </div>

        <div class="space-y-2">
            <label for="term-minimum-order" class="text-sm font-medium text-bakery-dark">Minimum rendelési mennyiség</label>
            <InputNumber id="term-minimum-order" v-model="form.minimum_order_quantity" class="w-full" input-class="w-full" :min="0" :min-fraction-digits="0" :max-fraction-digits="3" />
            <p v-if="form.errors.minimum_order_quantity" class="text-xs text-red-700">{{ form.errors.minimum_order_quantity }}</p>
        </div>

        <div class="space-y-2">
            <label for="term-pack-size" class="text-sm font-medium text-bakery-dark">Kiszerelés</label>
            <InputNumber id="term-pack-size" v-model="form.pack_size" class="w-full" input-class="w-full" :min="0" :min-fraction-digits="0" :max-fraction-digits="3" />
            <p v-if="form.errors.pack_size" class="text-xs text-red-700">{{ form.errors.pack_size }}</p>
        </div>

        <div class="space-y-2">
            <label for="term-unit-cost" class="text-sm font-medium text-bakery-dark">Egyedi egységár</label>
            <InputNumber id="term-unit-cost" v-model="form.unit_cost_override" class="w-full" input-class="w-full" :min="0" :min-fraction-digits="0" :max-fraction-digits="2" />
            <p v-if="form.errors.unit_cost_override" class="text-xs text-red-700">{{ form.errors.unit_cost_override }}</p>
        </div>

        <div class="flex min-h-16 items-center justify-between rounded-lg border border-bakery-brown/15 px-3">
            <div>
                <p class="text-sm font-medium text-bakery-dark">Aktív</p>
                <p class="text-xs text-bakery-dark/60">Listázásban és ajánlásokban használható.</p>
            </div>
            <ToggleSwitch v-model="form.active" aria-label="Aktív" />
        </div>

        <div class="flex min-h-16 items-center justify-between rounded-lg border border-bakery-brown/15 px-3">
            <div>
                <p class="text-sm font-medium text-bakery-dark">Preferált</p>
                <p class="text-xs text-bakery-dark/60">Egy alapanyaghoz csak egy aktív lehet.</p>
            </div>
            <ToggleSwitch v-model="form.preferred" :disabled="!form.active" aria-label="Preferált" />
        </div>

        <p v-if="form.errors.active" class="text-xs text-red-700 md:col-span-2">{{ form.errors.active }}</p>
        <p v-if="form.errors.preferred" class="text-xs text-red-700 md:col-span-2">{{ form.errors.preferred }}</p>

        <div class="space-y-2 md:col-span-2">
            <label for="term-meta" class="text-sm font-medium text-bakery-dark">Meta JSON</label>
            <Textarea id="term-meta" v-model="form.meta" rows="4" class="w-full font-mono text-sm" auto-resize placeholder='{"note":"..."}' />
            <p v-if="form.errors.meta" class="text-xs text-red-700">{{ form.errors.meta }}</p>
        </div>
    </div>
</template>
