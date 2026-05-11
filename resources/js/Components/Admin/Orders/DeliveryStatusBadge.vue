<script setup>
import { computed } from "vue";
import Tag from "primevue/tag";
import { trans } from "laravel-vue-i18n";

const props = defineProps({
    status: {
        type: String,
        default: "pending",
    },
    label: {
        type: String,
        default: "",
    },
});

const normalizedStatus = computed(() => props.status || "pending");

const severity = computed(
    () =>
        ({
            pending: "secondary",
            assigned: "info",
            out_for_delivery: "warn",
            delivered: "success",
            failed: "danger",
            cancelled: "secondary",
        })[normalizedStatus.value] ?? "secondary"
);

const displayLabel = computed(() => props.label || trans(`delivery.statuses.${normalizedStatus.value}`));
</script>

<template>
    <Tag :value="displayLabel" :severity="severity" rounded />
</template>
