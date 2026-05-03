<script setup>
import { trans } from "laravel-vue-i18n";

const props = defineProps({
    dangerous: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <span
        class="ui-badge"
        :class="props.dangerous ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'"
    >
        {{ props.dangerous ? trans("admin_permissions.risk.dangerous") : trans("admin_permissions.risk.safe") }}
    </span>
</template>
