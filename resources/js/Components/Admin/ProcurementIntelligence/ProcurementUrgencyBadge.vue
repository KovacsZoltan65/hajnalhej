<script setup>
import { trans } from 'laravel-vue-i18n';

const props = defineProps({
    value: {
        type: String,
        required: true,
    },
});

const classes = {
    critical: 'bg-red-100 text-red-800 ring-red-200',
    high: 'bg-amber-100 text-amber-800 ring-amber-200',
    medium: 'bg-yellow-100 text-yellow-800 ring-yellow-200',
    low: 'bg-emerald-100 text-emerald-800 ring-emerald-200',
};
</script>

<template>
    <span
        class="inline-flex min-h-7 items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1"
        :class="classes[props.value] ?? classes.low"
    >
        {{ trans(`admin_procurement_intelligence.urgencies.${props.value}`) }}
    </span>
</template>
