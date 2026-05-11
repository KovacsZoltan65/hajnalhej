<script setup>
const props = defineProps({
    type: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        default: "",
    },
});

const classes = {
    bakery: "bg-amber-100 text-amber-900",
    shop: "bg-blue-100 text-blue-800",
    pickup_point: "bg-emerald-100 text-emerald-800",
    warehouse: "bg-slate-200 text-slate-800",
};
</script>

<template>
    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="classes[type] ?? classes.shop">
        {{ label || $t(`admin_branches.types.${props.type}`) }}
    </span>
</template>
