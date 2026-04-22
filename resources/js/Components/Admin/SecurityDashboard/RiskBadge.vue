<script setup>
import Tag from 'primevue/tag';

const props = defineProps({
    level: {
        type: String,
        default: 'low',
    },
});

const severityMap = {
    critical: 'danger',
    high: 'warn',
    medium: 'warning',
    low: 'info',
    info: 'secondary',
};
</script>

<template>
    <Tag
        :value="level.toUpperCase()"
        :severity="severityMap[level] ?? 'secondary'"
        rounded
        class="text-[11px] font-semibold tracking-[0.06em] !px-2.5 !py-1"
    />
</template>
