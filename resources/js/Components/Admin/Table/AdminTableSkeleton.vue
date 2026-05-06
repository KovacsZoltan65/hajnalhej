<script setup>
import Skeleton from "primevue/skeleton";

defineProps({
    rows: { type: Number, default: 6 },
    columns: { type: Number, default: 5 },
});
</script>

<template>
    <div class="space-y-2" data-testid="admin-table-skeleton">
        <div
            v-for="row in rows"
            :key="row"
            class="grid gap-3 rounded-lg border border-bakery-brown/10 bg-white p-3"
            :style="{ gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))` }"
        >
            <Skeleton v-for="column in columns" :key="column" height="1.5rem" />
        </div>
    </div>
</template>
