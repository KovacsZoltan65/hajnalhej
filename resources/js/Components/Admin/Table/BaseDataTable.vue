<script setup>
import DataTable from "primevue/datatable";
import AdminBulkActionBar from "./AdminBulkActionBar.vue";
import AdminTableEmptyState from "./AdminTableEmptyState.vue";
import AdminTableSkeleton from "./AdminTableSkeleton.vue";

defineProps({
    value: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    selectedCount: { type: Number, default: 0 },
    emptyTitle: { type: String, required: true },
    emptyDescription: { type: String, required: true },
    emptyPrimaryLabel: { type: String, default: "" },
    emptySecondaryLabel: { type: String, default: "" },
});

defineEmits(["empty-primary", "empty-secondary", "clear-selection"]);
</script>

<template>
    <div>
        <AdminTableSkeleton v-if="loading" />
        <DataTable v-else :value="value" data-key="id" v-bind="$attrs">
            <template #empty>
                <AdminTableEmptyState
                    :title="emptyTitle"
                    :description="emptyDescription"
                    :primary-label="emptyPrimaryLabel"
                    :secondary-label="emptySecondaryLabel"
                    @primary="$emit('empty-primary')"
                    @secondary="$emit('empty-secondary')"
                />
            </template>
            <slot />
        </DataTable>
        <AdminBulkActionBar :selected-count="selectedCount" @clear="$emit('clear-selection')">
            <slot name="bulk-actions" />
        </AdminBulkActionBar>
    </div>
</template>
