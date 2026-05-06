<script setup>
import Button from "primevue/button";

defineProps({
    canGoBack: { type: Boolean, default: false },
    canGoNext: { type: Boolean, default: true },
    isLast: { type: Boolean, default: false },
    saving: { type: Boolean, default: false },
});

defineEmits(["back", "next", "submit"]);
</script>

<template>
    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-bakery-brown/10 pt-4">
        <Button
            :label="$t('admin.products.flow.actions.previous')"
            icon="pi pi-arrow-left"
            outlined
            :disabled="!canGoBack || saving"
            @click="$emit('back')"
        />
        <Button
            v-if="!isLast"
            :label="$t('admin.products.flow.actions.next')"
            icon="pi pi-arrow-right"
            icon-pos="right"
            :disabled="!canGoNext || saving"
            @click="$emit('next')"
        />
        <Button
            v-else
            :label="$t('admin.products.flow.actions.create_production_plan')"
            icon="pi pi-check"
            :loading="saving"
            :disabled="!canGoNext"
            @click="$emit('submit')"
        />
    </div>
</template>
