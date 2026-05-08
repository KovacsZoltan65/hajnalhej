<script setup>
import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";

defineProps({
    form: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="grid gap-4 md:grid-cols-2">
        <!-- NÉV -->
        <div class="space-y-2 md:col-span-2">
            <label for="supplier-name" class="text-sm font-medium text-bakery-dark">{{ $t("common.name") }}</label>
            <InputText id="supplier-name" v-model="form.name" class="w-full" :invalid="Boolean(form.errors.name)" />
            <p v-if="form.errors.name" class="text-xs text-red-700">
                {{ form.errors.name }}
            </p>
        </div>

        <div class="space-y-2">
            <label for="supplier-email" class="text-sm font-medium text-bakery-dark">{{ $t("common.email") }}</label>
            <InputText id="supplier-email" v-model="form.email" class="w-full" />
            <p v-if="form.errors.email" class="text-xs text-red-700">
                {{ form.errors.email }}
            </p>
        </div>

        <div class="space-y-2">
            <label for="supplier-phone" class="text-sm font-medium text-bakery-dark">{{ $t("common.phone") }}</label>
            <InputText id="supplier-phone" v-model="form.phone" class="w-full" />
            <p v-if="form.errors.phone" class="text-xs text-red-700">
                {{ form.errors.phone }}
            </p>
        </div>

        <div class="space-y-2">
            <label for="supplier-tax-number" class="text-sm font-medium text-bakery-dark">{{
                $t("common.tax_number")
            }}</label>
            <InputText id="supplier-tax-number" v-model="form.tax_number" class="w-full" />
            <p v-if="form.errors.tax_number" class="text-xs text-red-700">
                {{ form.errors.tax_number }}
            </p>
        </div>

        <div class="space-y-2">
            <label for="supplier-lead-time" class="text-sm font-medium text-bakery-dark"
                >{{ $t("common.basic_lead_time") }} ({{ $t("common.day") }})</label
            >
            <InputText
                id="supplier-lead-time"
                v-model="form.lead_time_days"
                type="number"
                min="0"
                max="365"
                class="w-full"
            />
            <p v-if="form.errors.lead_time_days" class="text-xs text-red-700">
                {{ form.errors.lead_time_days }}
            </p>
        </div>

        <div class="space-y-2 md:col-span-2">
            <label for="supplier-notes" class="text-sm font-medium text-bakery-dark">{{ $t("common.notes") }}</label>
            <Textarea id="supplier-notes" v-model="form.notes" rows="4" class="w-full" auto-resize />
            <p v-if="form.errors.notes" class="text-xs text-red-700">
                {{ form.errors.notes }}
            </p>
        </div>
    </div>
</template>
