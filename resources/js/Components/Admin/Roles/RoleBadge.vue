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
        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
        :class="props.system ? 'bg-bakery-gold/20 text-bakery-brown' : 'bg-slate-100 text-slate-700'"
    >
        {{ props.role }}
        <span v-if="props.system" class="ml-1 rounded-full bg-bakery-brown/10 px-1.5 py-0.5 text-[10px] uppercase tracking-[0.08em]">
            system
        </span>
    </span>
</template>
