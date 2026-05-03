<script setup>
defineProps({
    eyebrow: {
        type: String,
        default: "",
    },
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: "",
    },
});
</script>

<template>
    <div class="ui-section max-w-3xl">
        <p
            v-if="eyebrow"
            class="text-[0.68rem] font-semibold uppercase tracking-[0.22em] text-bakery-gold/90"
        >
            {{ eyebrow }}
        </p>
        <h2
            class="font-heading text-[1.95rem] leading-tight text-bakery-dark sm:text-[2.35rem]"
        >
            {{ title }}
        </h2>
        <p
            v-if="description"
            class="max-w-2xl text-[0.98rem] leading-relaxed text-bakery-dark/78"
        >
            {{ description }}
        </p>
    </div>
</template>
