<script setup>
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import ToggleSwitch from "primevue/toggleswitch";
import InlineSaveIndicator from "./InlineSaveIndicator.vue";

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    routeName: { type: String, required: true },
    routeParams: { type: [Array, Object, Number, String], required: true },
    field: { type: String, required: true },
    reloadOnly: { type: Array, default: () => [] },
});

const draft = ref(Boolean(props.modelValue));
const state = ref("idle");
const error = ref("");

watch(
    () => props.modelValue,
    (value) => {
        draft.value = Boolean(value);
    }
);

const save = () => {
    state.value = "saving";
    error.value = "";

    router.patch(
        route(props.routeName, props.routeParams),
        { field: props.field, value: draft.value },
        {
            preserveScroll: true,
            preserveState: true,
            only: props.reloadOnly,
            onSuccess: () => {
                state.value = "saved";
                window.setTimeout(() => (state.value = "idle"), 1200);
            },
            onError: (errors) => {
                draft.value = Boolean(props.modelValue);
                state.value = "error";
                error.value = errors.value ?? errors[props.field] ?? errors.field ?? "";
            },
        }
    );
};
</script>

<template>
    <div class="space-y-1">
        <ToggleSwitch v-model="draft" @change="save" @keyup.enter="save" @keyup.escape="draft = modelValue" />
        <div class="flex items-center gap-2">
            <InlineSaveIndicator :state="state" />
            <p v-if="error" class="text-xs font-medium text-red-700">{{ error }}</p>
        </div>
    </div>
</template>
