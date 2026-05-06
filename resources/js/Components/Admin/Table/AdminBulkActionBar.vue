<script setup>
import Button from "primevue/button";

defineProps({
    selectedCount: { type: Number, default: 0 },
});

defineEmits(["clear"]);
</script>

<template>
    <div
        v-if="selectedCount > 0"
        class="sticky bottom-3 z-20 mt-3 flex items-center justify-between rounded-lg border border-bakery-brown/20 bg-bakery-dark px-4 py-3 text-white shadow-lg"
        data-testid="admin-bulk-action-bar"
    >
        <span class="text-sm font-semibold">
            {{ $t("admin.common.bulk.selected", { count: selectedCount }) }}
        </span>
        <div class="flex items-center gap-2">
            <slot />
            <Button :label="$t('common.cancel')" size="small" text severity="secondary" @click="$emit('clear')" />
        </div>
    </div>
</template>
