<script setup>
defineProps({
    status: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <span class="ui-badge" :class="status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'ui-badge-muted'">
        {{ status === "active" ? $t("common.active") : $t("common.inactive") }}
    </span>
</template>
