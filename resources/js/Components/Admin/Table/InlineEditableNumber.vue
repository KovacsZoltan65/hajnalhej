<script setup>
import InlineEditableCell from "./InlineEditableCell.vue";

defineProps({
    modelValue: { type: [Number, String], default: 0 },
    routeName: { type: String, required: true },
    routeParams: { type: [Array, Object, Number, String], required: true },
    field: { type: String, required: true },
    suffix: { type: String, default: "" },
    reloadOnly: { type: Array, default: () => [] },
});
</script>

<template>
    <InlineEditableCell
        :model-value="modelValue"
        :route-name="routeName"
        :route-params="routeParams"
        :field="field"
        input-type="number"
        :reload-only="reloadOnly"
    >
        <template #display="{ value }">
            {{ value }}<span v-if="suffix" class="text-xs text-bakery-dark/60"> {{ suffix }}</span>
        </template>
    </InlineEditableCell>
</template>
