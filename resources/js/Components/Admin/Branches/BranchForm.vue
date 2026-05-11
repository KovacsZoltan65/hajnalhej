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
    typeOptions: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div class="grid gap-4 md:grid-cols-2">
        <div class="space-y-2">
            <label for="branch-name" class="text-sm font-medium text-bakery-dark">{{ $t("common.name") }}</label>
            <InputText id="branch-name" v-model="form.name" class="w-full" :invalid="Boolean(form.errors.name)" />
            <p v-if="form.errors.name" class="text-xs text-red-700">{{ form.errors.name }}</p>
        </div>

        <div class="space-y-2">
            <label for="branch-code" class="text-sm font-medium text-bakery-dark">{{
                $t("admin_branches.code")
            }}</label>
            <InputText id="branch-code" v-model="form.code" class="w-full" :invalid="Boolean(form.errors.code)" />
            <p v-if="form.errors.code" class="text-xs text-red-700">{{ form.errors.code }}</p>
        </div>

        <div class="space-y-2">
            <label for="branch-type" class="text-sm font-medium text-bakery-dark">{{
                $t("admin_branches.branch_type")
            }}</label>
            <Select
                id="branch-type"
                v-model="form.type"
                :options="typeOptions"
                option-label="label"
                option-value="value"
                class="w-full"
                :invalid="Boolean(form.errors.type)"
            />
            <p v-if="form.errors.type" class="text-xs text-red-700">{{ form.errors.type }}</p>
        </div>

        <div class="flex items-center gap-2 pt-7">
            <ToggleSwitch id="branch-active" v-model="form.active" />
            <label for="branch-active" class="text-sm text-bakery-dark/80">{{ $t("common.active") }}</label>
            <p v-if="form.errors.active" class="text-xs text-red-700">{{ form.errors.active }}</p>
        </div>

        <div class="space-y-2">
            <label for="branch-email" class="text-sm font-medium text-bakery-dark">{{ $t("common.email") }}</label>
            <InputText id="branch-email" v-model="form.email" class="w-full" :invalid="Boolean(form.errors.email)" />
            <p v-if="form.errors.email" class="text-xs text-red-700">{{ form.errors.email }}</p>
        </div>

        <div class="space-y-2">
            <label for="branch-phone" class="text-sm font-medium text-bakery-dark">{{ $t("common.phone") }}</label>
            <InputText id="branch-phone" v-model="form.phone" class="w-full" :invalid="Boolean(form.errors.phone)" />
            <p v-if="form.errors.phone" class="text-xs text-red-700">{{ form.errors.phone }}</p>
        </div>

        <div class="space-y-2 md:col-span-2">
            <label for="branch-address" class="text-sm font-medium text-bakery-dark">{{ $t("common.address") }}</label>
            <Textarea
                id="branch-address"
                v-model="form.address"
                rows="3"
                class="w-full"
                auto-resize
                :invalid="Boolean(form.errors.address)"
            />
            <p v-if="form.errors.address" class="text-xs text-red-700">{{ form.errors.address }}</p>
        </div>

        <div class="space-y-2 md:col-span-2">
            <label for="branch-meta" class="text-sm font-medium text-bakery-dark">{{
                $t("admin_branches.meta")
            }}</label>
            <Textarea
                id="branch-meta"
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
