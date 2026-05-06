<script setup>
import Button from "primevue/button";

defineProps({
    icon: { type: String, default: "pi pi-inbox" },
    title: { type: String, required: true },
    description: { type: String, required: true },
    primaryLabel: { type: String, default: "" },
    secondaryLabel: { type: String, default: "" },
});

defineEmits(["primary", "secondary"]);
</script>

<template>
    <div class="rounded-lg border border-dashed border-bakery-brown/25 bg-[#fcf7ef] p-8 text-center">
        <i :class="icon" class="text-2xl text-bakery-brown" />
        <h3 class="mt-3 text-base font-semibold text-bakery-dark">{{ title }}</h3>
        <p class="mx-auto mt-1 max-w-md text-sm text-bakery-dark/65">{{ description }}</p>
        <div v-if="primaryLabel || secondaryLabel" class="mt-4 flex flex-wrap justify-center gap-2">
            <Button v-if="secondaryLabel" :label="secondaryLabel" outlined size="small" @click="$emit('secondary')" />
            <Button v-if="primaryLabel" :label="primaryLabel" size="small" @click="$emit('primary')" />
        </div>
    </div>
</template>
