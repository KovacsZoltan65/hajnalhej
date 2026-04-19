<script setup>
defineProps({
    eyebrow: {
        type: String,
        default: '',
    },
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="max-w-2xl space-y-3">
        <p v-if="eyebrow" class="text-xs font-semibold uppercase tracking-[0.2em] text-bakery-gold">{{ eyebrow }}</p>
        <h2 class="font-heading text-3xl leading-tight text-bakery-dark sm:text-4xl">{{ title }}</h2>
        <p v-if="description" class="text-base text-bakery-dark/80">{{ description }}</p>
    </div>
</template>
