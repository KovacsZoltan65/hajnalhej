<script setup>
defineProps({
    state: {
        type: String,
        default: "idle",
    },
});
</script>

<template>
    <span
        v-if="state !== 'idle'"
        class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold"
        :class="{
            'bg-amber-100 text-amber-800': state === 'saving',
            'bg-emerald-100 text-emerald-800': state === 'saved',
            'bg-red-100 text-red-800': state === 'error',
        }"
    >
        {{
            state === "saving"
                ? $t("admin.common.inline_edit.saving")
                : state === "saved"
                  ? $t("admin.common.inline_edit.saved")
                  : $t("admin.common.inline_edit.failed")
        }}
    </span>
</template>
