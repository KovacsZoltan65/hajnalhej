<script setup>
const props = defineProps({
    status: { type: [String, Boolean], required: true },
});

const classes = {
    active: "bg-emerald-100 text-emerald-800",
    inactive: "bg-stone-100 text-stone-700",
    draft: "bg-amber-100 text-amber-800",
    published: "bg-emerald-100 text-emerald-800",
    archived: "bg-stone-100 text-stone-700",
    preorder: "bg-sky-100 text-sky-800",
    in_stock: "bg-emerald-100 text-emerald-800",
    out_of_stock: "bg-red-100 text-red-800",
};
</script>

<template>
    <span
        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
        :class="classes[status === true ? 'active' : status === false ? 'inactive' : status] ?? classes.inactive"
    >
        <slot>
            {{ typeof status === "boolean" ? (status ? $t("common.active") : $t("common.inactive")) : status }}
        </slot>
    </span>
</template>
