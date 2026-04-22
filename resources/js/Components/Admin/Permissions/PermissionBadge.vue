<script setup>
const props = defineProps({
    name: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">
        {{ props.name }}
    </span>
</template>
