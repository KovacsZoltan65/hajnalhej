<script setup>
import Button from "primevue/button";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import ExportStatusBadge from "./ExportStatusBadge.vue";
import { useExport } from "./useExport.js";

defineProps({
    exports: {
        type: Object,
        required: true,
    },
});

const { downloadUrl } = useExport();

const canDownload = (exportJob) => exportJob.status === "completed" && !exportJob.is_expired;
</script>

<template>
    <DataTable :value="exports.data" data-key="id" responsive-layout="scroll">
        <template #empty>
            <div class="p-6 text-center text-sm text-bakery-dark/65">{{ $t("common.no_exports") }}</div>
        </template>
        <Column field="type" :header="$t('common.export_type')" />
        <Column field="format" :header="$t('common.export_format')" />
        <Column field="status" :header="$t('common.status')">
            <template #body="{ data }">
                <ExportStatusBadge :status="data.status" />
            </template>
        </Column>
        <Column field="expires_at" :header="$t('common.export_expires_at')" />
        <Column :header="$t('common.download')">
            <template #body="{ data }">
                <a v-if="canDownload(data)" :href="downloadUrl(data)">
                    <Button icon="pi pi-download" :aria-label="$t('common.download')" text rounded />
                </a>
                <span v-else class="text-sm text-bakery-dark/45">-</span>
            </template>
        </Column>
    </DataTable>
</template>
