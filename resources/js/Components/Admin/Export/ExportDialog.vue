<script setup>
import { computed, ref, watch } from "vue";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Select from "primevue/select";

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    type: {
        type: String,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:visible", "confirm"]);
const format = ref("csv");

watch(
    () => props.visible,
    (visible) => {
        if (visible) {
            format.value = "csv";
        }
    }
);

const formatOptions = computed(() => [
    { label: "CSV", value: "csv" },
    { label: "XLSX", value: "xlsx" },
]);

const activeFilters = computed(() =>
    Object.entries(props.filters ?? {}).filter(([, value]) => value !== null && value !== "" && value !== undefined)
);

const close = () => emit("update:visible", false);
const confirm = () => emit("confirm", { type: props.type, format: format.value, filters: props.filters });
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        :header="$t('common.export')"
        class="w-[min(92vw,32rem)]"
        @update:visible="$emit('update:visible', $event)"
    >
        <div class="space-y-4">
            <div class="rounded-md border border-bakery-brown/15 bg-[#fcf7ef] p-3 text-sm text-bakery-dark/75">
                <div class="font-medium text-bakery-dark">{{ $t("common.export_type") }}: {{ type }}</div>
                <div v-if="activeFilters.length" class="mt-2 flex flex-wrap gap-2">
                    <span
                        v-for="[key, value] in activeFilters"
                        :key="key"
                        class="rounded border border-bakery-brown/15 bg-white px-2 py-1 text-xs"
                    >
                        {{ key }}: {{ value }}
                    </span>
                </div>
                <p v-else class="mt-2 text-xs">{{ $t("common.all") }}</p>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">
                    {{ $t("common.export_format") }}
                </label>
                <Select
                    v-model="format"
                    :options="formatOptions"
                    option-label="label"
                    option-value="value"
                    class="w-full"
                />
            </div>

            <p class="text-xs text-bakery-dark/60">{{ $t("common.export_large_warning") }}</p>
        </div>

        <template #footer>
            <Button :label="$t('common.cancel')" text @click="close" />
            <Button icon="pi pi-download" :label="$t('common.export')" :loading="loading" @click="confirm" />
        </template>
    </Dialog>
</template>
