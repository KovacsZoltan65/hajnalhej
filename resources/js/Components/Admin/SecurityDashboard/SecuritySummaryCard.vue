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
    tone: {
        type: String,
        default: 'low',
    },
});

const toneClass = {
    critical: 'border-red-300 bg-red-50 text-red-800',
    high: 'border-amber-300 bg-amber-50 text-amber-800',
    medium: 'border-orange-200 bg-orange-50 text-orange-800',
    low: 'border-emerald-200 bg-emerald-50 text-emerald-800',
    info: 'border-blue-200 bg-blue-50 text-blue-800',
};
</script>

<template>
    <article
        class="rounded-2xl border p-4 shadow-sm transition hover:-translate-y-0.5"
        :class="toneClass[tone] ?? toneClass.info"
    >
        <p class="text-xs font-semibold uppercase tracking-[0.14em]">{{ title }}</p>
        <p class="mt-3 font-heading text-3xl leading-none">{{ value }}</p>
    </article>
</template>

