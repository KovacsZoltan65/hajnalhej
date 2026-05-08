<script setup>
import BaseDatePicker from "@/Components/BaseDatePicker.vue";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Textarea from "primevue/textarea";

defineProps({
    form: {
        type: Object,
        required: true,
    },
    ingredientOptions: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div class="grid gap-4">
        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark">{{ $t("common.ingredient") }}</label>
            <Select
                v-model="form.ingredient_id"
                :options="ingredientOptions"
                option-label="label"
                option-value="value"
                filter
                class="w-full"
            />
            <p v-if="form.errors.ingredient_id" class="text-xs text-red-700">
                {{ form.errors.ingredient_id }}
            </p>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark">{{ $t("admin_ingredients.difference") }} (+/-)</label>
            <InputText v-model="form.difference" type="number" step="0.001" class="w-full" />
            <p v-if="form.errors.difference" class="text-xs text-red-700">
                {{ form.errors.difference }}
            </p>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark"
                >{{ $t("common.unit_cost") }} ({{ $t("common.optional") }})</label
            >
            <InputText v-model="form.unit_cost" type="number" min="0" step="1" class="w-full" />
            <p v-if="form.errors.unit_cost" class="text-xs text-red-700">
                {{ form.errors.unit_cost }}
            </p>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark">{{ $t("common.date") }}</label>
            <BaseDatePicker v-model="form.occurred_at" class="w-full" />
            <p v-if="form.errors.occurred_at" class="text-xs text-red-700">
                {{ form.errors.occurred_at }}
            </p>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark">{{ $t("common.notes") }}</label>
            <Textarea v-model="form.notes" rows="3" class="w-full" auto-resize />
            <p v-if="form.errors.notes" class="text-xs text-red-700">
                {{ form.errors.notes }}
            </p>
        </div>
    </div>
</template>
