<script setup>
import { computed, nextTick, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import InputText from "primevue/inputtext";
import InlineSaveIndicator from "./InlineSaveIndicator.vue";

const props = defineProps({
    modelValue: { type: [String, Number, Boolean, null], default: null },
    routeName: { type: String, required: true },
    routeParams: { type: [Array, Object, Number, String], required: true },
    field: { type: String, required: true },
    inputType: { type: String, default: "text" },
    displayValue: { type: [String, Number, Boolean, null], default: null },
    reloadOnly: { type: Array, default: () => [] },
});

const emit = defineEmits(["saved"]);

const editing = ref(false);
const draft = ref(props.modelValue);
const input = ref(null);
const state = ref("idle");
const error = ref("");

const renderedValue = computed(() => props.displayValue ?? props.modelValue ?? "-");
const changed = computed(() => String(draft.value ?? "") !== String(props.modelValue ?? ""));

watch(
    () => props.modelValue,
    (value) => {
        if (!editing.value) {
            draft.value = value;
        }
    }
);

const start = async () => {
    editing.value = true;
    draft.value = props.modelValue;
    error.value = "";
    await nextTick();
    input.value?.$el?.focus?.();
};

const cancel = () => {
    draft.value = props.modelValue;
    editing.value = false;
    error.value = "";
    state.value = "idle";
};

const save = () => {
    if (!changed.value) {
        editing.value = false;
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
                editing.value = false;
                state.value = "saved";
                emit("saved", draft.value);
                window.setTimeout(() => {
                    if (state.value === "saved") {
                        state.value = "idle";
                    }
                }, 1200);
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
    <div class="min-w-28 space-y-1">
        <button
            v-if="!editing"
            type="button"
            class="rounded-md px-2 py-1 text-left font-medium text-bakery-dark hover:bg-bakery-brown/10"
            @click="start"
        >
            <slot name="display" :value="modelValue">
                {{ renderedValue }}
            </slot>
        </button>
        <InputText
            v-else
            ref="input"
            v-model="draft"
            :type="inputType"
            class="w-full"
            size="small"
            @keyup.enter="save"
            @keyup.escape="cancel"
            @blur="save"
        />
        <div class="flex items-center gap-2">
            <InlineSaveIndicator :state="state" />
            <p v-if="error" class="text-xs font-medium text-red-700">{{ error }}</p>
        </div>
    </div>
</template>
