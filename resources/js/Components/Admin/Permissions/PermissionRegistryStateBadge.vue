<script setup>
const props = defineProps({
    state: {
        type: String,
        required: true,
    },
});

const labelMap = {
    synced: 'Synced',
    missing_in_db: 'Missing In DB',
    orphan_db_only: 'DB Only',
};

const classMap = {
    synced: 'bg-emerald-100 text-emerald-700',
    missing_in_db: 'bg-amber-100 text-amber-800',
    orphan_db_only: 'bg-rose-100 text-rose-700',
};
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold"
        :class="classMap[props.state] ?? 'bg-slate-100 text-slate-700'"
    >
        {{ labelMap[props.state] ?? props.state }}
    </span>
</template>
