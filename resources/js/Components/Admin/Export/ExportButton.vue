<script setup>
import { ref } from "vue";
import Button from "primevue/button";
import ExportDialog from "./ExportDialog.vue";
import { useExport } from "./useExport.js";

const props = defineProps({
    type: {
        type: String,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const dialogVisible = ref(false);
const { loading, startExport } = useExport();

const confirm = (payload) => {
    startExport(payload, {
        onSuccess: () => {
            dialogVisible.value = false;
        },
    });
};
</script>

<template>
    <Button
        icon="pi pi-download"
        :label="$t('common.export')"
        outlined
        :disabled="disabled || loading"
        :loading="loading"
        @click="dialogVisible = true"
    />
    <ExportDialog
        v-model:visible="dialogVisible"
        :type="type"
        :filters="filters"
        :loading="loading"
        @confirm="confirm"
    />
</template>
