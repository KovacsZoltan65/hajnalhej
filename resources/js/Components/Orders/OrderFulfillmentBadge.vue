<script setup>
const props = defineProps({
    method: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        default: "",
    },
});

const labels = {
    pickup: "Átvétel",
    delivery: "Kiszállítás",
};

const classes = {
    pickup: "bg-emerald-100 text-emerald-800",
    delivery: "bg-sky-100 text-sky-800",
};
</script>

<template>
    <span class="ui-badge" :class="classes[props.method] ?? 'ui-badge-muted'">
        {{ props.label || labels[props.method] || props.method }}
    </span>
</template>
