<script setup>
import { Head } from "@inertiajs/vue3";
import ExportHistoryTable from "@/Components/Admin/Export/ExportHistoryTable.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";

defineOptions({ layout: AdminLayout });

defineProps({
    exports: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head :title="$t('common.export_history')" />

    <div class="space-y-6">
        <SectionTitle :eyebrow="$t('common.export')" :title="$t('common.export_history')" />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <ExportHistoryTable :exports="exports" />
        </div>
    </div>
</template>
