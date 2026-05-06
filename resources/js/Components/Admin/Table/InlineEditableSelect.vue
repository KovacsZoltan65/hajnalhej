<script setup>
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import Select from "primevue/select";
import InlineSaveIndicator from "./InlineSaveIndicator.vue";

const props = defineProps({
    modelValue: { type: [String, Number, Boolean, null], default: null },
    options: { type: Array, required: true },
    optionLabel: { type: String, default: "label" },
    optionValue: { type: String, default: "value" },
    routeName: { type: String, required: true },
    routeParams: { type: [Array, Object, Number, String], required: true },
    field: { type: String, required: true },
    reloadOnly: { type: Array, default: () => [] },
});

const draft = ref(props.modelValue);
const state = ref("idle");
const error = ref("");

watch(
    () => props.modelValue,
    (value) => {
        draft.value = value;
    }
);

const save = () => {
    if (String(draft.value ?? "") === String(props.modelValue ?? "")) {
        return;
    }

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
                state.value = "error";
                error.value = errors.value ?? errors[props.field] ?? errors.field ?? "";
            },
        }
    );
};
</script>

<template>
    <div class="min-w-36 space-y-1">
        <Select
            v-model="draft"
            :options="options"
            :option-label="optionLabel"
            :option-value="optionValue"
            size="small"
            class="w-full"
            @change="save"
            @keyup.enter="save"
            @keyup.escape="draft = modelValue"
        />
        <div class="flex items-center gap-2">
            <InlineSaveIndicator :state="state" />
            <p v-if="error" class="text-xs font-medium text-red-700">{{ error }}</p>
        </div>
    </div>
</template>
