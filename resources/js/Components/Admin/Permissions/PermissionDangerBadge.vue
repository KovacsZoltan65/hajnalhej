<script setup>
const props = defineProps({
    dangerous: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold"
        :class="props.dangerous ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'"
    >
        {{ props.dangerous ? 'Veszélyes' : 'Biztonságos' }}
    </span>
</template>
