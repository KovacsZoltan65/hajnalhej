<script setup>
const props = defineProps({
    role: {
        type: String,
        required: true,
    },
    system: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <span
        class="ui-badge"
        :class="props.system ? 'bg-bakery-gold/20 text-bakery-brown' : 'ui-badge-muted'"
    >
        {{ props.role }}
        <span v-if="props.system" class="ml-1 rounded-full bg-bakery-brown/10 px-1.5 py-0.5 text-[10px] uppercase tracking-[0.08em]">
            rendszer
        </span>
    </span>
</template>
