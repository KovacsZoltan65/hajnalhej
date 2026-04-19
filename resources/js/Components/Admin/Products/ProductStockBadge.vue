<script setup>
const props = defineProps({
    status: {
        type: String,
        required: true,
    },
});

const map = {
    in_stock: { label: 'Raktaron', className: 'bg-green-100 text-green-800' },
    preorder: { label: 'Elojegyzes', className: 'bg-amber-100 text-amber-800' },
    out_of_stock: { label: 'Elfogyott', className: 'bg-rose-100 text-rose-700' },
};

const config = map[props.status] ?? map.in_stock;
</script>

<template>
    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="config.className">{{ config.label }}</span>
</template>
