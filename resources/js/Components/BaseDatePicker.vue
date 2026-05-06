<script setup>
import DatePicker from "primevue/datepicker";

defineOptions({ name: "BaseDatePicker", inheritAttrs: false });

defineProps({
    modelValue: {
        type: [String, Date, Number, Object],
        default: null,
    },
    dateFormat: {
        type: String,
        default: "yy-mm-dd",
    },
    updateModelType: {
        type: String,
        default: "string",
    },
    showIcon: {
        type: Boolean,
        default: true,
    },
    appendTo: {
        type: String,
        default: "body",
    },
});

const emit = defineEmits(["update:modelValue", "date-select"]);
</script>

<template>
    <DatePicker
        :model-value="modelValue"
        :date-format="dateFormat"
        :update-model-type="updateModelType"
        :show-icon="showIcon"
        :append-to="appendTo"
        v-bind="$attrs"
        @update:model-value="emit('update:modelValue', $event)"
        @date-select="emit('date-select', $event)"
    />
</template>
