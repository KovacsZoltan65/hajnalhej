<script setup>
const props = defineProps({
    type: {
        type: String,
        default: null,
    },
    label: {
        type: String,
        default: "",
    },
});

const classes = {
    walking: "bg-stone-100 text-stone-800",
    bicycle: "bg-emerald-100 text-emerald-800",
    scooter: "bg-sky-100 text-sky-800",
    car: "bg-indigo-100 text-indigo-800",
    van: "bg-amber-100 text-amber-800",
};
</script>

<template>
    <span class="ui-badge" :class="classes[props.type] ?? 'ui-badge-muted'">
        {{ props.label || props.type || $t("common.not_specified") }}
    </span>
</template>
