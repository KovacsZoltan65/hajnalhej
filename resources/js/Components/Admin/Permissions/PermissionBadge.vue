<script setup>
const props = defineProps({
    name: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <span
        class="inline-flex max-w-full items-center rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700 break-all"
    >
        {{ props.name }}
    </span>
</template>
