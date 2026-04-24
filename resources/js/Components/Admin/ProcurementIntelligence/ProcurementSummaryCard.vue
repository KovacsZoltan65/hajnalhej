<script setup>
defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    hint: {
        type: String,
        default: '',
    },
    icon: {
        type: String,
        default: 'pi pi-chart-line',
    },
});
</script>

<template>
    <article class="ui-card p-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-bakery-dark/60">{{ label }}</p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">{{ value }}</p>
            </div>
            <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-bakery-brown/10 text-bakery-brown">
                <i :class="icon" />
            </span>
        </div>
        <p v-if="hint" class="mt-3 text-sm text-bakery-dark/65">{{ hint }}</p>
    </article>
</template>
