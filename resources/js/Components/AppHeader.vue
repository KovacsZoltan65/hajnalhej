<script setup>
import AppLogo from './AppLogo.vue';

const props = defineProps({
    containerClass: {
        type: String,
        default: 'max-w-6xl',
    },
});
</script>

<template>
    <header class="sticky top-0 z-40 border-b border-bakery-brown/10 bg-[#f9f3ea]/92 backdrop-blur-md">
        <div :class="['mx-auto flex w-full items-center justify-between px-4 py-3.5 sm:px-6', props.containerClass]">
            <AppLogo />
            <slot name="actions" />
        </div>
    </header>
</template>
