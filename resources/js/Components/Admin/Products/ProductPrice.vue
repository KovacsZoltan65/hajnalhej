<script setup>
defineProps({
    price: {
        type: Number,
        required: true,
    },
});

const formatPrice = (price) => Math.round(price).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
</script>

<template>
    <span class="font-medium text-bakery-dark">{{ formatPrice(price) }} Ft</span>
</template>
