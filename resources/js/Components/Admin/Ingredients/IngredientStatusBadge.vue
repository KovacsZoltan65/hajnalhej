<script setup>
defineProps({
    active: {
        type: Boolean,
        required: true,
    },
});
</script>

<template>
    <span
        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
        :class="active ? 'bg-green-100 text-green-800' : 'bg-stone-200 text-stone-700'"
    >
        {{ active ? 'Aktív' : 'Inaktív' }}
    </span>
</template>
