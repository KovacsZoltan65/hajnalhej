<script setup>
defineProps({
    items: { type: Array, default: () => [] },
});
</script>

<template>
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div v-for="item in items" :key="item.label" class="rounded-lg border border-bakery-brown/10 bg-white/75 p-3">
            <p class="text-xs font-medium uppercase text-bakery-brown/70">
                {{ item.label }}
            </p>
            <p class="mt-1 text-xl font-semibold text-bakery-dark">{{ item.value }}</p>
        </div>
    </div>
</template>
