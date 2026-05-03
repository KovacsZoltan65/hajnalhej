<script setup>
import { trans } from "laravel-vue-i18n";

const props = defineProps({
    state: {
        type: String,
        required: true,
    },
});

const labelMap = {
    synced: "admin_permissions.registry_states.synced",
    missing_in_db: "admin_permissions.registry_states.missing_in_db",
    orphan_db_only: "admin_permissions.registry_states.orphan_db_only",
};

const classMap = {
    synced: "bg-emerald-100 text-emerald-700",
    missing_in_db: "bg-amber-100 text-amber-800",
    orphan_db_only: "bg-rose-100 text-rose-700",
};
</script>

<template>
    <span
        class="ui-badge"
        :class="classMap[props.state] ?? 'bg-slate-100 text-slate-700'"
    >
        {{ labelMap[props.state] ? trans(labelMap[props.state]) : props.state }}
    </span>
</template>

