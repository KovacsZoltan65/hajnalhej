<script setup>
const props = defineProps({
    permission: {
        type: String,
        required: true,
    },
    dangerous: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
        :class="props.dangerous ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'"
    >
        {{ props.permission }}
    </span>
</template>
