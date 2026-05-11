<script setup>
defineProps({
    active: {
        type: Boolean,
        required: true,
    },
});
</script>

<template>
    <span class="ui-badge" :class="active ? 'bg-emerald-100 text-emerald-800' : 'ui-badge-muted'">
        {{ active ? $t("common.active") : $t("common.inactive") }}
    </span>
</template>
