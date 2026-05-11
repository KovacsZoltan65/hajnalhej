<script setup>
import { trans } from "laravel-vue-i18n";

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
});

const labels = {
    pending: trans("order_status.pending"),
    confirmed: trans("order_status.confirmed"),
    in_preparation: trans("order_status.under_preparation"),
    ready_for_pickup: trans("order_status.ready_for_pickup"),
    completed: trans("order_status.completed"),
    cancelled: trans("order_status.deleted"),
};

const classes = {
    pending: "bg-amber-100 text-amber-800",
    confirmed: "bg-blue-100 text-blue-800",
    in_preparation: "bg-indigo-100 text-indigo-800",
    ready_for_pickup: "bg-emerald-100 text-emerald-800",
    completed: "bg-green-100 text-green-800",
    cancelled: "bg-rose-100 text-rose-800",
};
</script>

<template>
    <span class="ui-badge" :class="classes[props.status] ?? 'ui-badge-muted'">
        {{ labels[props.status] ?? props.status }}
    </span>
</template>
