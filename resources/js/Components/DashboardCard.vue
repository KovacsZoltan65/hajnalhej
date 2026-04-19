<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: String,
        required: true,
    },
    icon: {
        type: String,
        default: 'pi pi-circle-fill',
    },
});
</script>

<template>
    <article class="rounded-2xl border border-bakery-brown/10 bg-white/80 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-bakery-dark/70">{{ title }}</p>
            <i :class="[icon, 'text-bakery-gold']" />
        </div>
        <p class="mt-3 font-heading text-2xl text-bakery-dark">{{ value }}</p>
    </article>
</template>
