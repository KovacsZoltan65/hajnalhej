<script setup>
const props = defineProps({
    state: {
        type: String,
        required: true,
    },
});

const labelMap = {
    synced: 'Szinkronizált',
    missing_in_db: 'Hiányzik az adatbázisból',
    orphan_db_only: 'Csak adatbázisban',
};

const classMap = {
    synced: 'bg-emerald-100 text-emerald-700',
    missing_in_db: 'bg-amber-100 text-amber-800',
    orphan_db_only: 'bg-rose-100 text-rose-700',
};
</script>

<template>
    <span
        class="ui-badge"
        :class="classMap[props.state] ?? 'bg-slate-100 text-slate-700'"
    >
        {{ labelMap[props.state] ?? props.state }}
    </span>
</template>

