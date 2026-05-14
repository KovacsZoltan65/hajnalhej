<script setup>
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Textarea from "primevue/textarea";

defineProps({
    form: {
        type: Object,
        required: true,
    },
    statusOptions: {
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

        <div class="space-y-2 md:col-span-2">
            <label for="courier-status" class="text-sm font-medium text-bakery-dark">{{ $t("common.status") }}</label>
            <Select
                id="courier-status"
                v-model="form.status"
                :options="statusOptions"
                option-label="label"
                option-value="value"
                class="w-full"
                :invalid="Boolean(form.errors.status)"
            />
            <p v-if="form.errors.status" class="text-xs text-red-700">{{ form.errors.status }}</p>
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
    </div>
</template>
