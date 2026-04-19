<script setup>
import AppLogo from './AppLogo.vue';
</script>

<template>
    <header class="sticky top-0 z-40 border-b border-bakery-brown/10 bg-[#f9f3ea]/90 backdrop-blur">
        <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-4 py-3 sm:px-6">
            <AppLogo />
            <slot name="actions" />
        </div>
    </header>
</template>
