<script setup>
import { computed } from "vue";

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
});

const state = computed(() => {
    const map = {
        pending: ["bg-amber-100 text-amber-800", "common.export_status_pending"],
        running: ["bg-blue-100 text-blue-800", "common.export_status_running"],
        completed: ["bg-emerald-100 text-emerald-800", "common.export_completed"],
        failed: ["bg-red-100 text-red-800", "common.export_failed"],
        expired: ["bg-stone-100 text-stone-700", "common.export_status_expired"],
    };

    return map[props.status] ?? ["bg-stone-100 text-stone-700", props.status];
});
</script>

<template>
    <span class="inline-flex items-center rounded px-2 py-1 text-xs font-medium" :class="state[0]">
        {{ $t(state[1]) }}
    </span>
</template>
