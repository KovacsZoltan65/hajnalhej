<script setup>
defineProps({
    preferred: {
        type: Boolean,
        required: true,
    },
    active: {
        type: Boolean,
        required: true,
    },
});
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
        :class="preferred && active ? 'bg-amber-100 text-amber-900' : 'bg-stone-100 text-stone-700'"
    >
        {{ preferred && active ? 'Preferált' : 'Normál' }}
    </span>
</template>
