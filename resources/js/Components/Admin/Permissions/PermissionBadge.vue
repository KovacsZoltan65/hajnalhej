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
        class="ui-badge ui-badge-muted max-w-full break-all"
    >
        {{ props.name }}
    </span>
</template>
