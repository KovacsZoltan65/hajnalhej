<script setup>
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Textarea from "primevue/textarea";
import ToggleSwitch from "primevue/toggleswitch";

defineProps({
    form: {
        type: Object,
        required: true,
    },
    vehicleTypeOptions: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div class="grid gap-4 md:grid-cols-2">
        <div class="space-y-2 md:col-span-2">
            <label for="courier-name" class="text-sm font-medium text-bakery-dark">{{ $t("common.name") }}</label>
            <InputText id="courier-name" v-model="form.name" class="w-full" :invalid="Boolean(form.errors.name)" />
            <p v-if="form.errors.name" class="text-xs text-red-700">{{ form.errors.name }}</p>
        </div>

        <div class="space-y-2">
            <label for="courier-phone" class="text-sm font-medium text-bakery-dark">{{ $t("common.phone") }}</label>
            <InputText id="courier-phone" v-model="form.phone" class="w-full" :invalid="Boolean(form.errors.phone)" />
            <p v-if="form.errors.phone" class="text-xs text-red-700">{{ form.errors.phone }}</p>
        </div>

        <div class="space-y-2">
            <label for="courier-email" class="text-sm font-medium text-bakery-dark">{{ $t("common.email") }}</label>
            <InputText id="courier-email" v-model="form.email" class="w-full" :invalid="Boolean(form.errors.email)" />
            <p v-if="form.errors.email" class="text-xs text-red-700">{{ form.errors.email }}</p>
        </div>

        <div class="space-y-2">
            <label for="courier-vehicle-type" class="text-sm font-medium text-bakery-dark">
                {{ $t("delivery.vehicle_type") }}
            </label>
            <Select
                id="courier-vehicle-type"
                v-model="form.vehicle_type"
                :options="vehicleTypeOptions"
                option-label="label"
                option-value="value"
                show-clear
                class="w-full"
                :invalid="Boolean(form.errors.vehicle_type)"
            />
            <p v-if="form.errors.vehicle_type" class="text-xs text-red-700">{{ form.errors.vehicle_type }}</p>
        </div>

        <div class="flex items-center gap-2 pt-7">
            <ToggleSwitch id="courier-active" v-model="form.active" />
            <label for="courier-active" class="text-sm text-bakery-dark/80">{{ $t("common.active") }}</label>
            <p v-if="form.errors.active" class="text-xs text-red-700">{{ form.errors.active }}</p>
        </div>

        <div class="space-y-2 md:col-span-2">
            <label for="courier-notes" class="text-sm font-medium text-bakery-dark">{{ $t("common.notes") }}</label>
            <Textarea
                id="courier-notes"
                v-model="form.notes"
                rows="3"
                class="w-full"
                auto-resize
                :invalid="Boolean(form.errors.notes)"
            />
            <p v-if="form.errors.notes" class="text-xs text-red-700">{{ form.errors.notes }}</p>
        </div>

        <div class="space-y-2 md:col-span-2">
            <label for="courier-meta" class="text-sm font-medium text-bakery-dark">{{
                $t("admin_couriers.meta")
            }}</label>
            <Textarea
                id="courier-meta"
                v-model="form.meta_json"
                rows="4"
                class="w-full font-mono text-sm"
                auto-resize
                :invalid="Boolean(form.errors.meta)"
            />
            <p v-if="form.errors.meta" class="text-xs text-red-700">{{ form.errors.meta }}</p>
        </div>
    </div>
</template>
